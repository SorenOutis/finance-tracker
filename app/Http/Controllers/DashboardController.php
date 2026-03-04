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

        // Previous month data for month-over-month comparison
        $prevStart = $selectedDate->clone()->subMonth()->startOfMonth();
        $prevEnd = $selectedDate->clone()->subMonth()->endOfMonth();

        $prevMonthIncome = Income::where('user_id', $user->id)
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->sum('amount');

        $prevMonthExpense = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->sum('amount');

        // Daily spending for sparkline chart
        $daysInMonth = $startOfMonth->daysInMonth;
        $dailySpending = [];
        $expensesByDay = $expenses->groupBy(function ($e) {
            $date = $e->date instanceof Carbon ? $e->date : Carbon::parse($e->date);
            return (int) $date->format('d');
        });

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dailySpending[] = [
                'day' => $d,
                'amount' => $expensesByDay->has($d) ? $expensesByDay[$d]->sum('amount') : 0,
            ];
        }

        // Expenses by category
        $expensesByCategory = $expenses->groupBy(function ($e) {
            return $e->category ?: 'Uncategorized';
        })->map(function ($group, $category) use ($totalExpenses) {
            $amount = $group->sum('amount');
            return [
                'category' => $category,
                'amount' => (float) $amount,
                'percentage' => $totalExpenses > 0 ? round(($amount / $totalExpenses) * 100, 1) : 0,
            ];
        })->sortByDesc('amount')->values()->all();

        return Inertia::render('Dashboard', [
            'user' => $user,
            'dashboard' => [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpenses,
                'netIncome' => $netIncome,
                'netWorth' => $netWorth,
                'prevMonthIncome' => (float) $prevMonthIncome,
                'prevMonthExpense' => (float) $prevMonthExpense,
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
            'dailySpending' => $dailySpending,
            'expensesByCategory' => $expensesByCategory,
        ]);
    }
}
