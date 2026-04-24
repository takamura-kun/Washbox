<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AdminNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'icon',
        'color',
        'link',
        'data',
        'branch_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Add this to ensure dates are always Carbon instances
    protected $dates = [
        'read_at',
        'created_at',
        'updated_at',
    ];

    // ===========================
    // RELATIONSHIPS
    // ===========================

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ===========================
    // SCOPES
    // ===========================

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ===========================
    // ACCESSORS - FIXED WITH NULL CHECKS
    // ===========================

    public function getIsReadAttribute()
    {
        return $this->read_at !== null;
    }

    public function getTimeAgoAttribute()
    {
        // FIX: Check if created_at exists before calling diffForHumans()
        if (!$this->created_at) {
            return 'Just now'; // Default value for null dates
        }

        try {
            return $this->created_at->diffForHumans();
        } catch (\Exception $e) {
            return 'Recently';
        }
    }

    public function getIconClassAttribute()
    {
        $icons = [
            'pickup_request' => 'bi-truck',
            'pickup_completed' => 'bi-check-circle',
            'pickup_cancelled' => 'bi-x-circle',
            'new_laundry' => 'bi-cart-plus',
            'payment' => 'bi-currency-dollar',
            'laundry_completed' => 'bi-check-all',
            'laundry_cancelled' => 'bi-x-circle',
            'unclaimed' => 'bi-exclamation-triangle',
            'new_customer' => 'bi-person-plus',
            'daily_revenue' => 'bi-graph-up',
            'low_cash_flow' => 'bi-currency-dollar',
            'payment_overdue' => 'bi-exclamation-triangle',
            'branch_performance' => 'bi-speedometer2',
            'customer_churn' => 'bi-people',
            'seasonal_trend' => 'bi-graph-up-arrow',
            'system' => 'bi-gear',
        ];

        return $icons[$this->type] ?? 'bi-bell';
    }

    // Add this accessor to safely get created_at
    public function getSafeCreatedAtAttribute()
    {
        if (!$this->created_at) {
            return null;
        }

        try {
            return $this->created_at->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    // Add this for formatted date display
    public function getFormattedCreatedAtAttribute()
    {
        if (!$this->created_at) {
            return 'Date unavailable';
        }

        try {
            return $this->created_at->format('M d, Y h:i A');
        } catch (\Exception $e) {
            return 'Date unavailable';
        }
    }

    // ===========================
    // METHODS
    // ===========================

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsUnread()
    {
        $this->update(['read_at' => null]);
    }

    // ===========================
    // STATIC HELPERS - Create Notifications
    // ===========================

    /**
     * Notify admin of new pickup request
     */
    public static function notifyNewPickupRequest($pickupRequest)
    {
        $pickupRequest->loadMissing('customer');

        return self::create([
            'type' => 'pickup_request',
            'title' => 'New Pickup Request',
            'message' => "Customer {$pickupRequest->customer->name} requested pickup at {$pickupRequest->pickup_address}",
            'icon' => 'truck',
            'color' => 'info',
            'link' => route('admin.pickups.show', $pickupRequest->id),
            'data' => [
                'pickup_request_id' => $pickupRequest->id,
                'customer_id' => $pickupRequest->customer_id,
                'customer_name' => $pickupRequest->customer->name,
                'pickup_address' => $pickupRequest->pickup_address,
                'preferred_date' => $pickupRequest->preferred_date?->format('Y-m-d'),
                'preferred_time' => $pickupRequest->preferred_time,
            ],
            'branch_id' => $pickupRequest->branch_id,
        ]);
    }

    /**
     * Notify admin of new laundry
     */
    public static function notifyNewLaundry($laundry)
    {
        $laundry->loadMissing('customer');

        return self::create([
            'type' => 'new_laundry',
            'title' => 'New Laundry Received',
            'message' => "Laundry #{$laundry->tracking_number} from {$laundry->customer->name} - ₱" . number_format($laundry->total_amount, 2),
            'icon' => 'cart-plus',
            'color' => 'success',
            'link' => route('admin.laundries.show', $laundry->id),
            'data' => [
                'laundries_id' => $laundry->id,
                'tracking_number' => $laundry->tracking_number,
                'customer_name' => $laundry->customer->name,
                'total_amount' => $laundry->total_amount,
            ],
            'branch_id' => $laundry->branch_id,
            'user_id' => $laundry->created_by,
        ]);
    }

    /**
     * Notify admin of payment received
     */
    public static function notifyPaymentReceived($laundry)
    {
        $laundry->loadMissing('customer');

        return self::create([
            'type' => 'payment',
            'title' => 'Payment Received',
            'message' => "₱" . number_format($laundry->total_amount, 2) . " received for laundry #{$laundry->tracking_number}",
            'icon' => 'currency-dollar',
            'color' => 'success',
            'link' => route('admin.laundries.show', $laundry->id),
            'data' => [
                'laundries_id' => $laundry->id,
                'tracking_number' => $laundry->tracking_number,
                'amount' => $laundry->total_amount,
            ],
            'branch_id' => $laundry->branch_id,
        ]);
    }

    /**
     * Notify admin of laundry completion
     */
    public static function notifyLaundryCompleted($laundry)
    {
        return self::create([
            'type' => 'laundry_completed',
            'title' => 'Laundry Completed',
            'message' => "Laundry #{$laundry->tracking_number} has been completed",
            'icon' => 'check-all',
            'color' => 'success',
            'link' => route('admin.laundries.show', $laundry->id),
            'branch_id' => $laundry->branch_id,
        ]);
    }

    /**
     * Notify admin of laundry cancellation
     */
    public static function notifyLaundryCancelled($laundry, $reason = null)
    {
        $laundry->loadMissing('customer');

        return self::create([
            'type' => 'laundry_cancelled',
            'title' => 'Laundry Cancelled',
            'message' => "Laundry #{$laundry->tracking_number} from {$laundry->customer->name} was cancelled",
            'icon' => 'x-circle',
            'color' => 'danger',
            'link' => route('admin.laundries.show', $laundry->id),
            'data' => [
                'laundries_id' => $laundry->id,
                'tracking_number' => $laundry->tracking_number,
                'reason' => $reason,
            ],
            'branch_id' => $laundry->branch_id,
        ]);
    }

    /**
     * Notify admin of new customer registration
     */
    public static function notifyNewCustomer($customer)
    {
        return self::create([
            'type' => 'new_customer',
            'title' => 'New Customer Registered',
            'message' => "{$customer->name} registered via " . ($customer->registration_type ?? 'app'),
            'icon' => 'person-plus',
            'color' => 'primary',
            'link' => route('admin.customers.show', $customer->id),
            'data' => [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'phone' => $customer->phone,
            ],
            'branch_id' => $customer->preferred_branch_id,
        ]);
    }

    /**
     * Notify admin of unclaimed laundry
     */
    public static function notifyUnclaimedLaundry($laundry, $daysUnclaimed)
    {
        $laundry->loadMissing('customer');

        return self::create([
            'type' => 'unclaimed',
            'title' => 'Unclaimed Laundry Alert',
            'message' => "Laundry #{$laundry->tracking_number} unclaimed for {$daysUnclaimed} days",
            'icon' => 'exclamation-triangle',
            'color' => 'warning',
            'link' => route('admin.laundries.show', $laundry->id),
            'data' => [
                'laundries_id' => $laundry->id,
                'tracking_number' => $laundry->tracking_number,
                'days_unclaimed' => $daysUnclaimed,
                'customer_name' => $laundry->customer->name,
            ],
            'branch_id' => $laundry->branch_id,
        ]);
    }

    /**
     * Financial Monitoring Notifications
     */
    public static function notifyDailyRevenue($revenue, $target, $percentage)
    {
        return self::create([
            'type' => 'daily_revenue',
            'title' => 'Daily Revenue Update',
            'message' => "₱" . number_format($revenue, 2) . " earned ({$percentage}% of target)",
            'icon' => 'graph-up',
            'color' => $percentage >= 100 ? 'success' : 'warning',
            'data' => ['revenue' => $revenue, 'target' => $target, 'percentage' => $percentage],
        ]);
    }

    public static function notifyLowCashFlow($branch, $amount)
    {
        return self::create([
            'type' => 'low_cash_flow',
            'title' => 'Low Cash Alert',
            'message' => "{$branch->name} cash below ₱" . number_format($amount, 2),
            'icon' => 'currency-dollar',
            'color' => 'danger',
            'branch_id' => $branch->id,
            'data' => ['branch_name' => $branch->name, 'amount' => $amount],
        ]);
    }

    public static function notifyPaymentOverdue($count, $totalAmount)
    {
        return self::create([
            'type' => 'payment_overdue',
            'title' => 'Overdue Payments Alert',
            'message' => "{$count} customers have overdue payments (₱" . number_format($totalAmount, 2) . ")",
            'icon' => 'exclamation-triangle',
            'color' => 'warning',
            'data' => ['count' => $count, 'total_amount' => $totalAmount],
        ]);
    }

    /**
     * Business Insights Notifications
     */
    public static function notifyBranchPerformance($branch, $metric, $value, $target)
    {
        $percentage = round(($value / $target) * 100);
        return self::create([
            'type' => 'branch_performance',
            'title' => 'Branch Performance Update',
            'message' => "{$branch->name} {$metric}: {$percentage}% of target",
            'icon' => 'speedometer2',
            'color' => $percentage >= 100 ? 'success' : 'info',
            'branch_id' => $branch->id,
            'data' => ['metric' => $metric, 'value' => $value, 'target' => $target, 'percentage' => $percentage],
        ]);
    }

    public static function notifyCustomerChurn($count, $days)
    {
        return self::create([
            'type' => 'customer_churn',
            'title' => 'Customer Retention Alert',
            'message' => "{$count} customers haven't returned in {$days} days",
            'icon' => 'people',
            'color' => 'warning',
            'data' => ['count' => $count, 'days' => $days],
        ]);
    }

    public static function notifySeasonalTrend($metric, $change, $period)
    {
        $direction = $change > 0 ? 'up' : 'down';
        $icon = $change > 0 ? 'trending-up' : 'trending-down';
        $color = $change > 0 ? 'success' : 'info';
        
        return self::create([
            'type' => 'seasonal_trend',
            'title' => 'Business Trend Alert',
            'message' => "{$metric} {$direction} " . abs($change) . "% this {$period}",
            'icon' => $icon,
            'color' => $color,
            'data' => ['metric' => $metric, 'change' => $change, 'period' => $period],
        ]);
    }

    // ===========================
    // BOOT METHOD - Add global scope to ensure dates are set
    // ===========================

    protected static function boot()
    {
        parent::boot();

        // Ensure created_at is always set when creating
        static::creating(function ($notification) {
            if (!$notification->created_at) {
                $notification->created_at = now();
            }
            if (!$notification->updated_at) {
                $notification->updated_at = now();
            }
        });
    }
}
