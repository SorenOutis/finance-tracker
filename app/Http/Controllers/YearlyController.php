<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use Inertia\Inertia;
use Carbon\Carbon;

class YearlyController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $currentYear = Carbon::now();
        $yearStart = $currentYear->clone()->startOfYear();
        $yearEnd = $currentYear->clone()->endOfYear();

        $incomes = Income::where('user_id', $user->id)
            ->whereBetween('date', [$yearStart, $yearEnd])
            ->get();

        $expenses = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$yearStart, $yearEnd])
            ->get();

        $yearlyIncome = $incomes->sum('amount');
        $yearlyExpenses = $expenses->sum('amount');
        $yearlyNet = $yearlyIncome - $yearlyExpenses;

        // Monthly breakdown
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthStart = $currentYear->clone()->month($month)->startOfMonth();
            $monthEnd = $currentYear->clone()->month($month)->endOfMonth();

            $monthIncome = $incomes->filter(function ($income) use ($monthStart, $monthEnd) {
                $date = $income->date instanceof \Carbon\Carbon 
                    ? $income->date 
                    : \Carbon\Carbon::parse($income->date);
                return $date >= $monthStart && $date <= $monthEnd;
            })->sum('amount');

            $monthExpenses = $expenses->filter(function ($expense) use ($monthStart, $monthEnd) {
                $date = $expense->date instanceof \Carbon\Carbon 
                    ? $expense->date 
                    : \Carbon\Carbon::parse($expense->date);
                return $date >= $monthStart && $date <= $monthEnd;
            })->sum('amount');

            $monthlyData[] = [
                'month' => $monthStart->format('F'),
                'income' => $monthIncome,
                'expenses' => $monthExpenses,
                'net' => $monthIncome - $monthExpenses,
            ];
        }

        return Inertia::render('Yearly', [
            'yearlyStats' => [
                ['label' => 'Year Income', 'value' => $yearlyIncome],
                ['label' => 'Year Expenses', 'value' => $yearlyExpenses],
                ['label' => 'Year Net', 'value' => $yearlyNet],
            ],
            'monthlyData' => $monthlyData,
            'currentYear' => $currentYear->year,
        ]);
    }
}
