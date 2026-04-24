<?php

/*
|--------------------------------------------------------------------------
| Web Routes - CSRF Protection
|--------------------------------------------------------------------------
|
| All routes in this file are automatically protected against CSRF attacks
| by Laravel's VerifyCsrfToken middleware, which is part of the 'web'
| middleware group. This middleware validates CSRF tokens for all POST,
| PUT, PATCH, and DELETE requests.
|
| CSRF tokens are automatically included in forms via @csrf Blade directive
| and in AJAX requests via the X-CSRF-TOKEN header.
|
*/

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BranchRatingController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LaundryController;
use App\Http\Controllers\Admin\LogisticsController;
use App\Http\Controllers\Admin\PickupRequestController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ReceiptController;
use App\Http\Controllers\Admin\ServiceTypeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\InventoryItemController;
use App\Http\Controllers\Admin\InventoryCategoryController;
use App\Http\Controllers\Admin\InventoryPurchaseController;
use App\Http\Controllers\Admin\InventoryDistributionController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\FinanceReportController;
use App\Http\Controllers\Branch\Auth\LoginController as BranchLoginController;

use App\Http\Controllers\Branch\AnalyticsController as BranchAnalyticsController;
use App\Http\Controllers\Branch\CustomerController as BranchCustomerController;
use App\Http\Controllers\Branch\DashboardController as BranchDashboardController;
use App\Http\Controllers\Branch\LaundryController as BranchLaundryController;
use App\Http\Controllers\Branch\NotificationController as BranchNotificationController;
use App\Http\Controllers\Branch\PickupRequestController as BranchPickupController;
use App\Http\Controllers\Branch\PromotionController as BranchPromotionController;
use App\Http\Controllers\Branch\RatingController as BranchStaffRatingController;
use App\Http\Controllers\Branch\ReportController as BranchReportController;
use App\Http\Controllers\Branch\ServiceController as BranchServiceController;
use App\Http\Controllers\Branch\SettingsController as BranchSettingsController;
use App\Http\Controllers\Branch\UnclaimedController as BranchUnclaimedController;
use App\Http\Controllers\Branch\PaymentVerificationController as BranchPaymentVerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

Route::get('/', function () {
    // Redirect to admin login instead of showing welcome page
    return redirect()->route('admin.login');
})->name('welcome');

// ============================================================================
// ADMIN AUTHENTICATION ROUTES (Guest Only)
// ============================================================================

Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::get('/forgot-password', [AdminLoginController::class, 'showForgotPasswordForm'])->name('forgot-password');
    Route::post('/forgot-password', [AdminLoginController::class, 'sendResetLinkEmail'])->name('forgot-password.email');
});

