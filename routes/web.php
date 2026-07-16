<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', \App\Http\Controllers\DashboardController::class)->name('dashboard');
    Route::post('onboarding/complete', function () {
        auth()->user()->update(['is_onboarded' => true]);

        return back();
    })->name('onboarding.complete');
    Route::get('yearly', \App\Http\Controllers\YearlyController::class)->name('yearly');
    Route::get('analytics', \App\Http\Controllers\AnalyticsController::class)->name('analytics');
    Route::get('history', \App\Http\Controllers\HistoryController::class)->name('history');
    Route::get('assets', \App\Http\Controllers\AssetsController::class)->name('assets');
    Route::get('settings', \App\Http\Controllers\SettingsController::class)->name('settings');
    Route::get('wallets', \App\Http\Controllers\WalletsController::class)->name('wallets');
    Route::get('bills', [\App\Http\Controllers\RecurringTransactionController::class, 'index'])->name('bills');
    Route::get('incomes', [\App\Http\Controllers\RecurringTransactionController::class, 'incomes'])->name('incomes');
    Route::get('expenses', [\App\Http\Controllers\ExpenseController::class, 'page'])->name('expenses');

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

    // ============================================================
    // Enhancement: Custom Categories
    // ============================================================
    Route::get('categories', [\App\Http\Controllers\CategoryController::class, 'index'])->name('categories');
    Route::post('categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::patch('categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('categories/seed-defaults', [\App\Http\Controllers\CategoryController::class, 'seedDefaults'])->name('categories.seed-defaults');

    // ============================================================
    // Enhancement: Notifications (JSON endpoint)
    // ============================================================
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications');

    // ============================================================
    // Enhancement: Financial Calendar
    // ============================================================
    Route::get('calendar', function () {
        return \Inertia\Inertia::render('Calendar');
    })->name('calendar');

    // ============================================================
    // Enhancement: Data Import
    // ============================================================
    Route::get('import', [\App\Http\Controllers\ImportController::class, 'index'])->name('import');
    Route::post('import/preview', [\App\Http\Controllers\ImportController::class, 'preview'])->name('import.preview');
    Route::post('import/process', [\App\Http\Controllers\ImportController::class, 'import'])->name('import.process');

    // ============================================================
    // Enhancement: Savings Envelopes
    // ============================================================
    Route::get('envelopes', [\App\Http\Controllers\EnvelopeController::class, 'index'])->name('envelopes');
    Route::post('envelopes', [\App\Http\Controllers\EnvelopeController::class, 'store'])->name('envelopes.store');
    Route::patch('envelopes/{envelope}', [\App\Http\Controllers\EnvelopeController::class, 'update'])->name('envelopes.update');
    Route::delete('envelopes/{envelope}', [\App\Http\Controllers\EnvelopeController::class, 'destroy'])->name('envelopes.destroy');
    Route::post('envelopes/{envelope}/allocate', [\App\Http\Controllers\EnvelopeController::class, 'allocate'])->name('envelopes.allocate');
    Route::post('envelopes/{envelope}/withdraw', [\App\Http\Controllers\EnvelopeController::class, 'withdraw'])->name('envelopes.withdraw');

    // ============================================================
    // Enhancement: AI Category Suggestion (JSON endpoint)
    // ============================================================
    Route::post('suggest-category', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'description' => 'required|string|max:500',
            'type' => 'nullable|in:income,expense',
        ]);

        $service = new \App\Services\CategorySuggestionService;
        $suggestion = $service->suggest(
            $request->input('description'),
            $request->input('type')
        );

        return response()->json(['suggestion' => $suggestion]);
    })->name('suggest-category');

    // ============================================================
    // Enhancement: Receipt Photo Upload
    // ============================================================
    Route::post('expenses/{expense}/receipt', [\App\Http\Controllers\ExpenseController::class, 'uploadReceipt'])->name('expenses.receipt');
    Route::delete('expenses/{expense}/receipt', [\App\Http\Controllers\ExpenseController::class, 'deleteReceipt'])->name('expenses.receipt-destroy');
});

require __DIR__.'/settings.php';
