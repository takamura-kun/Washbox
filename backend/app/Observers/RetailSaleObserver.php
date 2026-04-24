<?php

namespace App\Observers;

use App\Models\RetailSale;
use App\Models\AdminNotification;
use App\Models\BranchNotification;

class RetailSaleObserver
{
    /**
     * Handle the RetailSale "created" event.
     */
    public function created(RetailSale $sale): void
    {
        $sale->loadMissing('branch');

        // 🔔 NOTIFY ADMIN: New retail sale
        AdminNotification::create([
            'type' => 'retail_sale',
            'title' => 'New Retail Sale',
            'message' => "Retail sale {$sale->sale_number} - ₱" . number_format($sale->total_amount, 2) . " at " . ($sale->branch->name ?? 'Central'),
            'icon' => 'cart-check',
            'color' => 'success',
            'link' => route('admin.finance.retail-sales.show', $sale->id),
            'data' => [
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'total_amount' => $sale->total_amount,
            ],
            'branch_id' => $sale->branch_id,
        ]);

        // 🔔 NOTIFY BRANCH: New retail sale
        if ($sale->branch_id) {
            BranchNotification::create([
                'branch_id' => $sale->branch_id,
                'type' => 'retail_sale',
                'title' => 'Retail Sale Completed',
                'message' => "Sale {$sale->sale_number} - ₱" . number_format($sale->total_amount, 2),
                'icon' => 'cart-check',
                'color' => 'success',
                'link' => route('admin.finance.retail-sales.show', $sale->id),
                'data' => [
                    'sale_id' => $sale->id,
                    'sale_number' => $sale->sale_number,
                    'total_amount' => $sale->total_amount,
                ],
            ]);
        }

        // 🔔 HIGH-VALUE SALE ALERT (over ₱5,000)
        if ($sale->total_amount >= 5000) {
            AdminNotification::create([
                'type' => 'high_value_sale',
                'title' => '💰 High-Value Sale',
                'message' => "Large retail sale of ₱" . number_format($sale->total_amount, 2) . " at " . ($sale->branch->name ?? 'Central'),
                'icon' => 'cash-coin',
                'color' => 'success',
                'link' => route('admin.retail.sales.show', $sale->id),
                'data' => [
                    'sale_id' => $sale->id,
                    'total_amount' => $sale->total_amount,
                ],
                'branch_id' => $sale->branch_id,
            ]);
        }
    }
}