// ============================================================================
// ADMIN AUTHENTICATED ROUTES
// ============================================================================

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');

    // Pickup API for route optimization
    Route::post('/pickups/by-ids', [App\Http\Controllers\Api\PickupController::class, 'getByIds'])->name('pickups.by-ids');


    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ========================================================================
    // PERFORMANCE REPORT (Replaced old Analytics)
    // ========================================================================
    // Main route: /admin/performance-report
    Route::get('/performance-report', [App\Http\Controllers\Admin\PerformanceReportController::class, 'index'])->name('performance-report');
    
    // Alias for backward compatibility (redirects to performance-report)
    Route::get('/analytics', function() {
        return redirect()->route('admin.performance-report');
    })->name('analytics');
    
    // OLD Analytics Routes (Preserved for future use - COMMENTED OUT)
    // Route::post('/analytics/refresh', [AnalyticsController::class, 'refresh'])->name('analytics.refresh');
    // Route::get('/api/analytics/historical', [AnalyticsController::class, 'historical'])->name('api.analytics.historical');
    // Route::get('/api/analytics/customer-behavior', [AnalyticsController::class, 'customerBehavior'])->name('api.analytics.customer-behavior');
    // Route::get('/api/analytics/realtime', [AnalyticsController::class, 'realtime'])->name('api.analytics.realtime');

    // ========================================================================
    // PROMOTIONS (FIXED ROUTE NAME)
    // ========================================================================
    Route::resource('promotions', PromotionController::class);
    Route::patch('promotions/{promotion}/toggle', [PromotionController::class, 'toggleStatus'])
        ->name('promotions.toggleStatus');
    Route::get('promotions-analytics', [PromotionController::class, 'analytics'])
        ->name('promotions.analytics');
    
    // Promotion Items Management
    Route::prefix('promotions/{promotion}')->name('promotions.')->group(function () {
        Route::get('/items', [PromotionController::class, 'manageItems'])->name('items');
        Route::post('/items', [PromotionController::class, 'addItem'])->name('add-item');
        Route::delete('/items/{item}', [PromotionController::class, 'removeItem'])->name('remove-item');
    });

    // ========================================================================
    // LOGISTICS
    // ========================================================================
    Route::prefix('pickups')->name('pickups.')->group(function () {
        Route::get('/{pickup}/route', [LogisticsController::class, 'getPickupRoute'])
            ->name('route');
        Route::get('/{pickup}/directions', [LogisticsController::class, 'getDirections'])
            ->name('directions');
        Route::post('/{pickup}/start-navigation', [LogisticsController::class, 'startNavigation'])
            ->name('start-navigation');
        Route::post('/{pickup}/complete', [LogisticsController::class, 'completePickup'])
            ->name('complete');
    });

    Route::prefix('logistics')->name('logistics.')->group(function () {
        Route::post('/optimize-route', [LogisticsController::class, 'getOptimizedRoute'])
            ->name('optimize-route');
        Route::post('/create-route', [LogisticsController::class, 'createDeliveryRoute'])
            ->name('create-route');
        Route::get('/active-routes', [LogisticsController::class, 'getActiveRoutes'])
            ->name('active-routes');
        Route::get('/stats', [LogisticsController::class, 'getRouteStats'])
            ->name('stats');
        Route::get('/admin/pickups/{pickup}/route', [LogisticsController::class, 'getPickupRoute']);

        Route::post('/start-multi-pickup', [LogisticsController::class, 'startMultiPickup'])
            ->name('start-multi-pickup');
    });

    Route::get('/admin/pickups/{pickup}/route', [LogisticsController::class, 'getPickupRoute']);

    // ========================================================================
    // LAUNDRIES
    // ========================================================================
    Route::resource('laundries', LaundryController::class);
    Route::get('/receipts/{laundry}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::post('/laundries/{laundry}/assign-staff', [LaundryController::class, 'assignStaff'])->name('laundries.assign-staff');
    Route::post('/laundries/{laundry}/mark-paid', [LaundryController::class, 'markPaid'])->name('laundries.mark-paid');
    Route::post('/laundries/{laundry}/mark-unclaimed', [LaundryController::class, 'markUnclaimed'])->name('laundries.mark-unclaimed');
    Route::put('/laundries/{laundry}/status', [LaundryController::class, 'updateStatus'])->name('laundries.update-status');
    Route::post('/laundries/{laundry}/record-payment', [LaundryController::class, 'recordPayment'])->name('laundries.record-payment');
    Route::post('/laundries/{laundry}/payment', [LaundryController::class, 'storePayment'])->name('laundries.store-payment');

    // ========================================================================
    // CUSTOMERS
    // ========================================================================
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/{customer}/laundries', [CustomerController::class, 'laundries'])->name('customers.laundries');
    Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::post('customers/export-orders', [CustomerController::class, 'exportOrders'])->name('customers.export-orders');


    // ========================================================================
    // BRANCHES
    // ========================================================================
    Route::resource('branches', BranchController::class);
    Route::get('/branches/{branch}/analytics', [BranchController::class, 'analytics'])->name('branches.analytics');
    Route::post('/branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle-status');
    Route::patch('/branches/{branch}/deactivate', [BranchController::class, 'deactivate'])->name('branches.deactivate');
    Route::patch('/branches/{branch}/activate', [BranchController::class, 'activate'])->name('branches.activate');
    Route::post('/branches/{branch}/reset-password', [BranchController::class, 'resetPassword'])->name('branches.reset-password');


// ========================================================================
// SERVICE TYPES ROUTES - ADD THESE NEW ROUTES
// ========================================================================
Route::resource('service-types', ServiceTypeController::class);
Route::post('service-types/{serviceType}/toggle-status', [ServiceTypeController::class, 'toggleStatus'])->name('service-types.toggle-status');
Route::get('service-types/by-category/{category}', [ServiceTypeController::class, 'getByCategory'])->name('service-types.by-category');

    // ========================================================================
    // SERVICES
    // ========================================================================
    Route::resource('services', ServiceController::class);
    Route::post('services/{service}/toggle-status', [ServiceController::class, 'toggleStatus'])->name('services.toggle-status');

    // ========================================================================
    // STAFF MANAGEMENT
    // ========================================================================
    Route::resource('staff', StaffController::class);
    Route::post('/staff/{user}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle-status');
    Route::post('/staff/{user}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset-password');
    Route::post('/staff/{user}/salary', [StaffController::class, 'updateSalary'])->name('staff.update-salary');
    Route::delete('/staff/{user}/salary', [StaffController::class, 'deleteSalary'])->name('staff.delete-salary');
    Route::get('/staff-salary-management', [StaffController::class, 'salaryManagement'])->name('staff.salary-management');
    Route::post('/staff-bulk-salary-update', [StaffController::class, 'bulkSalaryUpdate'])->name('staff.bulk-salary-update');

    // ========================================================================
    // ATTENDANCE MANAGEMENT
    // ========================================================================
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\AttendanceController::class, 'index'])->name('index');
        Route::post('/time-in', [App\Http\Controllers\Admin\AttendanceController::class, 'timeIn'])->name('time-in');
        Route::post('/{attendance}/time-out', [App\Http\Controllers\Admin\AttendanceController::class, 'timeOut'])->name('time-out');
        Route::post('/manual-entry', [App\Http\Controllers\Admin\AttendanceController::class, 'manualEntry'])->name('manual-entry');
        Route::post('/{attendance}/verify', [App\Http\Controllers\Admin\AttendanceController::class, 'verify'])->name('verify');
        Route::post('/bulk-verify', [App\Http\Controllers\Admin\AttendanceController::class, 'bulkVerify'])->name('bulk-verify');
        Route::get('/report', [App\Http\Controllers\Admin\AttendanceController::class, 'report'])->name('report');
        Route::post('/mark-absent', [App\Http\Controllers\Admin\AttendanceController::class, 'markAbsent'])->name('mark-absent');
    });

    // ========================================================================
    // LEAVE MANAGEMENT
    // ========================================================================
    Route::prefix('leave-requests')->name('leave-requests.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\LeaveRequestController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\LeaveRequestController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\LeaveRequestController::class, 'store'])->name('store');
        Route::get('/{leaveRequest}', [App\Http\Controllers\Admin\LeaveRequestController::class, 'show'])->name('show');
        Route::post('/{leaveRequest}/approve', [App\Http\Controllers\Admin\LeaveRequestController::class, 'approve'])->name('approve');
        Route::post('/{leaveRequest}/reject', [App\Http\Controllers\Admin\LeaveRequestController::class, 'reject'])->name('reject');
    });

    // ========================================================================
    // INVENTORY MANAGEMENT
    // ========================================================================
    Route::prefix('inventory')->name('inventory.')->group(function () {
        // API endpoint for supplies
        Route::get('/supplies-api/active', [InventoryController::class, 'getActiveSupplies'])->name('supplies-api.active');

        // Warehouse (main inventory)
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('/dashboard', [InventoryController::class, 'dashboard'])->name('dashboard');
        Route::get('/manage', [InventoryItemController::class, 'index'])->name('manage');

        // Categories
        Route::resource('categories', InventoryCategoryController::class)->except(['show']);

        // Items
        Route::resource('items', InventoryItemController::class)->except(['index']);

        // Purchases
        Route::get('/purchases', [InventoryPurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/create', [InventoryPurchaseController::class, 'create'])->name('purchases.create');
        Route::post('/purchases', [InventoryPurchaseController::class, 'store'])->name('purchases.store');
        Route::get('/purchases/{purchase}', [InventoryPurchaseController::class, 'show'])->name('purchases.show');

        // Distribution
        Route::get('/distribute', [InventoryDistributionController::class, 'index'])->name('distribute.index');
        Route::post('/distribute', [InventoryDistributionController::class, 'store'])->name('distribute.store');
        Route::get('/dist-log', [InventoryDistributionController::class, 'log'])->name('dist-log');

        // Branch stock
        Route::get('/branch-stock', [InventoryController::class, 'branchStock'])->name('branch-stock');

        // Stock Transfers (Branch Requests)
        Route::prefix('stock-transfers')->name('stock-transfers.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\StockTransferController::class, 'index'])->name('index');
            Route::post('/{transfer}/approve', [App\Http\Controllers\Admin\StockTransferController::class, 'approve'])->name('approve');
            Route::post('/{transfer}/reject', [App\Http\Controllers\Admin\StockTransferController::class, 'reject'])->name('reject');
            Route::post('/bulk-approve', [App\Http\Controllers\Admin\StockTransferController::class, 'bulkApprove'])->name('bulk-approve');
        });

        // Stock Adjustments
        Route::prefix('adjustments')->name('adjustments.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\InventoryAdjustmentController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\InventoryAdjustmentController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\InventoryAdjustmentController::class, 'store'])->name('store');
            Route::get('/{adjustment}', [App\Http\Controllers\Admin\InventoryAdjustmentController::class, 'show'])->name('show');
            Route::post('/{adjustment}/approve', [App\Http\Controllers\Admin\InventoryAdjustmentController::class, 'approve'])->name('approve');
            Route::post('/{adjustment}/reject', [App\Http\Controllers\Admin\InventoryAdjustmentController::class, 'reject'])->name('reject');
            Route::post('/bulk-approve', [App\Http\Controllers\Admin\InventoryAdjustmentController::class, 'bulkApprove'])->name('bulk-approve');
            Route::get('/branch/{branchId}/items', [App\Http\Controllers\Admin\InventoryAdjustmentController::class, 'getBranchItems'])->name('branch-items');
        });

        // Stock Movement Report
        Route::prefix('movements')->name('movements.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\InventoryMovementController::class, 'index'])->name('index');
        });
    });

    // ========================================================================
    // FINANCE MANAGEMENT
    // ========================================================================
    Route::prefix('finance')->name('finance.')->group(function () {
        // Dashboard
        Route::get('/', [App\Http\Controllers\Admin\FinancialDashboardController::class, 'index'])->name('dashboard');

        // Sales
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\FinancialDashboardController::class, 'salesReport'])->name('index');
        });

        // Expenses
        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\ExpenseController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\ExpenseController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\ExpenseController::class, 'store'])->name('store');
            Route::get('/report', [App\Http\Controllers\Admin\FinancialDashboardController::class, 'expenseReport'])->name('report');
            Route::post('/category', [App\Http\Controllers\Admin\ExpenseController::class, 'storeCategory'])->name('store-category');
        });

        // Payroll
        Route::prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/', [PayrollController::class, 'index'])->name('index');
            Route::get('/create', [PayrollController::class, 'create'])->name('create');
            Route::post('/', [PayrollController::class, 'store'])->name('store');
            Route::get('/{period}', [PayrollController::class, 'show'])->name('show');
            Route::put('/{item}/update-item', [PayrollController::class, 'updateItem'])->name('update-item');
            Route::post('/{period}/approve', [PayrollController::class, 'approve'])->name('approve');
            Route::post('/{period}/mark-paid', [PayrollController::class, 'markAsPaid'])->name('mark-paid');
            Route::delete('/{period}', [PayrollController::class, 'destroy'])->name('destroy');
        });

        // Financial Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/profit-loss', [App\Http\Controllers\Admin\FinancialDashboardController::class, 'profitLossReport'])->name('profit-loss');
        });

        // Financial Ledger
        Route::prefix('ledger')->name('ledger.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\FinancialLedgerController::class, 'index'])->name('index');
            Route::get('/export', [App\Http\Controllers\Admin\FinancialLedgerController::class, 'export'])->name('export');
            // Route::get('/pending', [App\Http\Controllers\Admin\FinancialLedgerController::class, 'pending'])->name('pending'); // Disabled
            // Route::post('/create', [App\Http\Controllers\Admin\FinancialLedgerController::class, 'create'])->name('create'); // Disabled
            Route::get('/{transaction}', [App\Http\Controllers\Admin\FinancialLedgerController::class, 'show'])->name('show');
            // Route::post('/{transaction}/approve', [App\Http\Controllers\Admin\FinancialLedgerController::class, 'approve'])->name('approve'); // Disabled
            // Route::post('/{transaction}/reject', [App\Http\Controllers\Admin\FinancialLedgerController::class, 'reject'])->name('reject'); // Disabled
            Route::post('/{transaction}/reverse', [App\Http\Controllers\Admin\FinancialLedgerController::class, 'reverse'])->name('reverse');
        });

        // Budgets
        Route::prefix('budgets')->name('budgets.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\BudgetController::class, 'index'])->name('index');
            Route::get('/create', [App\Http\Controllers\Admin\BudgetController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\BudgetController::class, 'store'])->name('store');
            Route::get('/{budget}', [App\Http\Controllers\Admin\BudgetController::class, 'show'])->name('show');
            Route::get('/{budget}/edit', [App\Http\Controllers\Admin\BudgetController::class, 'edit'])->name('edit');
            Route::put('/{budget}', [App\Http\Controllers\Admin\BudgetController::class, 'update'])->name('update');
            Route::delete('/{budget}', [App\Http\Controllers\Admin\BudgetController::class, 'destroy'])->name('destroy');
            Route::post('/{budget}/refresh', [App\Http\Controllers\Admin\BudgetController::class, 'refresh'])->name('refresh');
        });

        // Cash Flow
        Route::prefix('cash-flow')->name('cash-flow.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\CashFlowController::class, 'index'])->name('index');
            Route::post('/generate', [App\Http\Controllers\Admin\CashFlowController::class, 'generate'])->name('generate');
            Route::post('/generate-range', [App\Http\Controllers\Admin\CashFlowController::class, 'generateRange'])->name('generate-range');
            Route::get('/{cashFlowRecord}', [App\Http\Controllers\Admin\CashFlowController::class, 'show'])->name('show');
        });

        // Audit Logs
        Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\FinancialAuditController::class, 'index'])->name('index');
            Route::get('/{log}', [App\Http\Controllers\Admin\FinancialAuditController::class, 'show'])->name('show');
        });
    });

    // ========================================================================
    // RETAIL SALES MANAGEMENT
    // ========================================================================
    Route::prefix('finance/retail-sales')->name('finance.retail-sales.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\RetailSaleController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Admin\RetailSaleController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Admin\RetailSaleController::class, 'store'])->name('store');
        Route::get('/{retail}', [App\Http\Controllers\Admin\RetailSaleController::class, 'show'])->name('show');
        Route::delete('/{retail}', [App\Http\Controllers\Admin\RetailSaleController::class, 'destroy'])->name('destroy');
        Route::get('/items/{branchId}', [App\Http\Controllers\Admin\RetailSaleController::class, 'getAvailableItems'])->name('items');
    });

    // ========================================================================
    // UNCLAIMED LAUNDRY MANAGEMENT
    // ========================================================================
    Route::prefix('unclaimed')->name('unclaimed.')->group(function () {
        // Static routes FIRST
        Route::get('/history', [App\Http\Controllers\Admin\UnclaimedController::class, 'disposalHistory'])->name('history');
        Route::get('/remind-all', [App\Http\Controllers\Admin\UnclaimedController::class, 'remindAll'])->name('remindAll');
        Route::get('/export', [App\Http\Controllers\Admin\UnclaimedController::class, 'export'])->name('export');
        Route::get('/stats', [App\Http\Controllers\Admin\UnclaimedController::class, 'stats'])->name('stats');
        Route::post('/bulk-reminders', [App\Http\Controllers\Admin\UnclaimedController::class, 'sendBulkReminders'])->name('bulk-reminders');

        // Main list
        Route::get('/', [App\Http\Controllers\Admin\UnclaimedController::class, 'index'])->name('index');

        // Dynamic routes LAST
        Route::get('/{laundry}', [App\Http\Controllers\Admin\UnclaimedController::class, 'show'])->name('show');
        Route::post('/{id}/send-reminder', [App\Http\Controllers\Admin\UnclaimedController::class, 'sendReminder'])->name('send-reminder');
        Route::post('/{id}/mark-claimed', [App\Http\Controllers\Admin\UnclaimedController::class, 'markClaimed'])->name('mark-claimed');
        Route::post('/{id}/mark-disposed', [App\Http\Controllers\Admin\UnclaimedController::class, 'markDisposed'])->name('mark-disposed');
    });

    // ========================================================================
    // NOTIFICATIONS
    // ========================================================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
        Route::get('/recent', [App\Http\Controllers\Admin\NotificationController::class, 'getRecent'])->name('recent');
        Route::get('/unread-count', [App\Http\Controllers\Admin\NotificationController::class, 'getUnreadCount'])->name('unread-count');

        // Individual notification actions
        Route::post('/{notification}/mark-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::delete('/{notification}', [App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('destroy');

        // Bulk actions
        Route::post('/mark-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/delete-all-read', [App\Http\Controllers\Admin\NotificationController::class, 'deleteAllRead'])->name('delete-all-read');  // Changed to DELETE
    });
    // ========================================================================
    // PICKUP REQUESTS
    // ========================================================================
    Route::prefix('pickups')->name('pickups.')->group(function () {
        // Confirm Dashboard
        Route::get('/confirm', [PickupRequestController::class, 'confirmView'])->name('confirm');
        Route::post('/{id}/confirm-quick', [PickupRequestController::class, 'confirmQuick'])->name('confirm-quick');
        Route::post('/confirm-all', [PickupRequestController::class, 'confirmAll'])->name('confirm-all');

        // List & View
        Route::get('/', [PickupRequestController::class, 'index'])->name('index');
        Route::get('/{id}', [PickupRequestController::class, 'show'])->name('show');

        // Actions
        Route::put('/{id}/accept', [PickupRequestController::class, 'accept'])->name('accept');
        Route::put('/{id}/en-route', [PickupRequestController::class, 'markEnRoute'])->name('en-route');
        Route::post('/{id}/upload-proof', [PickupRequestController::class, 'uploadProof'])->name('upload-proof');
        Route::put('/{id}/picked-up', [PickupRequestController::class, 'markPickedUp'])->name('picked-up');
        Route::put('/{id}/cancel', [PickupRequestController::class, 'cancel'])->name('cancel');

        // Staff Assignment
        Route::put('/{id}/assign-staff', [PickupRequestController::class, 'assignStaff'])->name('assign-staff');

        // Bulk Actions
        Route::post('/bulk-accept', [PickupRequestController::class, 'bulkAccept'])->name('bulk-accept');

        // Statistics
        Route::get('/stats/data', [PickupRequestController::class, 'stats'])->name('stats');
    });


    // ========================================================================
    // RATINGS
    // ========================================================================
    Route::prefix('ratings')->name('ratings.')->group(function () {
        Route::get('/', [BranchRatingController::class, 'index'])->name('index');
        Route::get('/branches', [BranchRatingController::class, 'index'])->name('branches');
        Route::get('/branches/{branch}', [BranchRatingController::class, 'show'])->name('branches.show');
        Route::delete('/{rating}', [BranchRatingController::class, 'destroy'])->name('destroy');
    });
