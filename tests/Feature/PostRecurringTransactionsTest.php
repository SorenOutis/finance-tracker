<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Income;
use App\Models\RecurringTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostRecurringTransactionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ─── Income ──────────────────────────────────────────────────

    public function test_posts_due_income_transaction(): void
    {
        RecurringTransaction::create([
            'user_id' => $this->user->id,
            'name' => 'Salary',
            'amount' => 50000,
            'type' => 'income',
            'category' => 'Salary',
            'frequency' => 'monthly',
            'next_due_date' => Carbon::today(),
            'is_active' => true,
        ]);

        $this->artisan('finance:post-recurring')
            ->expectsOutputToContain('Posted 1 recurring transaction(s).')
            ->assertSuccessful();

        $this->assertDatabaseHas('incomes', [
            'user_id' => $this->user->id,
            'amount' => 50000,
            'category' => 'Salary',
        ]);
    }

    // ─── Expense ─────────────────────────────────────────────────

    public function test_posts_due_expense_transaction(): void
    {
        RecurringTransaction::create([
            'user_id' => $this->user->id,
            'name' => 'Netflix Subscription',
            'amount' => 499,
            'type' => 'expense',
            'category' => 'Entertainment',
            'frequency' => 'monthly',
            'next_due_date' => Carbon::today(),
            'is_active' => true,
        ]);

        $this->artisan('finance:post-recurring')
            ->expectsOutputToContain('Posted 1 recurring transaction(s).')
            ->assertSuccessful();

        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->user->id,
            'amount' => 499,
            'category' => 'Entertainment',
        ]);
    }

    // ─── Due Date Advancement ────────────────────────────────────

    public function test_advances_next_due_date_monthly(): void
    {
        $dueDate = Carbon::today();

        $tx = RecurringTransaction::create([
            'user_id' => $this->user->id,
            'name' => 'Monthly Rent',
            'amount' => 1000,
            'type' => 'expense',
            'frequency' => 'monthly',
            'next_due_date' => $dueDate,
            'is_active' => true,
        ]);

        $this->artisan('finance:post-recurring')
            ->expectsOutputToContain('Posted 1 recurring transaction(s).')
            ->assertSuccessful();

        // Check raw DB value (bypasses model casting)
        $saved = \Illuminate\Support\Facades\DB::table('recurring_transactions')
            ->where('id', $tx->id)
            ->value('next_due_date');
        $this->assertEquals(
            $dueDate->copy()->addMonth()->toDateString(),
            \Carbon\Carbon::parse($saved)->toDateString()
        );
    }

    public function test_advances_next_due_date_weekly(): void
    {
        $dueDate = Carbon::today();

        $tx = RecurringTransaction::create([
            'user_id' => $this->user->id,
            'name' => 'Weekly Allowance',
            'amount' => 500,
            'type' => 'income',
            'frequency' => 'weekly',
            'next_due_date' => $dueDate,
            'is_active' => true,
        ]);

        $this->artisan('finance:post-recurring')
            ->expectsOutputToContain('Posted 1 recurring transaction(s).')
            ->assertSuccessful();

        // Check raw DB value (bypasses model casting)
        $saved = \Illuminate\Support\Facades\DB::table('recurring_transactions')
            ->where('id', $tx->id)
            ->value('next_due_date');
        $this->assertEquals(
            $dueDate->copy()->addWeek()->toDateString(),
            \Carbon\Carbon::parse($saved)->toDateString()
        );
    }

    public function test_advances_next_due_date_yearly(): void
    {
        $dueDate = Carbon::today();

        $tx = RecurringTransaction::create([
            'user_id' => $this->user->id,
            'name' => 'Yearly Insurance',
            'amount' => 12000,
            'type' => 'expense',
            'frequency' => 'yearly',
            'next_due_date' => $dueDate,
            'is_active' => true,
        ]);

        $this->artisan('finance:post-recurring')
            ->expectsOutputToContain('Posted 1 recurring transaction(s).')
            ->assertSuccessful();

        // Check raw DB value (bypasses model casting)
        $saved = \Illuminate\Support\Facades\DB::table('recurring_transactions')
            ->where('id', $tx->id)
            ->value('next_due_date');
        $this->assertEquals(
            $dueDate->copy()->addYear()->toDateString(),
            \Carbon\Carbon::parse($saved)->toDateString()
        );
    }

    // ─── Inactive / Future ───────────────────────────────────────

    public function test_does_not_post_inactive_transactions(): void
    {
        RecurringTransaction::create([
            'user_id' => $this->user->id,
            'name' => 'Inactive Rent',
            'amount' => 1000,
            'type' => 'expense',
            'category' => 'Rent',
            'frequency' => 'monthly',
            'next_due_date' => Carbon::today(),
            'is_active' => false,
        ]);

        $this->artisan('finance:post-recurring')
            ->expectsOutput('No recurring transactions due today.')
            ->assertSuccessful();

        $this->assertDatabaseCount('expenses', 0);
    }

    public function test_does_not_post_future_transactions(): void
    {
        RecurringTransaction::create([
            'user_id' => $this->user->id,
            'name' => 'Future Bill',
            'amount' => 1000,
            'type' => 'expense',
            'frequency' => 'monthly',
            'next_due_date' => Carbon::tomorrow(),
            'is_active' => true,
        ]);

        $this->artisan('finance:post-recurring')
            ->expectsOutput('No recurring transactions due today.')
            ->assertSuccessful();

        $this->assertDatabaseCount('expenses', 0);
    }

    // ─── Empty / Edge Cases ──────────────────────────────────────

    public function test_handles_no_due_transactions(): void
    {
        $this->artisan('finance:post-recurring')
            ->expectsOutput('No recurring transactions due today.')
            ->assertSuccessful();
    }

    public function test_posts_multiple_due_transactions(): void
    {
        RecurringTransaction::create([
            'user_id' => $this->user->id,
            'name' => 'Monthly Salary',
            'amount' => 50000,
            'type' => 'income',
            'category' => 'Salary',
            'frequency' => 'monthly',
            'next_due_date' => Carbon::today(),
            'is_active' => true,
        ]);

        RecurringTransaction::create([
            'user_id' => $this->user->id,
            'name' => 'Monthly Rent',
            'amount' => 1500,
            'type' => 'expense',
            'category' => 'Rent',
            'frequency' => 'monthly',
            'next_due_date' => Carbon::today(),
            'is_active' => true,
        ]);

        $this->artisan('finance:post-recurring')
            ->expectsOutputToContain('Posted 2 recurring transaction(s).')
            ->assertSuccessful();

        $this->assertDatabaseCount('incomes', 1);
        $this->assertDatabaseCount('expenses', 1);
    }

    public function test_sets_auto_description_from_name(): void
    {
        RecurringTransaction::create([
            'user_id' => $this->user->id,
            'name' => 'Netflix Premium',
            'amount' => 999,
            'type' => 'expense',
            'category' => 'Entertainment',
            'frequency' => 'monthly',
            'next_due_date' => Carbon::today(),
            'is_active' => true,
        ]);

        $this->artisan('finance:post-recurring')->assertSuccessful();

        $this->assertDatabaseHas('expenses', [
            'description' => 'Auto: Netflix Premium',
        ]);
    }
}
