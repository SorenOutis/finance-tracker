<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // ─── Authentication ─────────────────────────────────────────

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_dashboard(): void
    {
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    // ─── Dashboard Data Structure ───────────────────────────────

    public function test_dashboard_returns_all_expected_props(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertOk();

        $page = $response->original->getData()['page'];
        $props = $page['props'];

        // Core data
        $this->assertArrayHasKey('dashboard', $props);
        $this->assertArrayHasKey('incomeEntries', $props);
        $this->assertArrayHasKey('expenseEntries', $props);

        // New feature data
        $this->assertArrayHasKey('dailySpending', $props);
        $this->assertArrayHasKey('expensesByCategory', $props);
        $this->assertArrayHasKey('topExpense', $props);
        $this->assertArrayHasKey('transactionStats', $props);
        $this->assertArrayHasKey('recentActivity', $props);
        $this->assertArrayHasKey('savingsStreak', $props);
        $this->assertArrayHasKey('ytd', $props);
        $this->assertArrayHasKey('budget', $props);
        $this->assertArrayHasKey('financialGoals', $props);
        $this->assertArrayHasKey('recurringTransactions', $props);
        $this->assertArrayHasKey('upcomingBills', $props);
    }

    public function test_dashboard_stats_are_correct(): void
    {
        $now = Carbon::now();

        Income::create(['user_id' => $this->user->id, 'amount' => 5000, 'date' => $now->format('Y-m-d'), 'category' => 'Salary']);
        Income::create(['user_id' => $this->user->id, 'amount' => 2000, 'date' => $now->format('Y-m-d'), 'category' => 'Freelance']);
        Expense::create(['user_id' => $this->user->id, 'amount' => 1500, 'date' => $now->format('Y-m-d'), 'category' => 'Food']);
        Expense::create(['user_id' => $this->user->id, 'amount' => 800, 'date' => $now->format('Y-m-d'), 'category' => 'Transport']);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $props = $response->original->getData()['page']['props'];

        $this->assertEquals(7000, $props['dashboard']['totalIncome']);
        $this->assertEquals(2300, $props['dashboard']['totalExpense']);
        $this->assertEquals(4700, $props['dashboard']['netIncome']);
    }

    public function test_transaction_stats_are_accurate(): void
    {
        $now = Carbon::now();

        Income::create(['user_id' => $this->user->id, 'amount' => 5000, 'date' => $now->format('Y-m-d')]);
        Expense::create(['user_id' => $this->user->id, 'amount' => 1000, 'date' => $now->format('Y-m-d')]);
        Expense::create(['user_id' => $this->user->id, 'amount' => 2000, 'date' => $now->format('Y-m-d')]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $props = $response->original->getData()['page']['props'];

        $this->assertEquals(3, $props['transactionStats']['total']);
        $this->assertEquals(1, $props['transactionStats']['incomeCount']);
        $this->assertEquals(2, $props['transactionStats']['expenseCount']);
        $this->assertEquals(1500, $props['transactionStats']['avgExpense']);
    }

    public function test_top_expense_is_correct(): void
    {
        $now = Carbon::now();

        Expense::create(['user_id' => $this->user->id, 'amount' => 500, 'date' => $now->format('Y-m-d'), 'category' => 'Food']);
        Expense::create(['user_id' => $this->user->id, 'amount' => 3000, 'date' => $now->format('Y-m-d'), 'category' => 'Rent']);
        Expense::create(['user_id' => $this->user->id, 'amount' => 200, 'date' => $now->format('Y-m-d'), 'category' => 'Transport']);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $props = $response->original->getData()['page']['props'];

        $this->assertNotNull($props['topExpense']);
        $this->assertEquals(3000, $props['topExpense']['amount']);
        $this->assertEquals('Rent', $props['topExpense']['category']);
    }

    public function test_daily_spending_has_correct_days(): void
    {
        $now = Carbon::now();

        Expense::create(['user_id' => $this->user->id, 'amount' => 100, 'date' => $now->format('Y-m-d')]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $props = $response->original->getData()['page']['props'];

        $daysInMonth = $now->daysInMonth;
        $this->assertCount($daysInMonth, $props['dailySpending']);
    }

    public function test_expenses_by_category_is_correct(): void
    {
        $now = Carbon::now();

        Expense::create(['user_id' => $this->user->id, 'amount' => 1000, 'date' => $now->format('Y-m-d'), 'category' => 'Food']);
        Expense::create(['user_id' => $this->user->id, 'amount' => 500, 'date' => $now->format('Y-m-d'), 'category' => 'Food']);
        Expense::create(['user_id' => $this->user->id, 'amount' => 500, 'date' => $now->format('Y-m-d'), 'category' => 'Transport']);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $props = $response->original->getData()['page']['props'];

        $this->assertNotEmpty($props['expensesByCategory']);
        // Food should be first (highest)
        $this->assertEquals('Food', $props['expensesByCategory'][0]['category']);
        $this->assertEquals(1500, $props['expensesByCategory'][0]['amount']);
        $this->assertEquals(75.0, $props['expensesByCategory'][0]['percentage']);
    }

    public function test_recent_activity_contains_both_types(): void
    {
        $now = Carbon::now();

        Income::create(['user_id' => $this->user->id, 'amount' => 5000, 'date' => $now->format('Y-m-d'), 'category' => 'Salary']);
        Expense::create(['user_id' => $this->user->id, 'amount' => 200, 'date' => $now->format('Y-m-d'), 'category' => 'Food']);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $props = $response->original->getData()['page']['props'];

        $this->assertNotEmpty($props['recentActivity']);
        $types = array_column($props['recentActivity'], 'type');
        $this->assertContains('income', $types);
        $this->assertContains('expense', $types);
    }

    public function test_ytd_data_is_correct(): void
    {
        $now = Carbon::now();

        Income::create(['user_id' => $this->user->id, 'amount' => 10000, 'date' => $now->format('Y-m-d')]);
        Expense::create(['user_id' => $this->user->id, 'amount' => 3000, 'date' => $now->format('Y-m-d')]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $props = $response->original->getData()['page']['props'];

        $this->assertEquals(10000, $props['ytd']['income']);
        $this->assertEquals(3000, $props['ytd']['expense']);
        $this->assertEquals(7000, $props['ytd']['net']);
        $this->assertGreaterThan(0, $props['ytd']['dayOfYear']);
        $this->assertGreaterThanOrEqual(365, $props['ytd']['daysInYear']);
    }

    public function test_user_data_is_isolated(): void
    {
        $otherUser = User::factory()->create();
        $now = Carbon::now();

        Income::create(['user_id' => $otherUser->id, 'amount' => 99999, 'date' => $now->format('Y-m-d')]);
        Expense::create(['user_id' => $otherUser->id, 'amount' => 50000, 'date' => $now->format('Y-m-d')]);

        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $props = $response->original->getData()['page']['props'];

        $this->assertEquals(0, $props['dashboard']['totalIncome']);
        $this->assertEquals(0, $props['dashboard']['totalExpense']);
    }

    public function test_empty_dashboard_works(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $props = $response->original->getData()['page']['props'];

        $this->assertEquals(0, $props['dashboard']['totalIncome']);
        $this->assertEquals(0, $props['dashboard']['totalExpense']);
        $this->assertEquals(0, $props['dashboard']['netIncome']);
        $this->assertNull($props['topExpense']);
        $this->assertEmpty($props['recentActivity']);
        $this->assertEquals(0, $props['savingsStreak']);
        $this->assertNull($props['budget']);
        $this->assertEmpty($props['financialGoals']);
    }
}