// ========================================================================
// REPORTS
// ========================================================================
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
    Route::get('/profitability', [ReportController::class, 'profitability'])->name('profitability');
    Route::get('/laundries', [ReportController::class, 'laundries'])->name('laundries');
    Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
    Route::get('/branches', [ReportController::class, 'branches'])->name('branches');
    Route::get('/branch-ratings', [ReportController::class, 'branchRatings'])->name('branch-ratings');
    Route::get('/branch-ratings/export', [ReportController::class, 'exportBranchRatings'])->name('branch-ratings.export');
    Route::post('/export', [ReportController::class, 'export'])->name('export');
});
    // ========================================================================
    // PAYMENT VERIFICATION
    // ========================================================================
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/verification', [App\Http\Controllers\Admin\PaymentVerificationController::class, 'index'])->name('verification.index');
        Route::get('/verification/{paymentProof}', [App\Http\Controllers\Admin\PaymentVerificationController::class, 'show'])->name('verification.show');
        Route::post('/verification/{paymentProof}/approve', [App\Http\Controllers\Admin\PaymentVerificationController::class, 'approve'])->name('verification.approve');
        Route::post('/verification/{paymentProof}/reject', [App\Http\Controllers\Admin\PaymentVerificationController::class, 'reject'])->name('verification.reject');
        Route::post('/verification/bulk-approve', [App\Http\Controllers\Admin\PaymentVerificationController::class, 'bulkApprove'])->name('verification.bulk-approve');
    });

    // ========================================================================
    // RECEIPTS
    // ========================================================================
    Route::get('/receipts', function () {
        return view('admin.receipts.index');
    })->name('receipts.index');
    Route::get('/receipts/{laundry}', [ReceiptController::class, 'show'])->name('receipts.show');

    // ========================================================================
    // SETTINGS (ADDED DIRECT ROUTE FOR NAVIGATION)
    // ========================================================================
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::post('/', [SettingsController::class, 'update'])->name('update');
        Route::post('/cleanup', [SettingsController::class, 'cleanup'])->name('cleanup');
        Route::post('/backup-cleanup', [SettingsController::class, 'cleanupBackups'])->name('backup-cleanup');
        Route::post('/fcm', [SettingsController::class, 'updateFCM'])->name('fcm');
        Route::post('/email', [SettingsController::class, 'updateEmail'])->name('email');
        Route::post('/sms', [SettingsController::class, 'updateSMS'])->name('sms');
        Route::post('/backup', [SettingsController::class, 'backup'])->name('backup');
        Route::get('/download-backup/{filename}', [SettingsController::class, 'downloadBackup'])->name('download-backup');
        Route::post('/notifications', [SettingsController::class, 'updateNotifications'])->name('notifications');
        Route::post('/apply-hours-to-branches', [SettingsController::class, 'applyHoursToBranches'])->name('apply-hours-to-branches');
        
        // Extra Services Settings
        Route::get('/extra-services', [SettingsController::class, 'extraServices'])->name('extra-services');
        Route::put('/extra-services', [SettingsController::class, 'updateExtraServices'])->name('extra-services.update');
    });

    // ========================================================================
    // PROFILE
    // ========================================================================
    Route::get('/profile', [SettingsController::class, 'profile'])->name('profile');
    Route::post('/profile', [SettingsController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [SettingsController::class, 'updatePassword'])->name('profile.password');
});

