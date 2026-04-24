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
    Route::get('wallets', \App\Http\Controllers\WalletsController::class)->name('wallets');
    Route::get('bills', [\App\Http\Controllers\RecurringTransactionController::class, 'index'])->name('bills');

    Route::resource('incomes', \App\Http\Controllers\IncomeController::class)->only(['store', 'destroy']);
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->only(['store', 'destroy']);
    Route::resource('assets', \App\Http\Controllers\AssetController::class)->only(['store', 'destroy', 'update']);
    Route::resource('liabilities', \App\Http\Controllers\LiabilityController::class)->only(['store', 'destroy', 'update']);

    // Accounts (Wallets)
    Route::post('accounts', [\App\Http\Controllers\AccountController::class, 'store'])->name('accounts.store');
    Route::patch('accounts/{account}', [\App\Http\Controllers\AccountController::class, 'update'])->name('accounts.update');
    Route::delete('accounts/{account}', [\App\Http\Controllers\AccountController::class, 'destroy'])->name('accounts.destroy');

    // Budget
    Route::post('budgets', [\App\Http\Controllers\BudgetController::class, 'store'])->name('budgets.store');

    // Financial Goals
    Route::post('financial-goals', [\App\Http\Controllers\FinancialGoalController::class, 'store'])->name('financial-goals.store');
    Route::patch('financial-goals/{financial_goal}', [\App\Http\Controllers\FinancialGoalController::class, 'update'])->name('financial-goals.update');
    Route::delete('financial-goals/{financial_goal}', [\App\Http\Controllers\FinancialGoalController::class, 'destroy'])->name('financial-goals.destroy');

    // Recurring Transactions
    Route::post('recurring-transactions', [\App\Http\Controllers\RecurringTransactionController::class, 'store'])->name('recurring-transactions.store');
    Route::patch('recurring-transactions/{recurring_transaction}', [\App\Http\Controllers\RecurringTransactionController::class, 'update'])->name('recurring-transactions.update');
    Route::delete('recurring-transactions/{recurring_transaction}', [\App\Http\Controllers\RecurringTransactionController::class, 'destroy'])->name('recurring-transactions.destroy');

    // Export
    Route::get('export/csv', [\App\Http\Controllers\ExportController::class, 'csv'])->name('export.csv');
});

require __DIR__.'/settings.php';
