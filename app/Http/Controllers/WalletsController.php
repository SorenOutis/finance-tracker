<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Income;
use App\Models\Expense;
use Inertia\Inertia;
use Carbon\Carbon;

class WalletsController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $accounts = Account::where('user_id', $user->id)
            ->where('is_archived', false)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($account) use ($startOfMonth, $endOfMonth) {
                $totalIn = $account->incomes()->sum('amount');
                $totalOut = $account->expenses()->sum('amount');
                $balance = (float) $account->initial_balance + $totalIn - $totalOut;

                $monthIn = $account->incomes()
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->sum('amount');
                $monthOut = $account->expenses()
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->sum('amount');

                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'color' => $account->color,
                    'initial_balance' => (float) $account->initial_balance,
                    'credit_limit' => $account->credit_limit ? (float) $account->credit_limit : null,
                    'currency' => $account->currency,
                    'last4' => $account->last4,
                    'holder_name' => $account->holder_name,
                    'balance' => $balance,
                    'month_income' => (float) $monthIn,
                    'month_expense' => (float) $monthOut,
                ];
            });

        $totalBalance = $accounts->sum('balance');

        return Inertia::render('Wallets', [
            'accounts' => $accounts->values(),
            'totalBalance' => $totalBalance,
        ]);
    }
}
