<?php

declare(strict_types=1);

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CalculatorFormulaController;
use App\Http\Controllers\CalculatorInputFieldController;
use App\Http\Controllers\CalculatorOutputProductMappingController;
use App\Http\Controllers\CalculatorPreviewController;
use App\Http\Controllers\CalculatorTypeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentAttributeSchemaController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\LicenseSettingsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductBatchController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductLookupController;
use App\Http\Controllers\ProductVariantBarcodeController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware(['auth', 'license.check'])->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('account', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('account/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('account', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::middleware('permission:manage-departments')->group(function (): void {
        Route::resource('departments', DepartmentController::class);
        Route::post('departments/{department}/attribute-schemas', [DepartmentAttributeSchemaController::class, 'store'])
            ->name('department-attribute-schemas.store');
        Route::put('attribute-schemas/{attributeSchema}', [DepartmentAttributeSchemaController::class, 'update'])
            ->name('department-attribute-schemas.update');
        Route::delete('attribute-schemas/{attributeSchema}', [DepartmentAttributeSchemaController::class, 'destroy'])
            ->name('department-attribute-schemas.destroy');
    });

    Route::middleware('permission:manage-products')->group(function (): void {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('brands', BrandController::class)->except(['show']);
        Route::get('product-variants/{productVariant}/barcode.svg', [ProductVariantBarcodeController::class, 'svg'])
            ->name('product-variants.barcode');
        Route::get('product-variants/{productVariant}/label', [ProductVariantBarcodeController::class, 'label'])
            ->name('product-variants.label');
    });

    Route::middleware('permission:manage-warehouses')->group(function (): void {
        Route::resource('warehouses', WarehouseController::class)->except(['show']);
    });

    Route::middleware('permission:manage-suppliers')->group(function (): void {
        Route::resource('suppliers', SupplierController::class)->except(['show']);
    });

    Route::middleware('permission:manage-stock|view-reports')->group(function (): void {
        Route::get('stock-movements', [StockMovementController::class, 'index'])->name('stock-movements.index');
    });

    Route::middleware(['permission:view-reports', 'license.feature:analytics-dashboard'])->group(function (): void {
        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('analytics/export/{report}', [AnalyticsController::class, 'export'])->name('analytics.export');
    });

    Route::middleware('permission:manage-stock')->group(function (): void {
        Route::resource('purchase-orders', PurchaseOrderController::class)
            ->parameters(['purchase-orders' => 'purchaseOrder']);
        Route::post('purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])
            ->name('purchase-orders.send');
        Route::post('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
            ->name('purchase-orders.cancel');
        Route::get('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
            ->name('purchase-orders.receive');
        Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'processReceive'])
            ->name('purchase-orders.receive.store');

        Route::get('stock-adjustments', [StockAdjustmentController::class, 'index'])->name('stock-adjustments.index');
        Route::get('stock-adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
        Route::post('stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
        Route::get('stock-adjustments/{stockAdjustment}', [StockAdjustmentController::class, 'show'])->name('stock-adjustments.show');

        Route::resource('stock-transfers', StockTransferController::class)
            ->parameters(['stock-transfers' => 'stockTransfer'])
            ->only(['index', 'create', 'store', 'show']);
        Route::post('stock-transfers/{stockTransfer}/dispatch', [StockTransferController::class, 'dispatch'])
            ->name('stock-transfers.dispatch');
        Route::post('stock-transfers/{stockTransfer}/receive', [StockTransferController::class, 'receive'])
            ->name('stock-transfers.receive');
        Route::post('stock-transfers/{stockTransfer}/cancel', [StockTransferController::class, 'cancel'])
            ->name('stock-transfers.cancel');

        Route::get('product-batches', [ProductBatchController::class, 'index'])->name('product-batches.index');

        Route::get('product-lookup', [ProductLookupController::class, 'index'])->name('product-lookup.index');
        Route::get('product-lookup/search', [ProductLookupController::class, 'search'])->name('product-lookup.search');
    });

    Route::middleware('permission:manage-stock-adjustments')->group(function (): void {
        Route::post('stock-adjustments/{stockAdjustment}/approve', [StockAdjustmentController::class, 'approve'])
            ->name('stock-adjustments.approve');
        Route::post('stock-adjustments/{stockAdjustment}/reject', [StockAdjustmentController::class, 'reject'])
            ->name('stock-adjustments.reject');
    });

    Route::middleware('permission:manage-orders')->group(function (): void {
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
        Route::post('orders/{order}/partially-fulfill', [OrderController::class, 'partiallyFulfill'])->name('orders.partially-fulfill');
        Route::post('orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
        Route::post('orders/{order}/progress', [OrderController::class, 'progress'])->name('orders.progress');
        Route::get('orders/{order}/pdf', [OrderController::class, 'pdf'])->name('orders.pdf');
    });

    Route::middleware(['permission:manage-quotes', 'license.feature:quote-management'])->group(function (): void {
        Route::get('quotes', [QuoteController::class, 'index'])->name('quotes.index');
        Route::get('quotes/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
        Route::patch('quotes/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
        Route::get('quotes/{quote}/pdf', [QuoteController::class, 'pdf'])->name('quotes.pdf');
        Route::post('quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
        Route::post('quotes/{quote}/accept', [QuoteController::class, 'markAccepted'])->name('quotes.accept');
        Route::post('quotes/{quote}/reject', [QuoteController::class, 'markRejected'])->name('quotes.reject');
        Route::post('quotes/{quote}/convert', [QuoteController::class, 'convertToOrder'])->name('quotes.convert');
        Route::get('quote-product-search', [QuoteController::class, 'searchProducts'])->name('quotes.search-products');
    });

    Route::middleware(['permission:manage-pos', 'license.feature:pos-module'])->group(function (): void {
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::get('pos/lookup', [PosController::class, 'lookup'])->name('pos.lookup');
        Route::post('pos', [PosController::class, 'store'])->name('pos.store');
        Route::get('pos/sales', [PosController::class, 'salesIndex'])->name('pos.sales.index');
        Route::get('pos/sales/{posSale}', [PosController::class, 'salesShow'])->name('pos.sales.show');
        Route::get('pos/sales/{posSale}/pdf', [PosController::class, 'salesPdf'])->name('pos.sales.pdf');
    });

    Route::middleware('permission:manage-users')->group(function (): void {
        Route::resource('users', UserController::class);
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::middleware('permission:manage-roles')->group(function (): void {
        Route::resource('roles', RoleController::class)->except(['index', 'show']);
    });

    // GET is deliberately broader than the write actions below — it's the
    // single landing page for Departments/Users/License/Audit Log/Planning
    // Tools too now (per the sidebar consolidation), so anyone who could
    // reach those standalone pages before must still be able to open
    // Settings to find them. Each tab's content is gated inside the view
    // by its own original permission; only the actual settings.* writes
    // stay manage-settings-only.
    Route::middleware('permission:manage-settings|manage-departments|manage-users|manage-license|manage-calculators')->group(function (): void {
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    });

    Route::middleware('permission:manage-settings')->group(function (): void {
        Route::post('settings/general', [SettingsController::class, 'updateGeneral'])->name('settings.general.update');
        Route::post('settings/numbering', [SettingsController::class, 'updateNumbering'])->name('settings.numbering.update');
        Route::post('settings/tax', [SettingsController::class, 'updateTax'])->name('settings.tax.update');
        Route::post('settings/notifications', [SettingsController::class, 'updateNotifications'])->name('settings.notifications.update');
        Route::post('settings/whatsapp', [SettingsController::class, 'updateWhatsApp'])->name('settings.whatsapp.update');
        Route::post('settings/backup/run', [SettingsController::class, 'runBackup'])->name('settings.backup.run');
        Route::post('settings/backup/frequency', [SettingsController::class, 'updateBackupFrequency'])->name('settings.backup.frequency');
        Route::get('settings/backup/{filename}/download', [SettingsController::class, 'downloadBackup'])->name('settings.backup.download');
    });

    Route::middleware(['permission:manage-calculators', 'license.feature:smart-calculators'])->group(function (): void {
        Route::resource('calculator-types', CalculatorTypeController::class);

        Route::post('calculator-types/{calculatorType}/input-fields', [CalculatorInputFieldController::class, 'store'])
            ->name('calculator-input-fields.store');
        Route::put('calculator-input-fields/{calculatorInputField}', [CalculatorInputFieldController::class, 'update'])
            ->name('calculator-input-fields.update');
        Route::delete('calculator-input-fields/{calculatorInputField}', [CalculatorInputFieldController::class, 'destroy'])
            ->name('calculator-input-fields.destroy');

        Route::post('calculator-types/{calculatorType}/formulas', [CalculatorFormulaController::class, 'store'])
            ->name('calculator-formulas.store');
        Route::put('calculator-formulas/{calculatorFormula}', [CalculatorFormulaController::class, 'update'])
            ->name('calculator-formulas.update');
        Route::delete('calculator-formulas/{calculatorFormula}', [CalculatorFormulaController::class, 'destroy'])
            ->name('calculator-formulas.destroy');
        Route::post('calculator-types/{calculatorType}/test-formulas', [CalculatorFormulaController::class, 'test'])
            ->name('calculator-formulas.test');

        Route::post('calculator-types/{calculatorType}/mappings', [CalculatorOutputProductMappingController::class, 'store'])
            ->name('calculator-output-mappings.store');
        Route::put('calculator-output-mappings/{calculatorOutputProductMapping}', [CalculatorOutputProductMappingController::class, 'update'])
            ->name('calculator-output-mappings.update');
        Route::delete('calculator-output-mappings/{calculatorOutputProductMapping}', [CalculatorOutputProductMappingController::class, 'destroy'])
            ->name('calculator-output-mappings.destroy');

        Route::get('calculator-preview', [CalculatorPreviewController::class, 'index'])->name('calculator-preview.index');
        Route::get('calculator-preview/{calculatorType}', [CalculatorPreviewController::class, 'show'])->name('calculator-preview.show');
        Route::post('calculator-preview/{calculatorType}', [CalculatorPreviewController::class, 'run'])->name('calculator-preview.run');
    });
});

// Deliberately outside the license.check-guarded group above: the License
// settings screen must stay reachable even in the not_activated/invalid
// state (CheckLicense only exempts routes from the "expired" lockout, not
// "not_activated"/"invalid"), otherwise nobody could ever activate a fresh
// install through the UI and would be stuck using the console command.
Route::middleware(['auth', 'permission:manage-license'])->group(function (): void {
    Route::get('settings/license', [LicenseSettingsController::class, 'index'])->name('license.index');
    Route::post('settings/license/activate', [LicenseSettingsController::class, 'activate'])->name('license.activate');
});

// Overrides the license-client package's own "expired" page (see
// config/license-client.php's routes.expired) so it carries this app's
// branding and, for anyone who can manage-license, an inline renewal form —
// the package's default page is a dead-end "contact your vendor" message
// with no way back in, even though license.index/activate above are already
// reachable while expired.
Route::middleware('auth')->get('/license/expired', fn () => view('license.expired'))->name('license.expired');

// Same override, for the "not_activated"/"invalid" state (see
// config/license-client.php's routes.blocked) — a fresh install with no
// license yet hits this, not "expired".
Route::middleware('auth')->get('/license/blocked', fn () => view('license.blocked'))->name('license.blocked');
