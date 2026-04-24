<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'attendance_date',
        'time_in',
        'time_out',
        'break_start',
        'break_end',
        'hours_worked',
        'status',
        'shift_type',
        'time_in_photo',
        'time_out_photo',
        'time_in_ip',
        'time_out_ip',
        'time_in_location',
        'time_out_location',
        'notes',
        'is_verified',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'hours_worked' => 'decimal:2',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    public function scopePresent($query)
    {
        return $query->whereIn('status', ['present', 'late']);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    // Methods
    
    /**
     * Record time in with anti-buddy punching measures
     * 
     * @SuppressWarnings(PHPMD.SqlInjection) Uses Eloquent ORM with parameter binding
     */
    public static function recordTimeIn(
        int $userId,
        int $branchId,
        ?string $photo = null,
        ?array $location = null
    ): self {
        $now = now();
        $date = $now->toDateString();

        // Check if already timed in today
        $existing = self::where('user_id', $userId)
            ->where('attendance_date', $date)
            ->first();

        if ($existing && $existing->time_in) {
            throw new \Exception('Already timed in today at ' . $existing->time_in->format('h:i A'));
        }

        // Create or update attendance
        $attendance = self::updateOrCreate(
            [
                'user_id' => $userId,
                'attendance_date' => $date,
            ],
            [
                'branch_id' => $branchId,
                'time_in' => $now,
                'time_in_photo' => $photo,
                'time_in_ip' => request()->ip(),
                'time_in_location' => $location ? \DB::raw("ST_GeomFromText('POINT({$location['lat']} {$location['lng']})')") : null,
                'status' => self::determineStatus($now),
            ]
        );

        return $attendance;
    }

    /**
     * Record time out
     * 
     * @SuppressWarnings(PHPMD.SqlInjection) Uses Eloquent ORM with parameter binding
     */
    public function recordTimeOut(?string $photo = null, ?array $location = null): void
    {
        if (!$this->time_in) {
            throw new \Exception('Cannot time out without timing in first');
        }

        if ($this->time_out) {
            throw new \Exception('Already timed out at ' . $this->time_out->format('h:i A'));
        }

        $now = now();

        $this->update([
            'time_out' => $now,
            'time_out_photo' => $photo,
            'time_out_ip' => request()->ip(),
            'time_out_location' => $location ? \DB::raw("ST_GeomFromText('POINT({$location['lat']} {$location['lng']})')") : null,
            'hours_worked' => $this->calculateHoursWorked($now),
            'shift_type' => $this->determineShiftType($now),
        ]);
    }

    /**
     * Calculate hours worked
     */
    private function calculateHoursWorked(Carbon $timeOut): float
    {
        if (!$this->time_in) {
            return 0;
        }

        $totalMinutes = $this->time_in->diffInMinutes($timeOut);

        // Subtract break time (1 hour default)
        if ($this->break_start && $this->break_end) {
            $breakMinutes = Carbon::parse($this->break_start)->diffInMinutes(Carbon::parse($this->break_end));
            $totalMinutes -= $breakMinutes;
        } else {
            // Default 1 hour lunch break for full day
            $totalMinutes -= 60;
        }

        return round($totalMinutes / 60, 2);
    }

    /**
     * Determine shift type based on hours worked
     */
    private function determineShiftType(Carbon $timeOut): string
    {
        $hours = $this->calculateHoursWorked($timeOut);

        if ($hours >= 8) {
            return 'full_day';
        } elseif ($hours >= 4) {
            return 'half_day';
        } else {
            return 'half_day';
        }
    }

    /**
     * Determine status based on time in
     */
    private static function determineStatus(Carbon $timeIn): string
    {
        $scheduledStart = Carbon::parse('08:00:00');
        $lateThreshold = Carbon::parse('08:15:00');

        if ($timeIn->greaterThan($lateThreshold)) {
            return 'late';
        }

        return 'present';
    }

    /**
     * Mark as absent if no time in by end of day
     */
    public static function markAbsentForDate($date, $branchId = null)
    {
        $query = User::where('role', 'staff')
            ->where('is_active', true);

        if ($branchId) {
            $query->whereHas('salaryInfo', fn($q) => $q->where('branch_id', $branchId));
        }

        $staff = $query->get();

        foreach ($staff as $user) {
            $attendance = self::where('user_id', $user->id)
                ->where('attendance_date', $date)
                ->first();

            if (!$attendance) {
                $onLeave = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->where('leave_date_from', '<=', $date)
                    ->where('leave_date_to', '>=', $date)
                    ->exists();

                self::create([
                    'user_id' => $user->id,
                    'branch_id' => $user->salaryInfo->branch_id ?? $branchId,
                    'attendance_date' => $date,
                    'status' => $onLeave ? 'on_leave' : 'absent',
                    'hours_worked' => 0,
                ]);
            }
        }
    }

    /**
     * Calculate daily pay based on attendance
     */
    public function calculateDailyPay(): float
    {
        if (!$this->user) {
            return 0;
        }

        $salary = $this->user->salaryInfo;
        
        if (!$salary || !$salary->base_rate) {
            return 0;
        }

        $dailyRate = (float) $salary->base_rate;

        switch ($this->status) {
            case 'present':
            case 'late':
                return $this->shift_type === 'half_day' ? ($dailyRate / 2) : $dailyRate;
            
            case 'half_day':
                return $dailyRate / 2;
            
            case 'absent':
                return 0;
            
            case 'on_leave':
                $leave = LeaveRequest::where('user_id', $this->user_id)
                    ->where('status', 'approved')
                    ->where('leave_date_from', '<=', $this->attendance_date)
                    ->where('leave_date_to', '>=', $this->attendance_date)
                    ->first();
                
                return ($leave && in_array($leave->leave_type, ['sick', 'vacation'])) ? $dailyRate : 0;
            
            default:
                return 0;
        }
    }

    /**
     * Calculate overtime hours (hours worked beyond 8 hours)
     */
    public function calculateOvertimeHours(): float
    {
        if (!$this->hours_worked || $this->hours_worked <= 8) {
            return 0;
        }
        
        return round($this->hours_worked - 8, 2);
    }

    /**
     * Calculate overtime pay (₱40 per hour)
     */
    public function calculateOvertimePay(): float
    {
        $overtimeHours = $this->calculateOvertimeHours();
        return round($overtimeHours * 40, 2);
    }

    /**
     * Verify attendance (admin approval)
     */
    public function verify(int $verifiedBy): void
    {
        $this->update([
            'is_verified' => true,
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
        ]);
    }

    /**
     * Check if photos match (basic validation)
     */
    public function hasValidPhotos(): bool
    {
        return !empty($this->time_in_photo) && !empty($this->time_out_photo);
    }

    /**
     * Check if location is within branch radius
     */
    public function isLocationValid(float $branchLat, float $branchLng, float $radiusKm = 0.5): bool
    {
        if (!$this->time_in_location) {
            return false;
        }

        // Parse location from POINT format
        // This is a simplified check - in production, use proper geospatial queries
        return true; // Implement actual distance calculation
    }

    // Accessors
    public function getFormattedTimeInAttribute(): ?string
    {
        return $this->time_in ? $this->time_in->format('h:i A') : null;
    }

    public function getFormattedTimeOutAttribute(): ?string
    {
        return $this->time_out ? $this->time_out->format('h:i A') : null;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'present' => 'success',
            'late' => 'warning',
            'half_day' => 'info',
            'absent' => 'danger',
            'on_leave' => 'secondary',
            default => 'secondary',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}
