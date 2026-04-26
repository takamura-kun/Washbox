<?php

namespace App\Observers;

use App\Models\Laundry;
use App\Models\AdminNotification;
use App\Models\BranchNotification;
use App\Models\ActivityLog;
use App\Models\DeletedRecord;
use App\Services\FinancialTransactionService;

class LaundryObserver
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Handle the Laundry "created" event.
     */
    public function created(Laundry $laundry)
    {
        // Load relationships
        $laundry->loadMissing('customer', 'branch', 'service');

        // 🔔 NOTIFY ADMIN: New laundry created
        AdminNotification::create([
            'type' => 'new_laundry',
            'title' => 'New Laundry Order',
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

        // 🔔 NOTIFY BRANCH: New laundry created
        if ($laundry->branch_id) {
            BranchNotification::create([
                'branch_id' => $laundry->branch_id,
                'type' => 'new_laundry',
                'title' => 'New Laundry Order',
                'message' => "Laundry #{$laundry->tracking_number} from {$laundry->customer->name} - ₱" . number_format($laundry->total_amount, 2),
                'icon' => 'bag-plus',
                'color' => 'primary',
                'link' => route('branch.laundries.show', $laundry->id),
                'data' => [
                    'laundries_id' => $laundry->id,
                    'tracking_number' => $laundry->tracking_number,
                    'customer_name' => $laundry->customer->name,
                    'total_amount' => $laundry->total_amount,
                ],
            ]);
        }

        ActivityLog::log('created', "Laundry #{$laundry->tracking_number} created for {$laundry->customer->name}", 'laundry', $laundry, [
            'tracking_number' => $laundry->tracking_number,
            'total_amount'    => $laundry->total_amount,
            'customer'        => $laundry->customer->name,
        ], $laundry->branch_id);

        // If laundry is created with paid_at already set (rare case)
        if ($laundry->paid_at !== null) {
            $this->financialService->recordLaundrySale($laundry);
            
            if (($laundry->pickup_fee > 0) || ($laundry->delivery_fee > 0)) {
                $this->financialService->recordPickupDeliveryFee($laundry);
            }
        }
    }

    /**
     * Handle the Laundry "updated" event.
     * Automatically record financial transaction when laundry is marked as paid
     */
    public function updated(Laundry $laundry)
    {
        // Load relationships
        $laundry->loadMissing('customer', 'branch', 'service');

        // Log status changes
        if ($laundry->isDirty('status')) {
            ActivityLog::log('status_changed', "Laundry #{$laundry->tracking_number} status changed from {$laundry->getOriginal('status')} to {$laundry->status}", 'laundry', $laundry, [
                'from'   => $laundry->getOriginal('status'),
                'to'     => $laundry->status,
            ], $laundry->branch_id);
        }

        // Check if paid_at was just set (laundry was just paid)
        if ($laundry->isDirty('paid_at') && $laundry->paid_at !== null) {
            // Check if financial transaction already exists
            $existingTransaction = \App\Models\FinancialTransaction::where('reference_type', Laundry::class)
                ->where('reference_id', $laundry->id)
                ->where('category', 'laundry_sale')
                ->exists();

            ActivityLog::log('paid', "Laundry #{$laundry->tracking_number} payment received ₱" . number_format($laundry->total_amount, 2), 'laundry', $laundry, [
                    'amount' => $laundry->total_amount,
                ], $laundry->branch_id);

            if (!$existingTransaction) {
                // Record laundry sale transaction
                $this->financialService->recordLaundrySale($laundry);

                // Record pickup/delivery fees if applicable
                if (($laundry->pickup_fee > 0) || ($laundry->delivery_fee > 0)) {
                    $this->financialService->recordPickupDeliveryFee($laundry);
                }
            }

            // 🔔 NOTIFY ADMIN: Payment received
            AdminNotification::create([
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

            // 🔔 NOTIFY BRANCH: Payment received
            if ($laundry->branch_id) {
                BranchNotification::create([
                    'branch_id' => $laundry->branch_id,
                    'type' => 'payment_received',
                    'title' => 'Payment Received',
                    'message' => "₱" . number_format($laundry->total_amount, 2) . " received for laundry #{$laundry->tracking_number}",
                    'icon' => 'currency-dollar',
                    'color' => 'success',
                    'link' => route('branch.laundries.show', $laundry->id),
                    'data' => [
                        'laundries_id' => $laundry->id,
                        'tracking_number' => $laundry->tracking_number,
                        'amount' => $laundry->total_amount,
                    ],
                ]);
            }
        }

        // Check if status changed
        if ($laundry->isDirty('status')) {
            $newStatus = $laundry->status;
            $oldStatus = $laundry->getOriginal('status');

            // Notify based on status change
            switch ($newStatus) {
                case 'ready':
                    // 🔔 NOTIFY ADMIN: Laundry ready
                    AdminNotification::create([
                        'type' => 'laundry_ready',
                        'title' => 'Laundry Ready for Pickup',
                        'message' => "Laundry #{$laundry->tracking_number} is ready for pickup",
                        'icon' => 'check-circle',
                        'color' => 'info',
                        'link' => route('admin.laundries.show', $laundry->id),
                        'branch_id' => $laundry->branch_id,
                    ]);

                    // 🔔 NOTIFY BRANCH: Laundry ready
                    if ($laundry->branch_id) {
                        BranchNotification::create([
                            'branch_id' => $laundry->branch_id,
                            'type' => 'laundry_ready',
                            'title' => 'Laundry Ready',
                            'message' => "Laundry #{$laundry->tracking_number} is ready for pickup",
                            'icon' => 'check-circle',
                            'color' => 'info',
                            'link' => route('branch.laundries.show', $laundry->id),
                        ]);
                    }
                    break;

                case 'completed':
                    // 🔔 NOTIFY ADMIN: Laundry completed
                    AdminNotification::create([
                        'type' => 'laundry_completed',
                        'title' => 'Laundry Completed',
                        'message' => "Laundry #{$laundry->tracking_number} has been completed",
                        'icon' => 'check-all',
                        'color' => 'success',
                        'link' => route('admin.laundries.show', $laundry->id),
                        'branch_id' => $laundry->branch_id,
                    ]);

                    // 🔔 NOTIFY BRANCH: Laundry completed
                    if ($laundry->branch_id) {
                        BranchNotification::create([
                            'branch_id' => $laundry->branch_id,
                            'type' => 'laundry_completed',
                            'title' => 'Laundry Completed',
                            'message' => "Laundry #{$laundry->tracking_number} has been completed",
                            'icon' => 'trophy',
                            'color' => 'success',
                            'link' => route('branch.laundries.show', $laundry->id),
                        ]);
                    }
                    break;

                case 'cancelled':
                    // 🔔 NOTIFY ADMIN: Laundry cancelled
                    AdminNotification::create([
                        'type' => 'laundry_cancelled',
                        'title' => 'Laundry Cancelled',
                        'message' => "Laundry #{$laundry->tracking_number} from {$laundry->customer->name} was cancelled",
                        'icon' => 'x-circle',
                        'color' => 'danger',
                        'link' => route('admin.laundries.show', $laundry->id),
                        'data' => [
                            'laundries_id' => $laundry->id,
                            'tracking_number' => $laundry->tracking_number,
                            'reason' => $laundry->cancellation_reason,
                        ],
                        'branch_id' => $laundry->branch_id,
                    ]);

                    // 🔔 NOTIFY BRANCH: Laundry cancelled
                    if ($laundry->branch_id) {
                        BranchNotification::create([
                            'branch_id' => $laundry->branch_id,
                            'type' => 'laundry_cancelled',
                            'title' => 'Laundry Cancelled',
                            'message' => "Laundry #{$laundry->tracking_number} was cancelled",
                            'icon' => 'x-circle',
                            'color' => 'danger',
                            'link' => route('branch.laundries.show', $laundry->id),
                        ]);
                    }
                    break;
            }

            // Auto-update inventory cost when status changes to paid/completed
            if (in_array($newStatus, ['paid', 'completed'])) {
                if ($laundry->inventoryItems()->count() > 0 && is_null($laundry->inventory_cost)) {
                    $inventoryCost = $laundry->calculateInventoryCost();
                    $laundry->inventory_cost = $inventoryCost;
                    $laundry->saveQuietly(); // Save without triggering events
                }
            }
        }
    }

    public function deleting(Laundry $laundry): void
    {
        $laundry->loadMissing('customer');
        DeletedRecord::snapshot($laundry, 'laundry');
        ActivityLog::log('deleted', "Laundry #{$laundry->tracking_number} deleted", 'laundry', null, [
            'tracking_number' => $laundry->tracking_number,
            'amount'          => $laundry->total_amount,
        ], $laundry->branch_id);
    }
}
