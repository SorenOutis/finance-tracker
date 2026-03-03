<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\LiabilityController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('incomes', IncomeController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('assets', AssetController::class);
    Route::apiResource('liabilities', LiabilityController::class);
});
