<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class DeletedRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'model_type',
        'model_label',
        'module',
        'original_id',
        'data',
        'deleted_by_name',
        'deleted_by_type',
        'deleted_by_id',
        'branch_id',
        'ip_address',
        'deleted_at',
    ];

    protected $casts = [
        'data'       => 'array',
        'deleted_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Call this inside a model's `deleting` observer BEFORE the record is gone
    // -------------------------------------------------------------------------

    public static function snapshot(Model $model, string $module, ?string $label = null): self
    {
        $causer     = Auth::user() ?? Auth::guard('branch')->user();
        $branchId   = null;

        if ($causer) {
            if ($causer instanceof Branch) {
                $branchId = $causer->id;
            } elseif (isset($causer->branch_id)) {
                $branchId = $causer->branch_id;
            }
        }

        // Try to resolve branch_id from the model itself if not from causer
        if (!$branchId && isset($model->branch_id)) {
            $branchId = $model->branch_id;
        }

        return self::create([
            'model_type'      => get_class($model),
            'model_label'     => $label ?? self::resolveLabel($model),
            'module'          => $module,
            'original_id'     => $model->id,
            'data'            => $model->toArray(),
            'deleted_by_name' => $causer?->name ?? 'System',
            'deleted_by_type' => $causer ? get_class($causer) : null,
            'deleted_by_id'   => $causer?->id,
            'branch_id'       => $branchId,
            'ip_address'      => Request::ip(),
            'deleted_at'      => now(),
        ]);
    }

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

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
