<?php
namespace App\Http\Controllers\Branch;
use App\Models\Branch;
use App\Models\Laundry;
use App\Models\Service;
use App\Models\Customer;
use App\Models\DeliveryFee;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\PickupRequest;
use App\Services\RouteService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PickupRequestController extends Controller
{
    public function index(Request $request)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch)
            return redirect()->route('branch.dashboard')->with('error','Please login to access this page.');

        $tab = $request->input('tab', 'active');

        $query = PickupRequest::with(['customer','service','assignedStaff','laundry'])
            ->where('branch_id',$branch->id)
            ->orderBy('created_at','asc');

        if ($tab === 'laundry') {
            $query->has('laundry');
        } else {
            $query->doesntHave('laundry')->whereNotIn('status', ['cancelled']);
        }

        if ($request->filled('status') && $request->status !== 'all')
            $query->where('status',$request->status);
        if ($request->filled('date'))
            $query->whereDate('preferred_date',$request->date);
        if ($request->filled('search')) {
            $s=$request->search;
            $query->where(fn($q)=>$q->where('pickup_address','like',"%{$s}%")
                ->orWhereHas('customer',fn($q2)=>$q2->where('name','like',"%{$s}%")));
        }
        if ($request->filled('my_assigned'))
            $query->where('assigned_to',$branch->id);

        $pickups      = $query->paginate(20)->withQueryString();
        $stats        = $this->getBranchStats($branch);
        $branches     = Branch::where('is_active',true)->get();
        $activeCount  = PickupRequest::where('branch_id',$branch->id)->doesntHave('laundry')->whereNotIn('status',['cancelled'])->count();
        $laundryCount = PickupRequest::where('branch_id',$branch->id)->has('laundry')->count();

        return view('branch.pickups.index', compact('pickups','stats','branches','tab','activeCount','laundryCount'));
    }

    private function getBranchStats($branch): array
    {
        $base = PickupRequest::where('branch_id',$branch->id);
        return [
            'pending'     => (clone $base)->pending()->count(),
            'accepted'    => (clone $base)->accepted()->count(),
            'en_route'    => (clone $base)->enRoute()->count(),
            'picked_up'   => (clone $base)->pickedUp()->count(),
            'today'       => (clone $base)->whereDate('preferred_date',today())->count(),
            'my_assigned' => (clone $base)->where('assigned_to',$branch->id)->count(),
        ];
    }

    public function create()
    {
        $branch=Auth::guard('branch')->user();
        if (!$branch)
            return redirect()->route('branch.dashboard')->with('error','Please login to access this page.');
        $customers    = Customer::where('is_active',true)->orderBy('name')->get();
        $services     = Service::where('is_active',true)->orderBy('name')->get();
        $deliveryFees = DeliveryFee::getOrCreateForBranch($branch->id);
        return view('branch.pickups.create', compact('customers','services','deliveryFees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'  =>'required|exists:customers,id',
            'service_id'   =>'required|exists:services,id',
            'weight'       =>'required|numeric|min:0.1',
            'pickup_date'  =>'nullable|date',
            'delivery_date'=>'nullable|date',
            'pickup_fee'   =>'nullable|numeric|min:0',
            'delivery_fee' =>'nullable|numeric|min:0',
            'notes'        =>'nullable|string|max:1000',
        ]);
        $branch      = Auth::guard('branch')->user();
        $service     = Service::findOrFail($validated['service_id']);
        $pricePerKg  = $service->price_per_piece;
        $subtotal    = $validated['weight'] * $pricePerKg;
        $pickupFee   = $validated['pickup_fee']??0;
        $deliveryFee = $validated['delivery_fee']??0;
        $totalAmount = $subtotal+$pickupFee+$deliveryFee;
        $trackingNumber = $this->generateTrackingNumber();
        $laundry = Laundry::create([
            'tracking_number'=>$trackingNumber,'customer_id'=>$validated['customer_id'],
            'branch_id'=>$branch->id,'service_id'=>$validated['service_id'],
            'staff_id'=>null,'created_by'=>$branch->id,'weight'=>$validated['weight'],
            'price_per_piece'=>$pricePerKg,'subtotal'=>$subtotal,'discount_amount'=>0,
            'pickup_fee'=>$pickupFee,'delivery_fee'=>$deliveryFee,'total_amount'=>$totalAmount,
            'status'=>'received','received_at'=>now(),'notes'=>$validated['notes']??null,
        ]);
        $laundry->statusHistories()->create(['status'=>'received','changed_by'=>$branch->id,'notes'=>'Pickup created by branch']);
        return redirect()->route('branch.pickups.show',$laundry)->with('success','Pickup created! Tracking: '.$trackingNumber);
    }

    private function generateTrackingNumber(): string
    {
        do { $t='WB-'.date('Ymd').'-'.strtoupper(Str::random(4)); }
        while(Laundry::where('tracking_number',$t)->exists());
        return $t;
    }

    public function show($id)
    {
        $branch=Auth::guard('branch')->user();
        if (!$branch)
            return redirect()->route('branch.dashboard')->with('error','Please login to access this page.');
        $pickup=PickupRequest::with(['customer','branch','service','assignedStaff','laundry'])
            ->where('branch_id',$branch->id)->where('id',$id)->firstOrFail();
        return view('branch.pickups.show', compact('pickup'));
    }

    public function accept($id)
    {
        $branch=Auth::guard('branch')->user();
        $pickup=PickupRequest::findOrFail($id);
        if ($pickup->branch_id!=$branch->id) abort(403);
        if (!$pickup->canBeAccepted()) return back()->with('error','Cannot accept in current status.');
        $pickup->accept($branch->id);
        Notification::createPickupAccepted($pickup);
        return back()->with('success','Pickup accepted and assigned to you!');
    }

    public function markEnRoute($id)
    {
        $branch=Auth::guard('branch')->user();
        $pickup=PickupRequest::findOrFail($id);
        if ($pickup->branch_id!=$branch->id) abort(403);
        if ($pickup->assigned_to!=$branch->id) return back()->with('error','You are not assigned to this pickup.');
        if (!$pickup->canMarkEnRoute()) return back()->with('error','Cannot mark en route in current status.');
        $pickup->markEnRoute();
        Notification::createPickupEnRoute($pickup);
        return back()->with('success','Status updated: On the way to customer!');
    }

    public function uploadProof(Request $request, $id)
    {
        $branch = Auth::guard('branch')->user();
        $request->validate(['proof_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120']);
        
        $pickup = PickupRequest::findOrFail($id);
        if ($pickup->branch_id != $branch->id) abort(403);
        
        if (!in_array($pickup->status, ['en_route', 'picked_up'])) {
            return back()->with('error', 'Pickup must be en route or picked up to upload proof.');
        }
        
        $image = $request->file('proof_photo');
        $filename = now()->timestamp . '_pickup_' . $pickup->id . '_' . Str::random(8) . '.' . $image->getClientOriginalExtension();
        $image->storeAs('pickup-proofs', $filename, 'public');
        
        $pickup->update([
            'pickup_proof_photo' => $filename,
            'proof_uploaded_at' => now(),
        ]);
        
        return back()->with('success', 'Proof photo uploaded successfully! You can now mark as picked up.');
    }

    public function markPickedUp($id)
    {
        $branch=Auth::guard('branch')->user();
        $pickup=PickupRequest::findOrFail($id);
        if ($pickup->branch_id!=$branch->id) abort(403);
        if ($pickup->assigned_to!=$branch->id) return back()->with('error','You are not assigned to this pickup.');
        if (!$pickup->canMarkPickedUp()) return back()->with('error','Cannot mark as picked up in current status.');
        
        if (!$pickup->pickup_proof_photo) {
            return back()->with('error', 'Please upload proof photo before marking as picked up.');
        }
        
        $pickup->markPickedUp();
        Notification::createPickupProofUploaded($pickup);
        
        return redirect()->route('branch.laundries.create',['pickup_id'=>$pickup->id])
            ->with('success','Laundry picked up! Customer has been notified. Now create the laundry order.');
    }

    public function cancel(Request $request, $id)
    {
        $branch=Auth::guard('branch')->user();
        $request->validate(['reason'=>'required|string|max:500']);
        $pickup=PickupRequest::findOrFail($id);
        if ($pickup->branch_id!=$branch->id) abort(403);
        if (!$pickup->canBeCancelled()) return back()->with('error','Cannot cancel in current status.');
        $pickup->cancel($request->reason,$branch->id);
        Notification::createPickupCancelled($pickup, 'branch');
        return back()->with('success','Pickup request cancelled.');
    }

    public function stats()
    {
        $branch=Auth::guard('branch')->user();
        if (!$branch) return response()->json(['error'=>'Not authenticated'],403);
        return response()->json($this->getBranchStats($branch));
    }

    public function updateLocation(Request $request,$id)
    {
        $branch=Auth::guard('branch')->user();
        $request->validate(['latitude'=>'required|numeric|between:-90,90','longitude'=>'required|numeric|between:-180,180']);
        $pickup=PickupRequest::findOrFail($id);
        if ($pickup->branch_id!=$branch->id||$pickup->assigned_to!=$branch->id) abort(403);
        $pickup->update(['staff_latitude'=>$request->latitude,'staff_longitude'=>$request->longitude,'location_updated_at'=>now()]);
        return response()->json(['success'=>true,'message'=>'Location updated']);
    }

    public function getRoute($id)
    {
        try {
            $branch=Auth::guard('branch')->user();
            $pickup=PickupRequest::with('branch','customer')->where('id',$id)->where('branch_id',$branch->id)->firstOrFail();
            if (!$pickup->latitude||!$pickup->longitude)
                return response()->json(['success'=>false,'error'=>'Invalid coordinates'],400);
            $route=(new RouteService())->getRouteFromBranch($pickup,'osrm');
            if (!$route['success']) return response()->json(['success'=>false,'error'=>$route['error']??'Route failed'],500);
            return response()->json(['success'=>true,'route'=>$route['route'],'branch'=>['name'=>$pickup->branch->name??null,'latitude'=>$pickup->branch->latitude??null,'longitude'=>$pickup->branch->longitude??null]]);
        } catch(\Exception $e) {
            Log::error('Branch route failed: '.$e->getMessage());
            return response()->json(['success'=>false,'error'=>$e->getMessage()],500);
        }
    }

    public function startNavigation(Request $request,$id)
    {
        try {
            $branch=Auth::guard('branch')->user();
            $pickup=PickupRequest::where('id',$id)->where('branch_id',$branch->id)->firstOrFail();
            $pickup->update(['status'=>'en_route','en_route_at'=>now(),'assigned_to'=>$branch->id]);
            return response()->json(['success'=>true,'message'=>'Navigation started for pickup #'.$pickup->id,'pickup'=>$pickup->fresh()]);
        } catch(\Exception $e) {
            return response()->json(['success'=>false,'error'=>$e->getMessage()],500);
        }
    }

    public function startMultiNavigation(Request $request)
    {
        try {
            $branch = Auth::guard('branch')->user();
            $pickupIds = $request->input('pickup_ids', []);
            
            if (empty($pickupIds)) {
                return response()->json(['success' => false, 'error' => 'No pickups selected'], 400);
            }
            
            $updated = PickupRequest::whereIn('id', $pickupIds)
                ->where('branch_id', $branch->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->update([
                    'status' => 'en_route',
                    'en_route_at' => now(),
                    'assigned_to' => $branch->id
                ]);
            
            return response()->json([
                'success' => true,
                'message' => "Navigation started for {$updated} pickup(s)",
                'updated_count' => $updated
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