// ============================================================================
// BRANCH AUTHENTICATION ROUTES (Guest Only)
// ============================================================================

Route::middleware('guest')->prefix('branch')->name('branch.')->group(function () {
    Route::get('/login', [BranchLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [BranchLoginController::class, 'login']);
});

// ============================================================================
// STAFF ROUTES (Redirect to Branch)
// ============================================================================

Route::middleware('guest')->prefix('staff')->name('staff.')->group(function () {
    Route::get('/login', function() {
        return redirect()->route('branch.login')->with('info', 'Please use your branch credentials to login.');
    })->name('login');
    Route::post('/login', function() {
        return redirect()->route('branch.login');
    });
});

// ============================================================================
// BRANCH AUTHENTICATED ROUTES
// ============================================================================

Route::middleware(['auth:branch', 'branch'])->prefix('branch')->name('branch.')->group(function () {

    // ========================================================================
    // DASHBOARD
    // ========================================================================
    Route::get('/dashboard', [BranchDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [BranchDashboardController::class, 'export'])->name('dashboard.export');

    // ========================================================================
    // AUTHENTICATION
    // ========================================================================
    Route::post('/logout', [BranchLoginController::class, 'logout'])->name('logout');

    // ========================================================================
    // LAUNDRY MANAGEMENT
    // ========================================================================
    Route::resource('laundries', BranchLaundryController::class)->except(['destroy']);
    Route::post('/laundries/{laundry}/status', [BranchLaundryController::class, 'updateStatus'])->name('laundries.update-status');
    Route::get('/laundries/{laundry}/receipt', [BranchLaundryController::class, 'receipt'])->name('laundries.receipt');
    Route::post('/laundries/{laundry}/record-payment', [BranchLaundryController::class, 'recordPayment'])->name('laundries.record-payment');

    // ========================================================================
    // CUSTOMER MANAGEMENT
    // ========================================================================
    Route::resource('customers', BranchCustomerController::class);

    // ========================================================================
    // BRANCH ATTENDANCE MANAGEMENT
    // ========================================================================
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [App\Http\Controllers\Branch\AttendanceController::class, 'index'])->name('index');
        Route::post('/time-in', [App\Http\Controllers\Branch\AttendanceController::class, 'timeIn'])->name('time-in');
        Route::post('/{attendance}/time-out', [App\Http\Controllers\Branch\AttendanceController::class, 'timeOut'])->name('time-out');
        Route::post('/manual-entry', [App\Http\Controllers\Branch\AttendanceController::class, 'manualEntry'])->name('manual-entry');
        Route::get('/report', [App\Http\Controllers\Branch\AttendanceController::class, 'report'])->name('report');
        Route::post('/mark-absent', [App\Http\Controllers\Branch\AttendanceController::class, 'markAbsent'])->name('mark-absent');
        Route::post('/submit-leave', [App\Http\Controllers\Branch\AttendanceController::class, 'submitLeave'])->name('submit-leave');
        Route::get('/leave-requests', [App\Http\Controllers\Branch\AttendanceController::class, 'leaveRequests'])->name('leave-requests');
    });

    // ========================================================================
    // BRANCH INVENTORY MANAGEMENT
    // ========================================================================
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [App\Http\Controllers\Branch\InventoryController::class, 'index'])->name('index');
        Route::get('/export', [App\Http\Controllers\Branch\InventoryController::class, 'export'])->name('export');
        Route::get('/requests', [App\Http\Controllers\Branch\InventoryController::class, 'requests'])->name('requests');
        Route::get('/history', [App\Http\Controllers\Branch\InventoryController::class, 'history'])->name('history');
        Route::get('/low-stock', [App\Http\Controllers\Branch\InventoryController::class, 'lowStock'])->name('low-stock');
        Route::get('/out-of-stock', [App\Http\Controllers\Branch\InventoryController::class, 'outOfStock'])->name('out-of-stock');
        Route::get('/item/{item}', [App\Http\Controllers\Branch\InventoryController::class, 'itemDetails'])->name('item-details');
        Route::post('/request-stock', [App\Http\Controllers\Branch\InventoryController::class, 'requestStock'])->name('request-stock');
        Route::post('/record-usage', [App\Http\Controllers\Branch\InventoryController::class, 'recordUsage'])->name('record-usage');
        Route::get('/{item}', [App\Http\Controllers\Branch\InventoryController::class, 'show'])->name('show');
    });

    // ========================================================================
    // BRANCH STOCK ADJUSTMENTS
    // ========================================================================
    Route::prefix('adjustments')->name('adjustments.')->group(function () {
        Route::get('/', [App\Http\Controllers\Branch\StockAdjustmentController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Branch\StockAdjustmentController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Branch\StockAdjustmentController::class, 'store'])->name('store');
        Route::get('/{adjustment}', [App\Http\Controllers\Branch\StockAdjustmentController::class, 'show'])->name('show');
    });

    // ========================================================================
    // BRANCH FINANCIAL TRACKING
    // ========================================================================
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [App\Http\Controllers\Branch\FinanceController::class, 'index'])->name('index');
        Route::post('/record-expense', [App\Http\Controllers\Branch\FinanceController::class, 'recordExpense'])->name('record-expense');
        Route::get('/expenses', [App\Http\Controllers\Branch\FinanceController::class, 'expenses'])->name('expenses');
        Route::get('/daily-cash-report', [App\Http\Controllers\Branch\FinanceController::class, 'dailyCashReport'])->name('daily-cash-report');
        Route::get('/weekly-summary', [App\Http\Controllers\Branch\FinanceController::class, 'weeklySummary'])->name('weekly-summary');
        Route::get('/sales-report', [App\Http\Controllers\Branch\FinanceController::class, 'salesReport'])->name('sales-report');
    });

    // ========================================================================
    // BRANCH RETAIL SALES
    // ========================================================================
    Route::prefix('retail')->name('retail.')->group(function () {
        Route::get('/', [App\Http\Controllers\Branch\RetailController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Branch\RetailController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Branch\RetailController::class, 'store'])->name('store');
        Route::post('/quick-sale', [App\Http\Controllers\Branch\RetailController::class, 'quickSale'])->name('quick-sale');
        Route::get('/{retail}', [App\Http\Controllers\Branch\RetailController::class, 'show'])->name('show');
    });

    // ========================================================================
    // BRANCH PICKUP & DELIVERY MANAGEMENT
    // ========================================================================
    Route::prefix('pickups')->name('pickups.')->group(function () {
        // Static routes
        Route::get('/', [BranchPickupController::class, 'index'])->name('index');
        Route::get('/create', [BranchPickupController::class, 'create'])->name('create');
        Route::get('/customers/list', [BranchPickupController::class, 'customers'])->name('customers');
        Route::get('/confirm/pending', [BranchPickupController::class, 'confirm'])->name('confirm');
        Route::get('/stats/data', [BranchPickupController::class, 'stats'])->name('stats');

        // Store new pickup
        Route::post('/', [BranchPickupController::class, 'store'])->name('store');

        // Dynamic routes
        Route::get('/{pickup}', [BranchPickupController::class, 'show'])->name('show');

        // Status updates
        Route::post('/{pickup}/status', [BranchPickupController::class, 'updateStatus'])->name('update-status');
        Route::post('/{pickup}/accept', [BranchPickupController::class, 'accept'])->name('accept');
        Route::post('/{pickup}/en-route', [BranchPickupController::class, 'markEnRoute'])->name('en-route');
        Route::post('/{pickup}/upload-proof', [BranchPickupController::class, 'uploadProof'])->name('upload-proof');
        Route::post('/{pickup}/picked-up', [BranchPickupController::class, 'markPickedUp'])->name('picked-up');
        Route::post('/{pickup}/cancel', [BranchPickupController::class, 'cancel'])->name('cancel');

        // GPS Location tracking
        Route::post('/{pickup}/update-location', [BranchPickupController::class, 'updateLocation'])->name('update-location');

        // Multi-pickup navigation
        Route::post('/start-multi-navigation', [BranchPickupController::class, 'startMultiNavigation'])->name('start-multi-navigation');
        Route::post('/{pickup}/start-navigation', [BranchPickupController::class, 'startNavigation'])->name('start-navigation');

        // Routing & Navigation for Staff (similar to admin logistics)
        Route::get('/{pickup}/route', [BranchPickupController::class, 'getRoute'])->name('route');
        Route::post('/{pickup}/start-navigation', [BranchPickupController::class, 'startNavigation'])->name('start-navigation');
    });

    // ========================================================================
    // PAYMENT VERIFICATION
    // ========================================================================
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/verification', [BranchPaymentVerificationController::class, 'index'])->name('verification.index');
        Route::get('/verification/{paymentProof}', [BranchPaymentVerificationController::class, 'show'])->name('verification.show');
        Route::post('/verification/{paymentProof}/approve', [BranchPaymentVerificationController::class, 'approve'])->name('verification.approve');
        Route::post('/verification/{paymentProof}/reject', [BranchPaymentVerificationController::class, 'reject'])->name('verification.reject');
        Route::post('/verification/bulk-approve', [BranchPaymentVerificationController::class, 'bulkApprove'])->name('verification.bulk-approve');
    });

    // ========================================================================
    // UNCLAIMED LAUNDRY MANAGEMENT
    // ========================================================================
    Route::prefix('unclaimed')->name('unclaimed.')->group(function () {
        Route::get('/', [BranchUnclaimedController::class, 'index'])->name('index');
        Route::get('/history', [BranchUnclaimedController::class, 'history'])->name('history');
        Route::get('/stats', [BranchUnclaimedController::class, 'stats'])->name('stats');
        Route::get('/export', [BranchUnclaimedController::class, 'export'])->name('export');
        Route::get('/{laundry}', [BranchUnclaimedController::class, 'show'])->name('show');
        Route::post('/{laundry}/send-reminder', [BranchUnclaimedController::class, 'sendReminder'])->name('send-reminder');
        Route::post('/{laundry}/mark-claimed', [BranchUnclaimedController::class, 'markClaimed'])->name('mark-claimed');
        Route::post('/{laundry}/mark-disposed', [BranchUnclaimedController::class, 'markDisposed'])->name('mark-disposed');
        Route::post('/{laundry}/log-call', [BranchUnclaimedController::class, 'logCallAttempt'])->name('log-call');
        Route::post('/bulk-reminders', [BranchUnclaimedController::class, 'sendBulkReminders'])->name('bulk-reminders');
    });

    // ========================================================================
    // SERVICES (View Only)
    // ========================================================================
    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [BranchServiceController::class, 'index'])->name('index');
        Route::get('/{service}', [BranchServiceController::class, 'show'])->name('show');
    });

    // ========================================================================
    // ADDONS (View Only)
    // ========================================================================
    Route::prefix('addons')->name('addons.')->group(function () {
        Route::get('/', [BranchServiceController::class, 'index'])->name('index');
        Route::get('/{service}', [BranchServiceController::class, 'show'])->name('show');
    });



    // ========================================================================
    // PROMOTIONS (View Only)
    // ========================================================================
    Route::prefix('promotions')->name('promotions.')->group(function () {
        Route::get('/', [BranchPromotionController::class, 'index'])->name('index');
        Route::get('/{promotion}', [BranchPromotionController::class, 'show'])->name('show');
    });

    // ========================================================================
    // BRANCHES (View Only)
    // ========================================================================
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\BranchController::class, 'index'])->name('index');
        Route::get('/{branch}', [App\Http\Controllers\Admin\BranchController::class, 'show'])->name('show');
    });

    // ========================================================================
    // RATINGS (Staff View - Branch Specific)
    // ========================================================================
    Route::prefix('ratings')->name('ratings.')->group(function () {
        Route::get('/', [BranchStaffRatingController::class, 'index'])->name('index');
        Route::get('/{rating}', [BranchStaffRatingController::class, 'show'])->name('show');
    });

    // ========================================================================
    // ANALYTICS (Staff View)
    // ========================================================================
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [BranchAnalyticsController::class, 'index'])->name('index');
        Route::get('/export', [BranchAnalyticsController::class, 'export'])->name('export');
    });

    // ========================================================================
    // REPORTS (Staff View)
    // ========================================================================
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [BranchReportController::class, 'index'])->name('index');
        Route::get('/daily', [BranchReportController::class, 'daily'])->name('daily');
        Route::get('/weekly', [BranchReportController::class, 'weekly'])->name('weekly');
        Route::get('/monthly', [BranchReportController::class, 'monthly'])->name('monthly');
        Route::post('/export', [BranchReportController::class, 'export'])->name('export');
    });

    // ========================================================================
    // STAFF MANAGEMENT (View Only)
    // ========================================================================
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [App\Http\Controllers\Branch\StaffController::class, 'index'])->name('index');
        Route::get('/salary-information', [App\Http\Controllers\Branch\StaffController::class, 'salaryInformation'])->name('salary-information');
        Route::get('/{user}', [App\Http\Controllers\Branch\StaffController::class, 'show'])->name('show');
    });

    // ========================================================================
    // MY PAYROLL (Staff View Their Own Payroll)
    // ========================================================================
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/', [App\Http\Controllers\Branch\PayrollViewController::class, 'index'])->name('index');
        Route::get('/{payrollItem}', [App\Http\Controllers\Branch\PayrollViewController::class, 'show'])->name('show');
    });

    // ========================================================================
    // SETTINGS
    // ========================================================================
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [BranchSettingsController::class, 'index'])->name('index');
        Route::post('/', [BranchSettingsController::class, 'update'])->name('update');
        Route::get('/account', [BranchSettingsController::class, 'account'])->name('account');
        Route::post('/account', [BranchSettingsController::class, 'updateAccount'])->name('account.update');
        
        // Extra Services Settings
        Route::get('/extra-services', [BranchSettingsController::class, 'extraServices'])->name('extra-services');
        Route::put('/extra-services', [BranchSettingsController::class, 'updateExtraServices'])->name('extra-services.update');
    });

    // ========================================================================
    // PROFILE MANAGEMENT
    // ========================================================================

    Route::get('/profile', [BranchSettingsController::class, 'profile'])->name('profile');
