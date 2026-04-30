<?php

namespace App\Observers;

use App\Models\Promotion;
use App\Models\ActivityLog;
use App\Models\DeletedRecord;

class PromotionObserver
{
    public function deleting(Promotion $promotion): void
    {
        DeletedRecord::snapshot($promotion, 'promotion');
        ActivityLog::log('deleted', "Promotion \"{$promotion->name}\" deleted", 'promotion', null, [
            'name'            => $promotion->name,
            'discount_type'   => $promotion->discount_type ?? null,
            'discount_value'  => $promotion->discount_value ?? null,
        ]);
    }
}
