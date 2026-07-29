<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashAccountController;
use App\Http\Controllers\Api\CashTransactionController;
use App\Http\Controllers\Api\ClosingSummaryController;
use App\Http\Controllers\Api\CustomFieldDefinitionController;
use App\Http\Controllers\Api\DailyStockController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OutletController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\ShiftScheduleController;
use App\Http\Controllers\Api\ShiftSwapRequestController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\StockOutflowController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:owner,admin')->group(function () {
        Route::get('/test-owner-admin-only', function () {
            return response()->json(['message' => 'You are owner/admin, access granted.']);
        });

        Route::apiResource('outlets', OutletController::class);

        Route::get('/outlets/{outletId}/sections', [SectionController::class, 'index']);
        Route::post('/outlets/{outletId}/sections', [SectionController::class, 'store']);
        Route::put('/outlets/{outletId}/sections/{sectionId}', [SectionController::class, 'update']);
        Route::delete('/outlets/{outletId}/sections/{sectionId}', [SectionController::class, 'destroy']);

        Route::get('/outlets/{outletId}/menus', [MenuController::class, 'index']);
        Route::post('/outlets/{outletId}/menus', [MenuController::class, 'store']);

        Route::get('/ingredients/low-stock', [IngredientController::class, 'lowStock']);

        Route::get('/sections/{sectionId}/ingredients', [IngredientController::class, 'index']);
        Route::post('/sections/{sectionId}/ingredients', [IngredientController::class, 'store']);
        Route::put('/sections/{sectionId}/ingredients/{ingredientId}', [IngredientController::class, 'update']);
        Route::delete('/sections/{sectionId}/ingredients/{ingredientId}', [IngredientController::class, 'destroy']);

        Route::get('/ingredients/{ingredientId}/daily-stocks', [DailyStockController::class, 'index']);
        Route::post('/ingredients/{ingredientId}/daily-stocks', [DailyStockController::class, 'store']);

        Route::get('/daily-stocks/{dailyStockId}/outflows', [StockOutflowController::class, 'index']);
        Route::post('/daily-stocks/{dailyStockId}/outflows', [StockOutflowController::class, 'store']);
        Route::put('/daily-stocks/{dailyStockId}/close', [StockOutflowController::class, 'closeDailyStock']);

        Route::get('/ingredients/{ingredientId}/stock-adjustments', [StockAdjustmentController::class, 'index']);
        Route::post('/ingredients/{ingredientId}/stock-adjustments', [StockAdjustmentController::class, 'store']);

        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::post('/suppliers', [SupplierController::class, 'store']);
        Route::put('/suppliers/{supplierId}', [SupplierController::class, 'update']);
        Route::delete('/suppliers/{supplierId}', [SupplierController::class, 'destroy']);

        Route::get('/outlets/{outletId}/purchase-orders', [PurchaseOrderController::class, 'index']);
        Route::post('/outlets/{outletId}/purchase-orders', [PurchaseOrderController::class, 'store']);
        Route::get('/outlets/{outletId}/purchase-orders/{purchaseOrderId}', [PurchaseOrderController::class, 'show']);
        Route::put('/outlets/{outletId}/purchase-orders/{purchaseOrderId}/status', [PurchaseOrderController::class, 'updateStatus']);

        Route::get('/outlets/{outletId}/cash-accounts', [CashAccountController::class, 'index']);
        Route::post('/outlets/{outletId}/cash-accounts', [CashAccountController::class, 'store']);
        Route::get('/cash-accounts/{cashAccountId}/transactions', [CashTransactionController::class, 'index']);
        Route::post('/cash-accounts/{cashAccountId}/transactions', [CashTransactionController::class, 'store']);

        Route::get('/sections/{sectionId}/closing-summary', [ClosingSummaryController::class, 'section']);

        Route::get('/sections/{sectionId}/employees', [EmployeeController::class, 'index']);
        Route::post('/sections/{sectionId}/employees', [EmployeeController::class, 'store']);
        Route::put('/sections/{sectionId}/employees/{employeeId}', [EmployeeController::class, 'update']);
        Route::delete('/sections/{sectionId}/employees/{employeeId}', [EmployeeController::class, 'destroy']);

        Route::get('/sections/{sectionId}/shifts', [ShiftController::class, 'index']);
        Route::post('/sections/{sectionId}/shifts', [ShiftController::class, 'store']);
        Route::put('/sections/{sectionId}/shifts/{shiftId}', [ShiftController::class, 'update']);
        Route::delete('/sections/{sectionId}/shifts/{shiftId}', [ShiftController::class, 'destroy']);

        Route::get('/employees/{employeeId}/shift-schedules', [ShiftScheduleController::class, 'index']);
        Route::post('/employees/{employeeId}/shift-schedules', [ShiftScheduleController::class, 'store']);
        Route::delete('/employees/{employeeId}/shift-schedules/{scheduleId}', [ShiftScheduleController::class, 'destroy']);

        Route::get('/shift-swap-requests', [ShiftSwapRequestController::class, 'index']);
        Route::post('/shift-swap-requests', [ShiftSwapRequestController::class, 'store']);
        Route::put('/shift-swap-requests/{swapRequestId}/approve', [ShiftSwapRequestController::class, 'approve']);
        Route::put('/shift-swap-requests/{swapRequestId}/reject', [ShiftSwapRequestController::class, 'reject']);

        Route::get('/employees/{employeeId}/attendance', [AttendanceController::class, 'index']);
        Route::post('/employees/{employeeId}/attendance/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/employees/{employeeId}/attendance/check-out', [AttendanceController::class, 'checkOut']);
        Route::post('/employees/{employeeId}/attendance/mark-status', [AttendanceController::class, 'markStatus']);

        Route::get('/tenant', [TenantController::class, 'show']);

        Route::get('/custom-field-definitions', [CustomFieldDefinitionController::class, 'index']);
        Route::post('/custom-field-definitions', [CustomFieldDefinitionController::class, 'store']);
        Route::delete('/custom-field-definitions/{id}', [CustomFieldDefinitionController::class, 'destroy']);
    });

    Route::middleware('role:owner')->group(function () {
        Route::put('/tenant/settings', [TenantController::class, 'updateSettings']);
    });
});
