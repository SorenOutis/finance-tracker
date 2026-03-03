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
    
    Route::resource('incomes', \App\Http\Controllers\IncomeController::class)->only(['store']);
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->only(['store']);
});

require __DIR__.'/settings.php';