Route::post('/profile', [BranchSettingsController::class, 'updateProfile'])->name('profile.update');
Route::post('/profile/password', [BranchSettingsController::class, 'updatePassword'])->name('profile.password');

    // ========================================================================
    // NOTIFICATIONS (Branch)
    // ========================================================================
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [BranchNotificationController::class, 'index'])->name('index');
        Route::get('/recent', [BranchNotificationController::class, 'getRecent'])->name('recent');
        Route::get('/unread-count', [BranchNotificationController::class, 'getUnreadCount'])->name('unread-count');
        Route::post('/{id}/read', [BranchNotificationController::class, 'markAsRead'])->name('mark-read');
        Route::post('/mark-all-read', [BranchNotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{id}', [BranchNotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/clear/read', [BranchNotificationController::class, 'deleteAllRead'])->name('delete-read');
    });


    // ========================================================================
    // SEARCH (Global)
    // ========================================================================
    Route::get('/search', function () {
        $query = request('q');
        return view('branch.search.results', compact('query'));
    })->name('search');
});

// Test route for chart debugging
Route::get('/admin/test-chart', function() {
    $controller = new \App\Http\Controllers\Admin\DashboardController();
    $reflection = new ReflectionClass($controller);
    $method = $reflection->getMethod('getLaundriesStatusTrends');
    $method->setAccessible(true);
    $data = $method->invoke($controller, null);
    
    return view('admin.test_chart', ['data' => $data]);
})->name('admin.test-chart');
