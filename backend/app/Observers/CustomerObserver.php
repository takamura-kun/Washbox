<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\AdminNotification;
use App\Models\BranchNotification;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        // 🔔 NOTIFY ADMIN: New customer registered
        AdminNotification::create([
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
                'email' => $customer->email,
            ],
            'branch_id' => $customer->preferred_branch_id,
        ]);

        // 🔔 NOTIFY BRANCH: New customer registered (if they have a preferred branch)
        if ($customer->preferred_branch_id) {
            BranchNotification::create([
                'branch_id' => $customer->preferred_branch_id,
                'type' => 'new_customer',
                'title' => 'New Customer',
                'message' => "{$customer->name} registered and selected your branch",
                'icon' => 'person-plus',
                'color' => 'primary',
                'link' => route('branch.dashboard'),
                'data' => [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'phone' => $customer->phone,
                ],
            ]);
        }
    }
}
