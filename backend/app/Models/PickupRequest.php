<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class PickupRequest extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'customer_id','branch_id','pickup_address','latitude','longitude',
        'preferred_date','preferred_time','phone_number','notes','service_id',
        'assigned_to','status','accepted_at','en_route_at','picked_up_at',
        'cancelled_at','cancellation_reason','cancelled_by',
        'laundries_id','pickup_fee','delivery_fee','service_type',
    ];
    protected $casts = [
        'preferred_date'=>'date','accepted_at'=>'datetime','en_route_at'=>'datetime',
        'picked_up_at'=>'datetime','cancelled_at'=>'datetime',
        'pickup_fee'=>'decimal:2','delivery_fee'=>'decimal:2',
    ];
    // RELATIONSHIPS
    public function customer()   { return $this->belongsTo(Customer::class); }
    public function branch()     { return $this->belongsTo(Branch::class); }
    public function service()    { return $this->belongsTo(Service::class); }
    public function assignedStaff()    { return $this->belongsTo(User::class, 'assigned_to'); }
    public function cancelledByUser()  { return $this->belongsTo(User::class, 'cancelled_by'); }
    /**
     * BUG FIX: was belongsTo(Laundry::class) — looks for laundry_id on pickup_requests (doesn't exist).
     * FK pickup_request_id lives on the laundries table → correct direction is hasOne.
     */
    public function laundry() { return $this->hasOne(Laundry::class, 'pickup_request_id'); }
    // SCOPES
    public function scopePending($q)   { return $q->where('status','pending'); }
    public function scopeAccepted($q)  { return $q->where('status','accepted'); }
    public function scopeEnRoute($q)   { return $q->where('status','en_route'); }
    public function scopePickedUp($q)  { return $q->where('status','picked_up'); }
    public function scopeCancelled($q) { return $q->where('status','cancelled'); }
    public function scopeActive($q)    { return $q->whereIn('status',['pending','accepted','en_route']); }
    public function scopeForBranch($q, $branchId) { return $q->where('branch_id',$branchId); }
    public function scopeForDate($q, $date)        { return $q->whereDate('preferred_date',$date); }
    // HELPERS
    public function isPending()   { return $this->status==='pending'; }
    public function isAccepted()  { return $this->status==='accepted'; }
    public function isEnRoute()   { return $this->status==='en_route'; }
    public function isPickedUp()  { return $this->status==='picked_up'; }
    public function isCancelled() { return $this->status==='cancelled'; }
    public function canBeCancelled()  { return in_array($this->status,['pending','accepted']); }
    public function canBeAccepted()   { return $this->status==='pending'; }
    public function canMarkEnRoute()  { return $this->status==='accepted'; }
    public function canMarkPickedUp() { return in_array($this->status,['accepted','en_route']); }
    /**
     * BUG FIX: was !is_null($this->laundries_id) — checks a column that may not be set.
     * Now checks the hasOne relationship via relationLoaded() to avoid extra queries.
     */
    public function hasLaundry(): bool
    {
        if ($this->relationLoaded('laundry')) {
            return $this->laundry !== null;
        }
        return Laundry::where('pickup_request_id', $this->id)->exists();
    }
    // STATUS TRANSITIONS
    public function accept($userId=null)
    {
        $this->update(['status'=>'accepted','accepted_at'=>now(),'assigned_to'=>$userId]);
    }
    public function markEnRoute()  { $this->update(['status'=>'en_route','en_route_at'=>now()]); }
    public function markPickedUp() { $this->update(['status'=>'picked_up','picked_up_at'=>now()]); }
    public function cancel($reason=null,$userId=null)
    {
        $this->update(['status'=>'cancelled','cancelled_at'=>now(),'cancellation_reason'=>$reason,'cancelled_by'=>$userId]);
    }
    public function linkToLaundry($laundryId) { $this->update(['laundries_id'=>$laundryId]); }
    // ACCESSORS
    public function getStatusBadgeColorAttribute(): string
    {
        return ['pending'=>'warning','accepted'=>'info','en_route'=>'primary','picked_up'=>'success','cancelled'=>'danger'][$this->status]??'secondary';
    }
    public function getFormattedPreferredDateTimeAttribute(): string
    {
        $date=$this->preferred_date->format('M d, Y');
        return $this->preferred_time ? $date.' at '.date('g:i A',strtotime($this->preferred_time)) : $date;
    }
    public function getMapUrlAttribute(): ?string
    {
        if(!$this->latitude||!$this->longitude) return null;
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }
    public function getTotalFeeAttribute(): float { return (float)($this->pickup_fee+$this->delivery_fee); }
    public function getDistanceFrom($lat,$lon): ?float
    {
        if(!$this->latitude||!$this->longitude) return null;
        $R=6371;
        $dLat=deg2rad($this->latitude-$lat); $dLon=deg2rad($this->longitude-$lon);
        $a=sin($dLat/2)*sin($dLat/2)+cos(deg2rad($lat))*cos(deg2rad($this->latitude))*sin($dLon/2)*sin($dLon/2);
        return $R*2*atan2(sqrt($a),sqrt(1-$a));
    }
}
