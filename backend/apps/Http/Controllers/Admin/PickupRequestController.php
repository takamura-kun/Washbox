<?php
namespace App\Http\Controllers\Admin;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Customer;
use App\Models\DeliveryFee;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\PickupRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PickupRequestController extends Controller
{
    protected $routingService;
    public function __construct()
    {
        if (app()->bound(\App\Services\RouteService::class))
            $this->routingService = app()->make(\App\Services\RouteService::class);
        elseif (app()->bound('routeService'))
            $this->routingService = app()->make('routeService');
        else
            $this->routingService = null;
    }

    /**
     * BUG FIX: Added 'laundry' to with() so $pickup->laundry is not always null
     * on the list page, which was causing "Create Laundry" to always show.
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'active'); // 'active' or 'laundry'

        $query = PickupRequest::with(['customer','branch','service','assignedStaff','laundry'])
            ->orderBy('created_at','asc');

        if ($tab === 'laundry') {
            $query->has('laundry');
        } else {
            $query->doesntHave('laundry')->whereNotIn('status', ['cancelled']);
        }

        if ($request->filled('status') && $request->status !== 'all')
            $query->where('status', $request->status);
        if ($request->filled('branch_id'))
            $query->where('branch_id', $request->branch_id);
        if ($request->filled('date'))
            $query->whereDate('preferred_date', $request->date);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pickup_address','like',"%{$search}%")
                  ->orWhereHas('customer',fn($q2)=>$q2->where('name','like',"%{$search}%"));
            });
        }

        $pickups          = $query->paginate(20)->withQueryString();
        $branches         = Branch::where('is_active', true)->get();
        $activeCount      = PickupRequest::doesntHave('laundry')->whereNotIn('status',['cancelled'])->count();
        $laundryCount     = PickupRequest::has('laundry')->count();

        return view('admin.pickups.index', compact('pickups','branches','tab','activeCount','laundryCount'));
    }

    public function create()
    {
        $customers = Customer::where('is_active',true)->orderBy('name')->get();
        $services  = Service::where('is_active',true)->orderBy('name')->get();
        $branches  = Branch::with('deliveryFees')->where('is_active',true)->get();
        return view('admin.pickups.create', compact('customers','services','branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'   =>'required|exists:customers,id',
            'branch_id'     =>'required|exists:branches,id',
            'service_id'    =>'required|exists:services,id',
            'service_type'  =>'required|in:pickup_only,delivery_only,both',
            'preferred_date'=>'required|date|after_or_equal:today',
            'preferred_time'=>'required',
            'pickup_address'=>'required|string|max:500',
            'latitude'      =>'nullable|numeric',
            'longitude'     =>'nullable|numeric',
            'notes'         =>'nullable|string|max:1000',
        ]);
        $deliveryFee = DeliveryFee::getOrCreateForBranch($validated['branch_id']);
        $fees        = $deliveryFee->calculateFee($validated['service_type']);
        $pickup = PickupRequest::create([
            ...$validated,
            'pickup_fee'   => $fees['pickup_fee'],
            'delivery_fee' => $fees['delivery_fee'],
            'status'       => 'pending',
        ]);
        return redirect()->route('admin.pickups.show',$pickup)
            ->with('success','Pickup created! Fee: ₱'.number_format($fees['total_fee'],2));
    }

    public function show($id)
    {
        $pickup = PickupRequest::with(['customer','branch','service','assignedStaff','laundry'])->findOrFail($id);
        return view('admin.pickups.show', compact('pickup'));
    }

    public function accept(Request $request, $id)
    {
        $pickup = PickupRequest::findOrFail($id);
        if (!$pickup->canBeAccepted()) return back()->with('error','Cannot accept pickup in current status');
        $pickup->accept(Auth::id());
        Notification::createPickupAccepted($pickup);
        return back()->with('success','Pickup request accepted!');
    }

    public function markEnRoute($id)
    {
        $pickup = PickupRequest::findOrFail($id);
        if (!$pickup->canMarkEnRoute()) return back()->with('error','Cannot mark en route in current status');
        $pickup->markEnRoute();
        Notification::createPickupEnRoute($pickup);
        return back()->with('success','Pickup marked as en route!');
    }

    public function uploadProof(Request $request, $id)
    {
        $request->validate(['proof_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120']);
        
        $pickup = PickupRequest::findOrFail($id);
        
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
        $pickup = PickupRequest::findOrFail($id);
        if (!$pickup->canMarkPickedUp()) return back()->with('error','Cannot mark as picked up in current status');
        
        if (!$pickup->pickup_proof_photo) {
            return back()->with('error', 'Please upload proof photo before marking as picked up.');
        }
        
        $pickup->markPickedUp();
        Notification::createPickupProofUploaded($pickup);
        
        return redirect()->route('admin.laundries.create',['pickup_id'=>$pickup->id])
            ->with('success','Laundry picked up! Customer has been notified. Now create the laundry order.');
    }

    public function cancel(Request $request, $id)
    {
        $request->validate(['reason'=>'required|string|max:500']);
        $pickup = PickupRequest::findOrFail($id);
        if (!$pickup->canBeCancelled()) return back()->with('error','Cannot cancel in current status');
        $pickup->cancel($request->reason, Auth::id());
        Notification::createPickupCancelled($pickup, 'admin');
        return back()->with('success','Pickup request cancelled.');
    }

    public function assignStaff(Request $request, $id)
    {
        $request->validate(['staff_id'=>'required|exists:users,id']);
        PickupRequest::findOrFail($id)->update(['assigned_to'=>$request->staff_id]);
        return back()->with('success','Staff assigned successfully!');
    }

    public function bulkAccept(Request $request)
    {
        $request->validate(['pickup_ids'=>'required|array','pickup_ids.*'=>'exists:pickup_requests,id']);
        $count=0;
        foreach($request->pickup_ids as $id){
            $pickup=PickupRequest::find($id);
            if($pickup&&$pickup->canBeAccepted()){ $pickup->accept(Auth::id()); $count++; }
        }
        return back()->with('success',"{$count} pickup(s) accepted!");
    }

    public function getRoute($id)
    {
        $pickup=PickupRequest::findOrFail($id);
        if(!$this->routingService) return response()->json(['success'=>false,'message'=>'Routing not configured'],500);
        $route=$this->routingService->getRoute(from:[9.3068,123.3033],to:[$pickup->latitude,$pickup->longitude]);
        return response()->json(['success'=>true,'route'=>$route,'estimated_arrival'=>now()->addSeconds($route['duration_seconds'])->format('h:i A')]);
    }
}
