<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Expense;
use Inertia\Inertia;
use Carbon\Carbon;

class AnalyticsController extends Controller
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

        // Monthly trend data
        $trendData = [];
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

            $trendData[] = [
                'month' => $monthStart->format('M'),
                'income' => $monthIncome,
                'expenses' => $monthExpenses,
            ];
        }

        // Category breakdown
        $categoryData = $expenses->groupBy('category')->map(function ($group) {
            return [
                'name' => $group->first()->category ?? 'Other',
                'amount' => $group->sum('amount'),
            ];
        })->values();

        $totalExpenses = $categoryData->sum('amount') ?: 1;
        $categoryData = $categoryData->map(function ($item) use ($totalExpenses) {
            return [
                'name' => $item['name'],
                'amount' => $item['amount'],
                'percentage' => round(($item['amount'] / $totalExpenses) * 100, 2),
            ];
        })->sortByDesc('amount')->take(5); // Show top 5 categories

        return Inertia::render('Analytics', [
            'trendData' => $trendData,
            'categoryData' => $categoryData->isEmpty() ? [['name' => 'Other', 'amount' => 0, 'percentage' => 0]] : $categoryData->toArray(),
        ]);
    }
}
