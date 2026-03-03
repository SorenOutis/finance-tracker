<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();
        $selectedDate = $request->query('date') ? Carbon::parse($request->query('date')) : Carbon::now();
        $startOfMonth = $selectedDate->clone()->startOfMonth();
        $endOfMonth = $selectedDate->clone()->endOfMonth();

        $incomes = Income::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'desc')
            ->get();

        $expenses = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'desc')
            ->get();

        $totalIncome = $incomes->sum('amount');
        $totalExpenses = $expenses->sum('amount');
        $netIncome = $totalIncome - $totalExpenses;

        // Get yearly data for stats
         $yearStart = $selectedDate->clone()->startOfYear();
         $yearEnd = $selectedDate->clone()->endOfYear();

        $yearlyIncomes = Income::where('user_id', $user->id)
            ->whereBetween('date', [$yearStart, $yearEnd])
            ->sum('amount');

        $yearlyExpenses = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$yearStart, $yearEnd])
            ->sum('amount');

        $netWorth = $yearlyIncomes - $yearlyExpenses;

        return Inertia::render('Dashboard', [
            'stats' => [
                ['label' => 'Total Income', 'value' => $totalIncome],
                ['label' => 'Total Expenses', 'value' => $totalExpenses],
                ['label' => 'Net Income', 'value' => $netIncome],
                ['label' => 'Net Worth', 'value' => $netWorth],
            ],
            'incomeEntries' => $incomes->map(fn($income) => [
                'id' => $income->id,
                'amount' => (float) $income->amount,
                'date' => $income->date instanceof \Carbon\Carbon 
                    ? $income->date->format('m/d/Y')
                    : \Carbon\Carbon::parse($income->date)->format('m/d/Y'),
                'category' => $income->category,
                'description' => $income->description,
            ]),
            'expenseEntries' => $expenses->map(fn($expense) => [
                'id' => $expense->id,
                'amount' => (float) $expense->amount,
                'date' => $expense->date instanceof \Carbon\Carbon 
                    ? $expense->date->format('m/d/Y')
                    : \Carbon\Carbon::parse($expense->date)->format('m/d/Y'),
                'category' => $expense->category,
                'description' => $expense->description,
            ]),
        ]);
    }
}
