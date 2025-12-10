<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncidentController;
use App\Http\Controllers\RiskRuleController;
use App\Http\Controllers\TradeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

/*
|--------------------------------------------------------------------------
| API Version 1
|--------------------------------------------------------------------------
|
| All v1 API endpoints are grouped under this prefix.
| Future API versions (v2, v3) can be added as separate groups.
|
*/
Route::prefix('v1')->group(function () {
    // Risk Rules Management
    Route::prefix('risk-rules')->group(function () {
        Route::get('/', [RiskRuleController::class, 'index']);
        Route::post('/', [RiskRuleController::class, 'store']);
        Route::get('/actions', [RiskRuleController::class, 'listActions']);
        Route::get('/{riskRule}', [RiskRuleController::class, 'show']);
        Route::put('/{riskRule}', [RiskRuleController::class, 'update']);
        Route::delete('/{riskRule}', [RiskRuleController::class, 'destroy']);
        Route::post('/{riskRule}/actions', [RiskRuleController::class, 'attachActions']);
    });

    // Incidents
    Route::prefix('incidents')->group(function () {
        Route::get('/', [IncidentController::class, 'index']);
        Route::get('/unread', [IncidentController::class, 'unread']);
        Route::post('/read-all', [IncidentController::class, 'markAllAsRead']);
        Route::get('/account/{accountId}/stats', [IncidentController::class, 'accountStats']);
        Route::get('/{incident}', [IncidentController::class, 'show']);
        Route::post('/{incident}/read', [IncidentController::class, 'markAsRead']);
    });

    // Trades
    Route::prefix('trades')->group(function () {
        Route::get('/', [TradeController::class, 'index']);
        Route::post('/', [TradeController::class, 'store']);
        Route::get('/{trade}', [TradeController::class, 'show']);
        Route::put('/{trade}', [TradeController::class, 'update']);
    });

    // Accounts
    Route::prefix('accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index']);
        Route::post('/', [AccountController::class, 'store']);
        Route::get('/{account}', [AccountController::class, 'show']);
        Route::post('/{account}/restore', [AccountController::class, 'restore']);
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
        Route::get('/incident-activity', [DashboardController::class, 'incidentActivity']);
        Route::get('/recent-incidents', [DashboardController::class, 'recentIncidents']);
        Route::get('/system-status', [DashboardController::class, 'systemStatus']);
    });
});

/*
|--------------------------------------------------------------------------
| Legacy Routes (Backward Compatibility)
|--------------------------------------------------------------------------
|
| These routes mirror v1 endpoints for backward compatibility.
| They will be deprecated in future releases.
|
*/
Route::prefix('risk-rules')->group(function () {
    Route::get('/', [RiskRuleController::class, 'index']);
    Route::post('/', [RiskRuleController::class, 'store']);
    Route::get('/actions', [RiskRuleController::class, 'listActions']);
    Route::get('/{riskRule}', [RiskRuleController::class, 'show']);
    Route::put('/{riskRule}', [RiskRuleController::class, 'update']);
    Route::delete('/{riskRule}', [RiskRuleController::class, 'destroy']);
    Route::post('/{riskRule}/actions', [RiskRuleController::class, 'attachActions']);
});

Route::prefix('incidents')->group(function () {
    Route::get('/', [IncidentController::class, 'index']);
    Route::get('/unread', [IncidentController::class, 'unread']);
    Route::post('/read-all', [IncidentController::class, 'markAllAsRead']);
    Route::get('/account/{accountId}/stats', [IncidentController::class, 'accountStats']);
    Route::get('/{incident}', [IncidentController::class, 'show']);
    Route::post('/{incident}/read', [IncidentController::class, 'markAsRead']);
});

Route::prefix('trades')->group(function () {
    Route::get('/', [TradeController::class, 'index']);
    Route::post('/', [TradeController::class, 'store']);
    Route::get('/{trade}', [TradeController::class, 'show']);
    Route::put('/{trade}', [TradeController::class, 'update']);
});

Route::prefix('accounts')->group(function () {
    Route::get('/', [AccountController::class, 'index']);
    Route::post('/', [AccountController::class, 'store']);
    Route::get('/{account}', [AccountController::class, 'show']);
    Route::post('/{account}/restore', [AccountController::class, 'restore']);
});

Route::prefix('dashboard')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/incident-activity', [DashboardController::class, 'incidentActivity']);
    Route::get('/recent-incidents', [DashboardController::class, 'recentIncidents']);
    Route::get('/system-status', [DashboardController::class, 'systemStatus']);
});

