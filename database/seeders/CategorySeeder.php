<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Default expense categories with predefined colors and icons.
     */
    public static array $defaultExpenseCategories = [
        ['name' => 'Groceries',     'color' => '#10B981', 'icon' => 'ShoppingCart',   'type' => 'expense', 'sort_order' => 1],
        ['name' => 'Dining Out',    'color' => '#F59E0B', 'icon' => 'Utensils',       'type' => 'expense', 'sort_order' => 2],
        ['name' => 'Transport',     'color' => '#3B82F6', 'icon' => 'MapPin',         'type' => 'expense', 'sort_order' => 3],
        ['name' => 'Shopping',      'color' => '#EC4899', 'icon' => 'ShoppingBag',    'type' => 'expense', 'sort_order' => 4],
        ['name' => 'Utilities',     'color' => '#6366F1', 'icon' => 'Zap',            'type' => 'expense', 'sort_order' => 5],
        ['name' => 'Rent',          'color' => '#8B5CF6', 'icon' => 'Home',           'type' => 'expense', 'sort_order' => 6],
        ['name' => 'Health',        'color' => '#EF4444', 'icon' => 'Heart',          'type' => 'expense', 'sort_order' => 7],
        ['name' => 'Entertainment', 'color' => '#F97316', 'icon' => 'Film',           'type' => 'expense', 'sort_order' => 8],
        ['name' => 'Education',     'color' => '#14B8A6', 'icon' => 'BookOpen',       'type' => 'expense', 'sort_order' => 9],
        ['name' => 'Other',         'color' => '#6B7280', 'icon' => 'MoreHorizontal', 'type' => 'expense', 'sort_order' => 10],
    ];

    /**
     * Default income categories with predefined colors and icons.
     */
    public static array $defaultIncomeCategories = [
        ['name' => 'Salary',     'color' => '#10B981', 'icon' => 'Briefcase',      'type' => 'income', 'sort_order' => 1],
        ['name' => 'Freelance',  'color' => '#3B82F6', 'icon' => 'Laptop',         'type' => 'income', 'sort_order' => 2],
        ['name' => 'Business',   'color' => '#8B5CF6', 'icon' => 'Building',       'type' => 'income', 'sort_order' => 3],
        ['name' => 'Investment', 'color' => '#F59E0B', 'icon' => 'TrendingUp',     'type' => 'income', 'sort_order' => 4],
        ['name' => 'Gifts',      'color' => '#EC4899', 'icon' => 'Gift',           'type' => 'income', 'sort_order' => 5],
        ['name' => 'Other',      'color' => '#6B7280', 'icon' => 'MoreHorizontal', 'type' => 'income', 'sort_order' => 6],
    ];

    /**
     * Run the seeder for the default test user.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();

        if ($user) {
            $this->runForUser($user->id);
            $this->command?->info('Default categories seeded for user: '.$user->email);
        }
    }

    /**
     * Seed default categories for a specific user.
     * Uses firstOrCreate so it's safe to call multiple times.
     */
    public static function runForUser(int $userId): void
    {
        $all = array_merge(self::$defaultExpenseCategories, self::$defaultIncomeCategories);

        foreach ($all as $cat) {
            Category::firstOrCreate(
                ['user_id' => $userId, 'name' => $cat['name'], 'type' => $cat['type']],
                $cat
            );
        }
    }
}
