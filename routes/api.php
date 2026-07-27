<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomFieldDefinitionController;
use App\Http\Controllers\Api\IngredientController;
use App\Http\Controllers\Api\OutletController;
use App\Http\Controllers\Api\SectionController;
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

        Route::get('/sections/{sectionId}/ingredients', [IngredientController::class, 'index']);
        Route::post('/sections/{sectionId}/ingredients', [IngredientController::class, 'store']);
        Route::put('/sections/{sectionId}/ingredients/{ingredientId}', [IngredientController::class, 'update']);
        Route::delete('/sections/{sectionId}/ingredients/{ingredientId}', [IngredientController::class, 'destroy']);

        Route::get('/tenant', [TenantController::class, 'show']);

        Route::get('/custom-field-definitions', [CustomFieldDefinitionController::class, 'index']);
        Route::post('/custom-field-definitions', [CustomFieldDefinitionController::class, 'store']);
        Route::delete('/custom-field-definitions/{id}', [CustomFieldDefinitionController::class, 'destroy']);
    });

    Route::middleware('role:owner')->group(function () {
        Route::put('/tenant/settings', [TenantController::class, 'updateSettings']);
    });
});
