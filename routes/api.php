<?php

use App\Http\Controllers\API\V1\AuthController;
use App\Http\Controllers\API\V1\AssetController;
use App\Http\Controllers\API\V1\ChartOfAccountController;
use App\Http\Controllers\API\V1\DocumentController;
use App\Http\Controllers\API\V1\DocumentCategoryController;
use App\Http\Controllers\API\V1\DocumentTemplateController;
use App\Http\Controllers\API\V1\EmployeeController;
use App\Http\Controllers\API\V1\EventController;
use App\Http\Controllers\API\V1\EventRegistrationConfigController;
use App\Http\Controllers\API\V1\EventRegistrationController;
use App\Http\Controllers\API\V1\EventSubResourceController;
use App\Http\Controllers\API\V1\InvoiceController;
use App\Http\Controllers\API\V1\JournalController;
use App\Http\Controllers\API\V1\XenditInvoiceController;
use App\Http\Controllers\API\V1\XenditWebhookController;
use App\Http\Controllers\API\V1\PaymentController;
use App\Http\Controllers\API\V1\PublicEventRegistrationController;
use App\Http\Controllers\API\V1\PurchaseOrderController;
use App\Http\Controllers\API\V1\ReportController;
use App\Http\Controllers\API\V1\RoleController;
use App\Http\Controllers\API\V1\SponsorController;
use App\Http\Controllers\API\V1\TeamController;
use App\Http\Controllers\API\V1\VenueController;
use App\Http\Controllers\API\V1\EventSeriesController;
use App\Http\Controllers\API\V1\VendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'requestPasswordReset']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

    // Public registration routes (no auth) — by public_id (UUID)
    Route::get('public/register/{publicId}', [PublicEventRegistrationController::class, 'show']);
    Route::post('public/register/{publicId}', [PublicEventRegistrationController::class, 'register']);
    Route::get('public/register/{publicId}/ticket/{registrationNumber}', [PublicEventRegistrationController::class, 'ticket']);
    Route::get('public/register/{publicId}/status/{idempotencyKey}', [PublicEventRegistrationController::class, 'status']);
    Route::get('public/invoices/{invoiceId}', [XenditInvoiceController::class, 'showPublic']);
    Route::post('public/check-in/{publicId}/auth', [PublicEventRegistrationController::class, 'checkInAuth']);
    Route::post('public/check-in/{publicId}/lookup', [PublicEventRegistrationController::class, 'checkInLookup']);
    Route::post('public/check-in/{publicId}/confirm', [PublicEventRegistrationController::class, 'checkInConfirm']);
    Route::post('public/check-in/{publicId}/employee-present', [PublicEventRegistrationController::class, 'employeePresent']);
    Route::post('public/check-in/{publicId}/employee-list', [PublicEventRegistrationController::class, 'employeeList']);
    Route::post('webhooks/xendit', [XenditWebhookController::class, 'handle']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('auth/profile', [AuthController::class, 'profile']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:rbac.manage');
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:rbac.manage');
        Route::put('roles/{id}', [RoleController::class, 'update'])->middleware('permission:rbac.manage');
        Route::delete('roles/{id}', [RoleController::class, 'destroy'])->middleware('permission:rbac.manage');

        Route::apiResource('venues', VenueController::class);
        Route::get('venues-all', [VenueController::class, 'all']);

        Route::apiResource('events', EventController::class);
        Route::get('events/{event}/series', [EventSeriesController::class, 'index']);
        Route::post('events/{event}/series', [EventSeriesController::class, 'store']);
        Route::get('events/{event}/series/{series}', [EventSeriesController::class, 'show']);
        Route::put('events/{event}/series/{series}', [EventSeriesController::class, 'update']);
        Route::patch('events/{event}/series/{series}/status', [EventSeriesController::class, 'updateStatus']);
        Route::post('events/{event}/series/{series}/regenerate-passcode', [EventSeriesController::class, 'regeneratePasscode']);
        Route::delete('events/{event}/series/{series}', [EventSeriesController::class, 'destroy']);

        // Series sub-resources (staff, vendors, budgets, expenses)
        Route::get('series/{series}/staff', [EventSubResourceController::class, 'listStaff']);
        Route::post('series/{series}/staff', [EventSubResourceController::class, 'addStaff']);
        Route::delete('series/{series}/staff/{staff}', [EventSubResourceController::class, 'removeStaff']);
        Route::patch('series/{series}/staff/{staff}/attendance', [EventSubResourceController::class, 'updateStaffAttendance']);
        Route::get('series/{series}/teams', [EventSubResourceController::class, 'listTeams']);
        Route::post('series/{series}/teams', [EventSubResourceController::class, 'addTeam']);
        Route::delete('series/{series}/teams/{assignment}', [EventSubResourceController::class, 'removeTeam']);
        Route::patch('series/{series}/teams/{assignment}/attendance', [EventSubResourceController::class, 'updateTeamAttendance']);
        Route::get('series/{series}/sponsors', [EventSubResourceController::class, 'listSponsors']);
        Route::post('series/{series}/sponsors', [EventSubResourceController::class, 'addSponsor']);
        Route::delete('series/{series}/sponsors/{assignment}', [EventSubResourceController::class, 'removeSponsor']);
        Route::get('series/{series}/vendors', [EventSubResourceController::class, 'listVendors']);
        Route::post('series/{series}/vendors', [EventSubResourceController::class, 'addVendor']);
        Route::delete('series/{series}/vendors/{eventVendor}', [EventSubResourceController::class, 'removeVendor']);
        Route::get('series/{series}/budgets', [EventSubResourceController::class, 'listBudgets']);
        Route::post('series/{series}/budgets', [EventSubResourceController::class, 'addBudget']);
        Route::delete('series/{series}/budgets/{budget}', [EventSubResourceController::class, 'removeBudget']);
        Route::get('series/{series}/expenses', [EventSubResourceController::class, 'listExpenses']);
        Route::post('series/{series}/expenses', [EventSubResourceController::class, 'addExpense']);
        Route::delete('series/{series}/expenses/{expense}', [EventSubResourceController::class, 'removeExpense']);
        Route::get('series/{series}/analytics', [EventSubResourceController::class, 'analytics']);

        // Series registration config (admin)
        Route::get('series/{series}/price-series', [EventRegistrationConfigController::class, 'indexPriceSeries']);
        Route::post('series/{series}/price-series', [EventRegistrationConfigController::class, 'storePriceSeries']);
        Route::put('series/{series}/price-series/{priceSeries}', [EventRegistrationConfigController::class, 'updatePriceSeries']);
        Route::delete('series/{series}/price-series/{priceSeries}', [EventRegistrationConfigController::class, 'destroyPriceSeries']);
        Route::get('series/{series}/categories', [EventRegistrationConfigController::class, 'indexCategories']);
        Route::post('series/{series}/categories', [EventRegistrationConfigController::class, 'storeCategory']);
        Route::put('series/{series}/categories/{category}', [EventRegistrationConfigController::class, 'updateCategory']);
        Route::delete('series/{series}/categories/{category}', [EventRegistrationConfigController::class, 'destroyCategory']);
        Route::get('series/{series}/price-matrix', [EventRegistrationConfigController::class, 'getPriceMatrix']);
        Route::post('series/{series}/price-matrix', [EventRegistrationConfigController::class, 'savePriceMatrix']);
        Route::get('series/{series}/category-prizes', [EventRegistrationConfigController::class, 'indexCategoryPrizes']);
        Route::post('series/{series}/category-prizes', [EventRegistrationConfigController::class, 'saveCategoryPrizes']);
        Route::get('series/{series}/registration-fields', [EventRegistrationConfigController::class, 'indexRegistrationFields']);
        Route::post('series/{series}/registration-fields', [EventRegistrationConfigController::class, 'syncRegistrationFields']);
        Route::get('series/{series}/registration-config', [EventRegistrationConfigController::class, 'getRegistrationConfig']);
        Route::patch('series/{series}/registration-config', [EventRegistrationConfigController::class, 'updateRegistrationConfig']);
        Route::get('series/{series}/registrations', [EventRegistrationController::class, 'index']);
        Route::get('series/{series}/registrations/lookup/{registrationNumber}', [EventRegistrationController::class, 'lookup']);
        Route::post('series/{series}/registrations/check-in', [EventRegistrationController::class, 'checkIn']);
        Route::get('series/{series}/registrations/guest-book-pdf', [EventRegistrationController::class, 'guestBookPdf']);
        Route::get('series/{series}/attendance/{type}/{id}', [EventRegistrationController::class, 'attendanceHistory']);
        Route::get('series/{series}/registrations/{registration}', [EventRegistrationController::class, 'show']);

        Route::apiResource('vendors', VendorController::class);
        Route::get('vendors/{vendor}/items', [VendorController::class, 'itemsIndex']);
        Route::post('vendors/{vendor}/items', [VendorController::class, 'itemsStore']);
        Route::put('vendors/{vendor}/items/{item}', [VendorController::class, 'itemsUpdate']);
        Route::delete('vendors/{vendor}/items/{item}', [VendorController::class, 'itemsDestroy']);
        Route::apiResource('employees', EmployeeController::class);
        Route::apiResource('sponsors', SponsorController::class);
        Route::apiResource('teams', TeamController::class);
        Route::apiResource('assets', AssetController::class)->only(['index', 'show', 'update']);
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
        Route::patch('purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus']);
        Route::apiResource('invoices', InvoiceController::class);
        Route::get('xendit/invoices', [XenditInvoiceController::class, 'index']);
        Route::get('xendit/invoices/{invoiceId}', [XenditInvoiceController::class, 'show']);
        Route::post('xendit/invoices/{invoiceId}/expire', [XenditInvoiceController::class, 'expire']);
        Route::apiResource('payments', PaymentController::class);
        Route::get('documents/templates/catalog', [DocumentController::class, 'templates']);
        Route::post('documents/batch-download', [DocumentController::class, 'batchDownload']);
        Route::apiResource('documents', DocumentController::class)->except(['update']);
        Route::apiResource('document-categories', DocumentCategoryController::class)->only(['index']);
        Route::apiResource('document-templates', DocumentTemplateController::class)->only(['index']);
        Route::apiResource('chart-of-accounts', ChartOfAccountController::class);
        Route::apiResource('journals', JournalController::class);

        Route::get('reports/kpis', [ReportController::class, 'kpis']);
        Route::get('reports/monthly-revenue', [ReportController::class, 'monthlyRevenue']);
        Route::get('reports/event-profitability', [ReportController::class, 'eventProfitability']);
        Route::post('reports/invoice/{invoice}/pdf', [ReportController::class, 'invoicePdf']);
        Route::post('reports/purchase-order/{purchaseOrder}/pdf', [ReportController::class, 'purchaseOrderPdf']);
    });
});
