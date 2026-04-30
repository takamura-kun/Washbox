<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryDistribution extends Model
{
    protected $fillable = [
        'reference_no',
        'distribution_date',
        'notes',
        'distributed_by',
    ];

    protected $casts = [
        'distribution_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryDistributionItem::class);
    }

    public function distributedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }
}
