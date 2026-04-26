<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use App\Models\Income;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HistoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $q = trim((string) $request->query('q', ''));

        $accountMap = Account::where('user_id', $user->id)
            ->get(['id', 'name', 'color', 'type'])
            ->keyBy('id');

        $applySearch = function ($qb) use ($q) {
            if ($q === '') {
                return;
            }
            $qb->where(function ($w) use ($q) {
                $w->where('category', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        };

        $incomes = Income::where('user_id', $user->id)
            ->tap($applySearch)
            ->get()
            ->map(fn ($i) => [
                'id' => 'i'.$i->id,
                'raw_id' => $i->id,
                'type' => 'income',
                'amount' => (float) $i->amount,
                'category' => $i->category,
                'description' => $i->description,
                'date' => $i->date->toDateString(),
                'account' => $accountMap[$i->account_id] ?? null,
            ]);

        $expenses = Expense::where('user_id', $user->id)
            ->tap($applySearch)
            ->get()
            ->map(fn ($e) => [
                'id' => 'e'.$e->id,
                'raw_id' => $e->id,
                'type' => 'expense',
                'amount' => (float) $e->amount,
                'category' => $e->category,
                'description' => $e->description,
                'date' => $e->date->toDateString(),
                'account' => $accountMap[$e->account_id] ?? null,
            ]);

        $items = $incomes
            ->concat($expenses)
            ->sortByDesc('date')
            ->values();

        return Inertia::render('History', [
            'items' => $items,
            'query' => $q,
        ]);
    }
}
