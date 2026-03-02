<?php
namespace App\Http\Controllers\Staff;
use App\Models\Branch;
use App\Models\Laundry;
use App\Models\Service;
use App\Models\Customer;
use App\Models\DeliveryFee;
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
        $staff = Auth::user();
        if (!$staff||!$staff->branch_id)
            return redirect()->route('staff.dashboard')->with('error','Not assigned to a branch.');

        $query = PickupRequest::with(['customer','service','assignedStaff','laundry'])
            ->where('branch_id',$staff->branch_id)
            ->orderBy('id','asc');

        if ($request->filled('status')&&$request->status!=='all')
            $query->where('status',$request->status);
        if ($request->filled('date'))
            $query->whereDate('preferred_date',$request->date);
        if ($request->filled('search')) {
            $s=$request->search;
            $query->where(fn($q)=>$q->where('pickup_address','like',"%{$s}%")
                ->orWhereHas('customer',fn($q2)=>$q2->where('name','like',"%{$s}%")));
        }
        if ($request->filled('my_assigned'))
            $query->where('assigned_to',$staff->id);

        $pickups  = $query->paginate(20);
        $stats    = $this->getBranchStats($staff);
        // BUG FIX: pass $branches so the filter select doesn't throw an undefined variable error
        $branches = Branch::where('is_active',true)->get();
        return view('staff.pickups.index', compact('pickups','stats','branches'));
    }

    private function getBranchStats($staff): array
    {
        $base = PickupRequest::where('branch_id',$staff->branch_id);
        return [
            'pending'     => (clone $base)->pending()->count(),
            'accepted'    => (clone $base)->accepted()->count(),
            'en_route'    => (clone $base)->enRoute()->count(),
            'picked_up'   => (clone $base)->pickedUp()->count(),
            'today'       => (clone $base)->whereDate('preferred_date',today())->count(),
            'my_assigned' => (clone $base)->where('assigned_to',$staff->id)->count(),
        ];
    }

    public function create()
    {
        $staff=Auth::user();
        if (!$staff||!$staff->branch_id)
            return redirect()->route('staff.dashboard')->with('error','Not assigned to a branch.');
        $customers    = Customer::where('is_active',true)->orderBy('name')->get();
        $services     = Service::where('is_active',true)->orderBy('name')->get();
        $deliveryFees = DeliveryFee::getOrCreateForBranch($staff->branch_id);
        return view('staff.pickups.create', compact('customers','services','deliveryFees'));
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
        $staff       = auth()->user();
        $service     = Service::findOrFail($validated['service_id']);
        $pricePerKg  = $service->price_per_piece;
        $subtotal    = $validated['weight'] * $pricePerKg;
        $pickupFee   = $validated['pickup_fee']??0;
        $deliveryFee = $validated['delivery_fee']??0;
        $totalAmount = $subtotal+$pickupFee+$deliveryFee;
        $trackingNumber = $this->generateTrackingNumber();
        $laundry = Laundry::create([
            'tracking_number'=>$trackingNumber,'customer_id'=>$validated['customer_id'],
            'branch_id'=>$staff->branch_id,'service_id'=>$validated['service_id'],
            'staff_id'=>$staff->id,'created_by'=>$staff->id,'weight'=>$validated['weight'],
            'price_per_piece'=>$pricePerKg,'subtotal'=>$subtotal,'discount_amount'=>0,
            'pickup_fee'=>$pickupFee,'delivery_fee'=>$deliveryFee,'total_amount'=>$totalAmount,
            'status'=>'received','received_at'=>now(),'notes'=>$validated['notes']??null,
        ]);
        $laundry->statusHistories()->create(['status'=>'received','changed_by'=>$staff->id,'notes'=>'Pickup created by staff']);
        return redirect()->route('staff.pickups.show',$laundry)->with('success','Pickup created! Tracking: '.$trackingNumber);
    }

    private function generateTrackingNumber(): string
    {
        do { $t='WB-'.date('Ymd').'-'.strtoupper(Str::random(4)); }
        while(Laundry::where('tracking_number',$t)->exists());
        return $t;
    }

    public function show($id)
    {
        $staff=$Auth=Auth::user();
        if (!$staff||!$staff->branch_id)
            return redirect()->route('staff.dashboard')->with('error','Not assigned to a branch.');
        $pickup=PickupRequest::with(['customer','branch','service','assignedStaff','laundry'])
            ->where('branch_id',$staff->branch_id)->where('id',$id)->firstOrFail();
        return view('staff.pickups.show', compact('pickup'));
    }

    public function accept($id)
    {
        $staff=Auth::user();
        $pickup=PickupRequest::findOrFail($id);
        if ($pickup->branch_id!=$staff->branch_id) abort(403);
        if (!$pickup->canBeAccepted()) return back()->with('error','Cannot accept in current status.');
        $pickup->accept($staff->id);
        return back()->with('success','Pickup accepted and assigned to you!');
    }

    public function markEnRoute($id)
    {
        $staff=Auth::user();
        $pickup=PickupRequest::findOrFail($id);
        if ($pickup->branch_id!=$staff->branch_id) abort(403);
        if ($pickup->assigned_to!=$staff->id) return back()->with('error','You are not assigned to this pickup.');
        if (!$pickup->canMarkEnRoute()) return back()->with('error','Cannot mark en route in current status.');
        $pickup->markEnRoute();
        return back()->with('success','Status updated: On the way to customer!');
    }

    public function markPickedUp($id)
    {
        $staff=Auth::user();
        $pickup=PickupRequest::findOrFail($id);
        if ($pickup->branch_id!=$staff->branch_id) abort(403);
        if ($pickup->assigned_to!=$staff->id) return back()->with('error','You are not assigned to this pickup.');
        if (!$pickup->canMarkPickedUp()) return back()->with('error','Cannot mark as picked up in current status.');
        $pickup->markPickedUp();
        return redirect()->route('staff.laundries.create',['pickup_id'=>$pickup->id])
            ->with('success','Laundry picked up! Now create the laundry order.');
    }

    public function cancel(Request $request, $id)
    {
        $staff=Auth::user();
        $request->validate(['reason'=>'required|string|max:500']);
        $pickup=PickupRequest::findOrFail($id);
        if ($pickup->branch_id!=$staff->branch_id) abort(403);
        if (!$pickup->canBeCancelled()) return back()->with('error','Cannot cancel in current status.');
        $pickup->cancel($request->reason,$staff->id);
        return back()->with('success','Pickup request cancelled.');
    }

    public function stats()
    {
        $staff=Auth::user();
        if (!$staff||!$staff->branch_id) return response()->json(['error'=>'Not assigned'],403);
        return response()->json($this->getBranchStats($staff));
    }

    public function updateLocation(Request $request,$id)
    {
        $staff=Auth::user();
        $request->validate(['latitude'=>'required|numeric|between:-90,90','longitude'=>'required|numeric|between:-180,180']);
        $pickup=PickupRequest::findOrFail($id);
        if ($pickup->branch_id!=$staff->branch_id||$pickup->assigned_to!=$staff->id) abort(403);
        $pickup->update(['current_latitude'=>$request->latitude,'current_longitude'=>$request->longitude,'location_updated_at'=>now()]);
        return response()->json(['success'=>true,'message'=>'Location updated']);
    }

    public function getRoute($id)
    {
        try {
            $staff=$staff=Auth::user();
            $pickup=PickupRequest::with('branch','customer')->where('id',$id)->where('branch_id',$staff->branch_id)->firstOrFail();
            if (!$pickup->latitude||!$pickup->longitude)
                return response()->json(['success'=>false,'error'=>'Invalid coordinates'],400);
            $route=(new RouteService())->getRouteFromBranch($pickup,'osrm');
            if (!$route['success']) return response()->json(['success'=>false,'error'=>$route['error']??'Route failed'],500);
            return response()->json(['success'=>true,'route'=>$route['route'],'branch'=>['name'=>$pickup->branch->name??null,'latitude'=>$pickup->branch->latitude??null,'longitude'=>$pickup->branch->longitude??null]]);
        } catch(\Exception $e) {
            Log::error('Staff route failed: '.$e->getMessage());
            return response()->json(['success'=>false,'error'=>$e->getMessage()],500);
        }
    }

    public function startNavigation(Request $request,$id)
    {
        try {
            $staff=Auth::user();
            $pickup=PickupRequest::where('id',$id)->where('branch_id',$staff->branch_id)->firstOrFail();
            $pickup->update(['status'=>'en_route','en_route_at'=>now(),'assigned_to'=>$staff->id]);
            return response()->json(['success'=>true,'message'=>'Navigation started for pickup #'.$pickup->id,'pickup'=>$pickup->fresh()]);
        } catch(\Exception $e) {
            return response()->json(['success'=>false,'error'=>$e->getMessage()],500);
        }
    }
}
