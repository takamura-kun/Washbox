<?php

namespace App\Providers;

use App\Models\Laundry;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Expense;
use App\Models\BranchStock;
use App\Models\StockTransfer;
use App\Models\RetailSale;
use App\Models\PayrollItem;
use App\Models\LeaveRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Budget;
use App\Models\InventoryPurchase;
use App\Models\Service;
use App\Models\InventoryItem;
use Laravel\Sanctum\Sanctum;
use App\Observers\LaundryObserver;
use App\Observers\CustomerObserver;
use App\Observers\PromotionObserver;
use App\Observers\ExpenseObserver;
use App\Observers\BranchStockObserver;
use App\Observers\StockTransferObserver;
use App\Observers\RetailSaleObserver;
use App\Observers\PayrollObserver;
use App\Observers\LeaveRequestObserver;
use App\Observers\AttendanceObserver;
use App\Observers\UserObserver;
use App\Observers\BudgetObserver;
use App\Observers\InventoryPurchaseObserver;
use App\Observers\ServiceObserver;
use App\Observers\InventoryItemObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Models\PickupRequest;
use Laravel\Sanctum\PersonalAccessToken;
use App\Observers\PickupRequestObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(\Laravel\Sanctum\PersonalAccessToken::class);

        // Register all observers
        PickupRequest::observe(PickupRequestObserver::class);
        Promotion::observe(PromotionObserver::class);
        Laundry::observe(LaundryObserver::class);
        Customer::observe(CustomerObserver::class);
        Expense::observe(ExpenseObserver::class);
        BranchStock::observe(BranchStockObserver::class);
        StockTransfer::observe(StockTransferObserver::class);
        RetailSale::observe(RetailSaleObserver::class);
        PayrollItem::observe(PayrollObserver::class);
        LeaveRequest::observe(LeaveRequestObserver::class);
        Attendance::observe(AttendanceObserver::class);
        User::observe(UserObserver::class);
        Budget::observe(BudgetObserver::class);
        InventoryPurchase::observe(InventoryPurchaseObserver::class);
        Service::observe(ServiceObserver::class);
        InventoryItem::observe(InventoryItemObserver::class);

        if (!app()->runningInConsole() && Schema::hasTable('branches')) {
            view()->share('branches', Branch::all());
            Paginator::useBootstrapFive();
        }
    }
}
