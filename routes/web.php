<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');
    Route::get('yearly', \App\Http\Controllers\YearlyController::class)->name('yearly');
    Route::get('analytics', \App\Http\Controllers\AnalyticsController::class)->name('analytics');
    Route::get('assets', \App\Http\Controllers\AssetsController::class)->name('assets');
    Route::get('settings', \App\Http\Controllers\SettingsController::class)->name('settings');
    
    Route::resource('incomes', \App\Http\Controllers\IncomeController::class)->only(['store', 'destroy']);
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->only(['store', 'destroy']);
    Route::resource('assets', \App\Http\Controllers\AssetController::class)->only(['store', 'destroy', 'update']);
    Route::resource('liabilities', \App\Http\Controllers\LiabilityController::class)->only(['store', 'destroy', 'update']);
});

require __DIR__.'/settings.php';
