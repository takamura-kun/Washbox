<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    protected $fillable = [
        'event',
        'description',
        'module',
        'causer_type',
        'causer_id',
        'causer_name',
        'subject_type',
        'subject_id',
        'subject_label',
        'branch_id',
        'properties',
        'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function causer()
    {
        return $this->morphTo();
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // -------------------------------------------------------------------------
    // Static helper — call this anywhere to log an activity
    // -------------------------------------------------------------------------

    public static function log(
        string $event,
        string $description,
        string $module,
        ?Model $subject = null,
        ?array $properties = null,
        ?int $branchId = null
    ): self {
        // Resolve causer — try web guard first, then branch guard
        $causer     = Auth::user() ?? Auth::guard('branch')->user();
        $causerType = null;
        $causerId   = null;
        $causerName = 'System';

        if ($causer) {
            $causerType = get_class($causer);
            $causerId   = $causer->id;
            $causerName = $causer->name;

            // Auto-resolve branch_id from branch guard
            if (!$branchId && $causer instanceof Branch) {
                $branchId = $causer->id;
            } elseif (!$branchId && isset($causer->branch_id)) {
                $branchId = $causer->branch_id;
            }
        }

        return self::create([
            'event'         => $event,
            'description'   => $description,
            'module'        => $module,
            'causer_type'   => $causerType,
            'causer_id'     => $causerId,
            'causer_name'   => $causerName,
            'subject_type'  => $subject ? get_class($subject) : null,
            'subject_id'    => $subject?->id,
            'subject_label' => $subject ? self::resolveLabel($subject) : null,
            'branch_id'     => $branchId,
            'properties'    => $properties,
            'ip_address'    => Request::ip(),
        ]);
    }

    // -------------------------------------------------------------------------
    // Resolve a human-readable label for the subject model
    // -------------------------------------------------------------------------

    private static function resolveLabel(Model $model): string
    {
        if (!empty($model->tracking_number))       return $model->tracking_number;
        if (!empty($model->sale_number))           return $model->sale_number;
        if (!empty($model->purchase_order_number)) return $model->purchase_order_number;
        if (!empty($model->transaction_number))    return $model->transaction_number;
        if (!empty($model->name))                  return $model->name;
        if (!empty($model->title))                 return $model->title;
        return '#' . $model->id;
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeByModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
