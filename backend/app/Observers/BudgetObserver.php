<?php

namespace App\Observers;

use App\Models\Budget;
use App\Models\ActivityLog;
use App\Models\DeletedRecord;

class BudgetObserver
{
    public function deleting(Budget $budget): void
    {
        DeletedRecord::snapshot($budget, 'finance');
        ActivityLog::log('deleted', "Budget \"{$budget->name}\" deleted", 'finance', null, [
            'name'   => $budget->name,
            'amount' => $budget->amount ?? null,
        ]);
    }
}
