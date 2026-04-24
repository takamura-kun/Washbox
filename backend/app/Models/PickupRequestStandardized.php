<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class PickupRequest extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'customer_id', 'customer_address_id', 'branch_id',
        'pickup_address', 'delivery_address',
        'latitude', 'longitude', 'delivery_latitude', 'delivery_longitude',
        'preferred_date', 'preferred_time', 'phone_number', 'contact_person',
        'notes', 'special_instructions', 'estimated_weight', 'estimated_duration_minutes',
        'service_id', 'service_type', 'priority',
        'pickup_fee', 'delivery_fee',
        'assigned_to', 'status',
        'accepted_at', 'dispatched_at', 'en_route_at', 'arrived_at', 'picked_up_at', 'completed_at',
        'cancelled_at', 'cancellation_reason', 'cancelled_by',
        'laundry_id', 'pickup_proof_photo', 'proof_uploaded_at',
        'staff_latitude', 'staff_longitude', 'location_updated_at',
        'actual_duration_minutes', 'status_history',
        'customer_rating', 'customer_feedback', 'feedback_at',
        'distance_from_branch', 'estimated_travel_time', 'actual_distance', 'actual_travel_time',
        'route_data', 'estimated_pickup_time', 'route_instructions', 'pin'
    ];
    
    protected $casts = [
        'preferred_date' => 'date',
        'accepted_at' => 'datetime',
        'dispatched_at' => 'datetime', 
        'en_route_at' => 'datetime',
        'arrived_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'proof_uploaded_at' => 'datetime',
        'location_updated_at' => 'datetime',
        'feedback_at' => 'datetime',
        'estimated_pickup_time' => 'datetime',
        'pickup_fee' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'estimated_weight' => 'decimal:2',
        'estimated_duration_minutes' => 'decimal:2',
        'actual_duration_minutes' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
        'staff_latitude' => 'decimal:8',
        'staff_longitude' => 'decimal:8',
        'distance_from_branch' => 'decimal:2',
        'actual_distance' => 'decimal:2',
        'route_data' => 'array',
        'status_history' => 'array',
        'customer_rating' => 'integer',
    ];

    // RELATIONSHIPS
    public function customer() { return $this->belongsTo(Customer::class); }
    public function customerAddress() { return $this->belongsTo(CustomerAddress::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function service() { return $this->belongsTo(Service::class); }
    public function assignedStaff() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function cancelledByUser() { return $this->belongsTo(User::class, 'cancelled_by'); }
    
    /**
     * Fixed relationship - now uses laundry_id instead of laundries_id
     */
    public function laundry() { return $this->hasOne(Laundry::class, 'pickup_request_id'); }

    // SCOPES
    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeAccepted($q) { return $q->where('status', 'accepted'); }
    public function scopeDispatched($q) { return $q->where('status', 'dispatched'); }
    public function scopeEnRoute($q) { return $q->where('status', 'en_route'); }
    public function scopeArrived($q) { return $q->where('status', 'arrived'); }
    public function scopePickedUp($q) { return $q->where('status', 'picked_up'); }
    public function scopeCompleted($q) { return $q->where('status', 'completed'); }
    public function scopeCancelled($q) { return $q->where('status', 'cancelled'); }
    public function scopeActive($q) { return $q->whereIn('status', ['pending', 'accepted', 'dispatched', 'en_route', 'arrived']); }
    public function scopeForBranch($q, $branchId) { return $q->where('branch_id', $branchId); }
    public function scopeForDate($q, $date) { return $q->whereDate('preferred_date', $date); }
    public function scopeHighPriority($q) { return $q->whereIn('priority', ['high', 'urgent']); }
    public function scopeWithRating($q) { return $q->whereNotNull('customer_rating'); }

    // STATUS HELPERS
    public function isPending() { return $this->status === 'pending'; }
    public function isAccepted() { return $this->status === 'accepted'; }
    public function isDispatched() { return $this->status === 'dispatched'; }
    public function isEnRoute() { return $this->status === 'en_route'; }
    public function isArrived() { return $this->status === 'arrived'; }
    public function isPickedUp() { return $this->status === 'picked_up'; }
    public function isCompleted() { return $this->status === 'completed'; }
    public function isCancelled() { return $this->status === 'cancelled'; }
    
    // PERMISSION HELPERS
    public function canBeCancelled() { return in_array($this->status, ['pending', 'accepted', 'dispatched']); }
    public function canBeAccepted() { return $this->status === 'pending'; }
    public function canBeDispatched() { return $this->status === 'accepted'; }
    public function canMarkEnRoute() { return in_array($this->status, ['accepted', 'dispatched']); }
    public function canMarkArrived() { return $this->status === 'en_route'; }
    public function canMarkPickedUp() { return in_array($this->status, ['en_route', 'arrived']); }
    public function canBeRescheduled() { return in_array($this->status, ['pending', 'accepted']); }
    public function canBeRated() { return $this->status === 'completed' && !$this->customer_rating; }

    /**
     * Check if pickup has associated laundry record
     */
    public function hasLaundry(): bool
    {
        if ($this->relationLoaded('laundry')) {
            return $this->laundry !== null;
        }
        return Laundry::where('pickup_request_id', $this->id)->exists();
    }

    // STATUS TRANSITIONS WITH HISTORY TRACKING
    public function accept($userId = null)
    {
        $this->updateStatusWithHistory('accepted', $userId, 'Pickup request accepted');
        $this->update(['accepted_at' => now(), 'assigned_to' => $userId]);
    }
    
    public function dispatch($userId = null)
    {
        $this->updateStatusWithHistory('dispatched', $userId, 'Staff dispatched for pickup');
        $this->update(['dispatched_at' => now()]);
    }

    public function markEnRoute($userId = null)
    {
        $this->updateStatusWithHistory('en_route', $userId, 'Staff en route to pickup location');
        $this->update(['en_route_at' => now()]);
    }
    
    public function markArrived($userId = null)
    {
        $this->updateStatusWithHistory('arrived', $userId, 'Staff arrived at pickup location');
        $this->update(['arrived_at' => now()]);
    }

    public function markPickedUp($userId = null)
    {
        $this->updateStatusWithHistory('picked_up', $userId, 'Laundry picked up successfully');
        $this->update(['picked_up_at' => now()]);
    }
    
    public function markCompleted($userId = null)
    {
        $this->updateStatusWithHistory('completed', $userId, 'Pickup process completed');
        $this->update(['completed_at' => now()]);
    }

    public function cancel($reason = null, $userId = null)
    {
        $this->updateStatusWithHistory('cancelled', $userId, $reason ?: 'Pickup cancelled');
        $this->update([
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'cancelled_by' => $userId
        ]);
    }
    
    /**
     * Update status with history tracking
     */
    private function updateStatusWithHistory($newStatus, $userId = null, $notes = null)
    {
        $history = $this->status_history ?: [];
        $history[] = [
            'status' => $newStatus,
            'timestamp' => now()->toISOString(),
            'changed_by' => $userId,
            'user_name' => $userId ? User::find($userId)?->name : null,
            'notes' => $notes
        ];
        
        $this->update([
            'status' => $newStatus,
            'status_history' => $history
        ]);
    }
    
    /**
     * Update staff location
     */
    public function updateStaffLocation($latitude, $longitude)
    {
        $this->update([
            'staff_latitude' => $latitude,
            'staff_longitude' => $longitude,
            'location_updated_at' => now()
        ]);
    }
    
    /**
     * Add customer rating and feedback
     */
    public function addCustomerFeedback($rating, $feedback = null)
    {
        $this->update([
            'customer_rating' => $rating,
            'customer_feedback' => $feedback,
            'feedback_at' => now()
        ]);
    }

    // ACCESSORS
    public function getStatusBadgeColorAttribute(): string
    {
        return [
            'pending' => 'warning',
            'accepted' => 'info', 
            'dispatched' => 'info',
            'en_route' => 'primary',
            'arrived' => 'primary',
            'picked_up' => 'success',
            'completed' => 'success',
            'cancelled' => 'danger'
        ][$this->status] ?? 'secondary';
    }

    public function getFormattedPreferredDateTimeAttribute(): string
    {
        $date = $this->preferred_date->format('M d, Y');
        return $this->preferred_time ? $date . ' at ' . date('g:i A', strtotime($this->preferred_time)) : $date;
    }

    public function getMapUrlAttribute(): ?string
    {
        if (!$this->latitude || !$this->longitude) return null;
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    public function getTotalFeeAttribute(): float
    {
        return (float)($this->pickup_fee + $this->delivery_fee);
    }

    public function getPickupProofPhotoUrlAttribute(): ?string
    {
        if (!$this->pickup_proof_photo) return null;
        return asset('storage/pickup-proofs/' . $this->pickup_proof_photo);
    }

    public function hasProofPhoto(): bool
    {
        return !empty($this->pickup_proof_photo);
    }
    
    public function getStatusDisplayAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
    
    public function getPriorityDisplayAttribute(): string
    {
        return ucfirst($this->priority);
    }
    
    public function getPriorityColorAttribute(): string
    {
        return [
            'low' => 'secondary',
            'normal' => 'primary',
            'high' => 'warning', 
            'urgent' => 'danger'
        ][$this->priority] ?? 'primary';
    }
    
    /**
     * Get estimated arrival time based on current location and traffic
     */
    public function getEstimatedArrivalAttribute(): ?string
    {
        if (!$this->estimated_pickup_time) return null;
        return $this->estimated_pickup_time->format('g:i A');
    }
    
    /**
     * Calculate distance from staff current location to pickup
     */
    public function getDistanceFromStaff($staffLat, $staffLng): ?float
    {
        if (!$this->latitude || !$this->longitude) return null;
        
        $R = 6371; // Earth's radius in kilometers
        $dLat = deg2rad($this->latitude - $staffLat);
        $dLon = deg2rad($this->longitude - $staffLng);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($staffLat)) * cos(deg2rad($this->latitude)) * sin($dLon/2) * sin($dLon/2);
        return $R * 2 * atan2(sqrt($a), sqrt(1-$a));
    }
    
    /**
     * Get the most recent status change from history
     */
    public function getLastStatusChangeAttribute(): ?array
    {
        if (!$this->status_history || empty($this->status_history)) return null;
        return end($this->status_history);
    }
    
    /**
     * Check if pickup is overdue based on preferred time
     */
    public function isOverdue(): bool
    {
        if (!$this->preferred_time || $this->isCompleted() || $this->isCancelled()) {
            return false;
        }
        
        $preferredDateTime = $this->preferred_date->setTimeFromTimeString($this->preferred_time);
        return now()->gt($preferredDateTime->addHours(1)); // 1 hour grace period
    }
}