<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\ActivityLog;
use App\Models\DeletedRecord;

class CustomerObserver
{
    public function deleting(Customer $customer): void
    {
        DeletedRecord::snapshot($customer, 'customer');
        ActivityLog::log('deleted', "Customer {$customer->name} deleted", 'customer', null, [
            'email' => $customer->email,
            'phone' => $customer->phone,
        ]);
    }
}
