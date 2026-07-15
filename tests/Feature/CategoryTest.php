<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
        $this->user = User::factory()->create();
    }

    // ─── Authentication ──────────────────────────────────────────

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('categories'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_categories(): void
    {
        $this->actingAs($this->user)
            ->get(route('categories'))
            ->assertOk();
    }

    // ─── Index ───────────────────────────────────────────────────

    public function test_index_returns_categories_grouped_by_type(): void
    {
        Category::create(['user_id' => $this->user->id, 'name' => 'Salary', 'type' => 'income', 'sort_order' => 1]);
        Category::create(['user_id' => $this->user->id, 'name' => 'Freelance', 'type' => 'income', 'sort_order' => 2]);
        Category::create(['user_id' => $this->user->id, 'name' => 'Food', 'type' => 'expense', 'sort_order' => 1]);
        Category::create(['user_id' => $this->user->id, 'name' => 'Rent', 'type' => 'expense', 'sort_order' => 2]);

        $response = $this->actingAs($this->user)->get(route('categories'));
        $props = $response->original->getData()['page']['props'];

        $this->assertCount(2, $props['incomeCategories']);
        $this->assertCount(2, $props['expenseCategories']);
        $this->assertEquals('Salary', $props['incomeCategories'][0]['name']);
        $this->assertEquals('Food', $props['expenseCategories'][0]['name']);
    }

    public function test_index_only_shows_own_categories(): void
    {
        $otherUser = User::factory()->create();
        Category::create(['user_id' => $otherUser->id, 'name' => 'Other Salary', 'type' => 'income']);

        $response = $this->actingAs($this->user)->get(route('categories'));
        $props = $response->original->getData()['page']['props'];

        $this->assertEmpty($props['incomeCategories']);
        $this->assertEmpty($props['expenseCategories']);
    }

    // ─── Store ───────────────────────────────────────────────────

    public function test_can_create_income_category(): void
    {
        $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Dividends',
            'type' => 'income',
            'color' => '#10B981',
            'icon' => 'TrendingUp',
            'sort_order' => 5,
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'name' => 'Dividends',
            'type' => 'income',
            'color' => '#10B981',
            'icon' => 'TrendingUp',
            'sort_order' => 5,
        ]);
    }

    public function test_can_create_expense_category(): void
    {
        $this->actingAs($this->user)->post(route('categories.store'), [
            'name' => 'Transport',
            'type' => 'expense',
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'user_id' => $this->user->id,
            'name' => 'Transport',
            'type' => 'expense',
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $this->actingAs($this->user)
            ->post(route('categories.store'), [])
            ->assertSessionHasErrors(['name', 'type']);
    }

    public function test_store_validates_type_must_be_income_or_expense(): void
    {
        $this->actingAs($this->user)
            ->post(route('categories.store'), [
                'name' => 'Test',
                'type' => 'invalid',
            ])
            ->assertSessionHasErrors(['type']);
    }

    // ─── Update ──────────────────────────────────────────────────

    public function test_can_update_category(): void
    {
        $category = Category::create([
            'user_id' => $this->user->id,
            'name' => 'Old Name',
            'type' => 'expense',
        ]);

        $this->actingAs($this->user)->patch(route('categories.update', $category), [
            'name' => 'New Name',
            'type' => 'expense',
            'color' => '#EF4444',
            'icon' => 'Heart',
            'sort_order' => 10,
        ])->assertRedirect();

        $category->refresh();
        $this->assertEquals('New Name', $category->name);
        $this->assertEquals('#EF4444', $category->color);
        $this->assertEquals('Heart', $category->icon);
        $this->assertEquals(10, $category->sort_order);
    }

    public function test_cannot_update_another_users_category(): void
    {
        $otherUser = User::factory()->create();
        $category = Category::create([
            'user_id' => $otherUser->id,
            'name' => 'Secret',
            'type' => 'expense',
        ]);

        $this->actingAs($this->user)
            ->patch(route('categories.update', $category), [
                'name' => 'Hacked',
                'type' => 'expense',
            ])
            ->assertForbidden();

        $category->refresh();
        $this->assertEquals('Secret', $category->name);
    }

    // ─── Destroy ─────────────────────────────────────────────────

    public function test_can_delete_category(): void
    {
        $category = Category::create([
            'user_id' => $this->user->id,
            'name' => 'Temp Category',
            'type' => 'expense',
        ]);

        $this->actingAs($this->user)
            ->delete(route('categories.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_cannot_delete_another_users_category(): void
    {
        $otherUser = User::factory()->create();
        $category = Category::create([
            'user_id' => $otherUser->id,
            'name' => 'Protected',
            'type' => 'expense',
        ]);

        $this->actingAs($this->user)
            ->delete(route('categories.destroy', $category))
            ->assertForbidden();

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    // ─── Seed Defaults ───────────────────────────────────────────

    public function test_seed_defaults_creates_categories(): void
    {
        $this->actingAs($this->user)
            ->post(route('categories.seed-defaults'))
            ->assertRedirect();

        $expenseCount = Category::where('user_id', $this->user->id)->where('type', 'expense')->count();
        $incomeCount = Category::where('user_id', $this->user->id)->where('type', 'income')->count();

        $this->assertEquals(10, $expenseCount);
        $this->assertEquals(6, $incomeCount);
    }

    public function test_seed_defaults_is_idempotent(): void
    {
        $this->actingAs($this->user)->post(route('categories.seed-defaults'));
        $this->actingAs($this->user)->post(route('categories.seed-defaults'));

        $expenseCount = Category::where('user_id', $this->user->id)->where('type', 'expense')->count();
        $incomeCount = Category::where('user_id', $this->user->id)->where('type', 'income')->count();

        // Should be the same after second call (firstOrCreate)
        $this->assertEquals(10, $expenseCount);
        $this->assertEquals(6, $incomeCount);
    }

    // ─── Empty State ─────────────────────────────────────────────

    public function test_index_returns_empty_arrays_when_no_categories(): void
    {
        $response = $this->actingAs($this->user)->get(route('categories'));
        $props = $response->original->getData()['page']['props'];

        $this->assertEmpty($props['incomeCategories']);
        $this->assertEmpty($props['expenseCategories']);
    }
}
