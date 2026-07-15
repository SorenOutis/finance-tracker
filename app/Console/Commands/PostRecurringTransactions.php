<?php

namespace App\Console\Commands;

use App\Models\Expense;
use App\Models\Income;
use App\Models\RecurringTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PostRecurringTransactions extends Command
{
    protected $signature = 'finance:post-recurring';
    protected $description = 'Auto-create income/expense records for due recurring transactions and advance their due dates';

    public function handle(): int
    {
        $today = Carbon::today();

        $dueTransactions = RecurringTransaction::where('is_active', true)
            ->where('next_due_date', '<=', $today)
            ->get();

        if ($dueTransactions->isEmpty()) {
            $this->info('No recurring transactions due today.');

            return Command::SUCCESS;
        }

        $posted = 0;

        foreach ($dueTransactions as $tx) {
            if ($tx->type === 'income') {
                Income::create([
                    'user_id' => $tx->user_id,
                    'account_id' => $tx->account_id,
                    'amount' => $tx->amount,
                    'date' => $today,
                    'category' => $tx->category,
                    'description' => 'Auto: '.($tx->name ?? $tx->category ?? 'Recurring Income'),
                ]);
            } elseif ($tx->type === 'expense') {
                Expense::create([
                    'user_id' => $tx->user_id,
                    'account_id' => $tx->account_id,
                    'amount' => $tx->amount,
                    'date' => $today,
                    'category' => $tx->category,
                    'description' => 'Auto: '.($tx->name ?? $tx->category ?? 'Recurring Expense'),
                ]);
            }

            // Advance the next due date
            // Must assign the match result because with CarbonImmutable,
            // addMonth()/addWeek()/addYear() return a new instance instead of mutating
            $nextDate = $tx->next_due_date->copy();
            $nextDate = match ($tx->frequency) {
                'weekly' => $nextDate->addWeek(),
                'monthly' => $nextDate->addMonth(),
                'yearly' => $nextDate->addYear(),
                default => $nextDate->addMonth(),
            };

            $tx->update(['next_due_date' => $nextDate]);
            $posted++;
        }

        $this->info("Posted {$posted} recurring transaction(s).");

        return Command::SUCCESS;
    }
}
