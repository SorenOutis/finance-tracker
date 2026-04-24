<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Asset;
use App\Models\Budget;
use App\Models\Expense;
use App\Models\FinancialGoal;
use App\Models\Income;
use App\Models\Liability;
use App\Models\RecurringTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        // Recurring monthly totals
        $recurringIncomesTotal = RecurringTransaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->where('is_active', true)
            ->get()
            ->sum(function ($rt) {
                if ($rt->frequency === 'weekly') return $rt->amount * 4;
                if ($rt->frequency === 'yearly') return $rt->amount / 12;
                return $rt->amount;
            });

        $recurringExpensesTotal = RecurringTransaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('is_active', true)
            ->get()
            ->sum(function ($rt) {
                if ($rt->frequency === 'weekly') return $rt->amount * 4;
                if ($rt->frequency === 'yearly') return $rt->amount / 12;
                return $rt->amount;
            });

        $totalIncome = (float) $incomes->sum('amount') + (float) $recurringIncomesTotal;
        $totalExpenses = (float) $expenses->sum('amount') + (float) $recurringExpensesTotal;
        $netIncome = $totalIncome - $totalExpenses;

        // Yearly data
        $yearStart = $selectedDate->clone()->startOfYear();
        $yearEnd = $selectedDate->clone()->endOfYear();

        $yearlyIncomes = Income::where('user_id', $user->id)
            ->whereBetween('date', [$yearStart, $yearEnd])
            ->sum('amount');
        $yearlyExpenses = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$yearStart, $yearEnd])
            ->sum('amount');

        $totalAssets = Asset::where('user_id', $user->id)->sum('amount');
        $totalLiabilities = Liability::where('user_id', $user->id)->sum('amount');
        $netWorth = $totalAssets - $totalLiabilities;

        // Previous month data for MoM comparison
        $prevStart = $selectedDate->clone()->subMonth()->startOfMonth();
        $prevEnd = $selectedDate->clone()->subMonth()->endOfMonth();
        $prevMonthIncome = (float) Income::where('user_id', $user->id)->whereBetween('date', [$prevStart, $prevEnd])->sum('amount') + (float) $recurringIncomesTotal;
        $prevMonthExpense = (float) Expense::where('user_id', $user->id)->whereBetween('date', [$prevStart, $prevEnd])->sum('amount') + (float) $recurringExpensesTotal;

        // Daily spending for sparkline
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

        // Top expense spotlight
        $topExpense = $expenses->sortByDesc('amount')->first();
        $topExpenseData = $topExpense ? [
            'amount' => (float) $topExpense->amount,
            'category' => $topExpense->category ?? 'Uncategorized',
            'description' => $topExpense->description,
            'date' => $topExpense->date instanceof Carbon
                ? $topExpense->date->format('m/d/Y')
                : Carbon::parse($topExpense->date)->format('m/d/Y'),
        ] : null;

        // Transaction stats
        $incomeCount = $incomes->count();
        $expenseCount = $expenses->count();
        $totalTransactions = $incomeCount + $expenseCount;
        $avgExpense = $expenseCount > 0 ? $totalExpenses / $expenseCount : 0;

        // Recent activity (merged timeline, last 10)
        $recentActivity = collect();
        foreach ($incomes->take(10) as $income) {
            $date = $income->date instanceof Carbon ? $income->date : Carbon::parse($income->date);
            $recentActivity->push([
                'id' => $income->id,
                'type' => 'income',
                'amount' => (float) $income->amount,
                'category' => $income->category,
                'description' => $income->description,
                'date' => $date->format('m/d/Y'),
                'timestamp' => $date->timestamp,
            ]);
        }
        foreach ($expenses->take(10) as $expense) {
            $date = $expense->date instanceof Carbon ? $expense->date : Carbon::parse($expense->date);
            $recentActivity->push([
                'id' => $expense->id,
                'type' => 'expense',
                'amount' => (float) $expense->amount,
                'category' => $expense->category,
                'description' => $expense->description,
                'date' => $date->format('m/d/Y'),
                'timestamp' => $date->timestamp,
            ]);
        }
        $recentActivity = $recentActivity->sortByDesc('timestamp')->take(8)->values()->all();

        // Spending streak: consecutive months with positive savings
        $streakCount = 0;
        $checkDate = $selectedDate->clone();
        for ($i = 0; $i < 12; $i++) {
            $mStart = $checkDate->clone()->startOfMonth();
            $mEnd = $checkDate->clone()->endOfMonth();
            $mIncome = Income::where('user_id', $user->id)->whereBetween('date', [$mStart, $mEnd])->sum('amount');
            $mExpense = Expense::where('user_id', $user->id)->whereBetween('date', [$mStart, $mEnd])->sum('amount');

            if ($mIncome > $mExpense && ($mIncome > 0 || $mExpense > 0)) {
                $streakCount++;
            } else {
                break;
            }
            $checkDate->subMonth();
        }

        // YTD data for year progress
        $ytdIncome = Income::where('user_id', $user->id)->whereBetween('date', [$yearStart, $selectedDate->clone()->endOfDay()])->sum('amount');
        $ytdExpense = Expense::where('user_id', $user->id)->whereBetween('date', [$yearStart, $selectedDate->clone()->endOfDay()])->sum('amount');

        // Total Wallet Balance
        $totalAccountBalance = Account::where('user_id', $user->id)
            ->where('is_archived', false)
            ->get()
            ->sum(function ($account) {
                return (float) $account->initial_balance + $account->incomes()->sum('amount') - $account->expenses()->sum('amount');
            });

        // Days with no spending this month
        $daysNoSpending = collect($dailySpending)->filter(fn ($d) => $d['amount'] == 0 && $d['day'] <= min($selectedDate->day, $daysInMonth))->count();

        // Budget
        $budget = Budget::where('user_id', $user->id)->first();

        // Financial goals
        $financialGoals = FinancialGoal::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        $now = Carbon::today();
        // Upcoming bills (next 30 days)
        $upcomingBills = RecurringTransaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->where('is_active', true)
            ->whereBetween('next_due_date', [$now->toDateString(), $now->copy()->addDays(30)->toDateString()])
            ->get()
            ->map(function ($bill) use ($now) {
                return [
                    'id' => $bill->id,
                    'name' => $bill->name,
                    'amount' => (float) $bill->amount,
                    'frequency' => $bill->frequency,
                    'next_due_date' => $bill->next_due_date->toDateString(),
                    'days_until' => $now->diffInDays($bill->next_due_date, false),
                    'color' => $bill->color,
                ];
            });

        // Recurring Incomes
        $recurringIncomes = RecurringTransaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->where('is_active', true)
            ->get()
            ->map(function ($income) {
                return [
                    'id' => $income->id,
                    'name' => $income->name,
                    'amount' => (float) $income->amount,
                    'frequency' => $income->frequency,
                    'next_due_date' => $income->next_due_date->toDateString(),
                    'color' => $income->color,
                ];
            });

        // Accounts (wallets)
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
                    'balance' => $balance,
                    'last4' => $account->last4,
                    'holder_name' => $account->holder_name,
                    'month_income' => (float) $monthIn,
                    'month_expense' => (float) $monthOut,
                ];
            });

        return Inertia::render('Dashboard', [
            'user' => $user,
            'dashboard' => [
                'totalIncome' => $totalIncome,
                'totalExpense' => $totalExpenses,
                'netIncome' => $netIncome,
                'netWorth' => $netWorth,
                'totalBalance' => (float) $totalAccountBalance,
                'prevMonthIncome' => (float) $prevMonthIncome,
                'prevMonthExpense' => (float) $prevMonthExpense,
            ],
            'incomeEntries' => $incomes->map(fn ($income) => [
                'id' => $income->id,
                'amount' => (float) $income->amount,
                'date' => $income->date instanceof Carbon
                    ? $income->date->format('m/d/Y')
                    : Carbon::parse($income->date)->format('m/d/Y'),
                'category' => $income->category,
                'description' => $income->description,
            ]),
            'expenseEntries' => $expenses->map(fn ($expense) => [
                'id' => $expense->id,
                'amount' => (float) $expense->amount,
                'date' => $expense->date instanceof Carbon
                    ? $expense->date->format('m/d/Y')
                    : Carbon::parse($expense->date)->format('m/d/Y'),
                'category' => $expense->category,
                'description' => $expense->description,
            ]),
            'dailySpending' => $dailySpending,
            'expensesByCategory' => $expensesByCategory,
            'topExpense' => $topExpenseData,
            'transactionStats' => [
                'total' => $totalTransactions,
                'incomeCount' => $incomeCount,
                'expenseCount' => $expenseCount,
                'avgExpense' => round($avgExpense, 2),
                'daysNoSpending' => $daysNoSpending,
            ],
            'recentActivity' => $recentActivity,
            'savingsStreak' => $streakCount,
            'ytd' => [
                'income' => (float) $ytdIncome,
                'expense' => (float) $ytdExpense,
                'net' => (float) ($ytdIncome - $ytdExpense),
                'dayOfYear' => $selectedDate->dayOfYear,
                'daysInYear' => $selectedDate->isLeapYear() ? 366 : 365,
            ],
            'budget' => $budget ? ['monthly_limit' => (float) $budget->monthly_limit] : null,
            'financialGoals' => $financialGoals->map(fn ($goal) => [
                'id' => $goal->id,
                'name' => $goal->name,
                'target_amount' => (float) $goal->target_amount,
                'current_amount' => (float) $goal->current_amount,
                'deadline' => $goal->deadline?->format('Y-m-d'),
                'color' => $goal->color,
            ]),
            'recurringIncomes' => $recurringIncomes,
            'upcomingBills' => $upcomingBills->values(),
            'accounts' => $accounts->values(),
        ]);
    }
}
