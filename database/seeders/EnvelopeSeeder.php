<?php

namespace Database\Seeders;

use App\Models\Envelope;
use Illuminate\Database\Seeder;

class EnvelopeSeeder extends Seeder
{
    /**
     * Default savings envelopes suggested for every new user.
     */
    public static array $defaultEnvelopes = [
        [
            'name' => 'Emergency Fund',
            'target_amount' => 50000,
            'current_amount' => 0,
            'color' => '#EF4444',
            'icon' => 'ShieldCheck',
            'deadline' => null,
            'sort_order' => 1,
        ],
        [
            'name' => 'Rent Fund',
            'target_amount' => 8500,
            'current_amount' => 0,
            'color' => '#6366F1',
            'icon' => 'Home',
            'deadline' => null,
            'sort_order' => 2,
        ],
        [
            'name' => 'Groceries Budget',
            'target_amount' => 10000,
            'current_amount' => 0,
            'color' => '#10B981',
            'icon' => 'ShoppingCart',
            'deadline' => null,
            'sort_order' => 3,
        ],
        [
            'name' => 'Vacation',
            'target_amount' => 30000,
            'current_amount' => 0,
            'color' => '#3B82F6',
            'icon' => 'MapPin',
            'deadline' => null,
            'sort_order' => 4,
        ],
        [
            'name' => 'Savings',
            'target_amount' => 20000,
            'current_amount' => 0,
            'color' => '#8B5CF6',
            'icon' => 'PiggyBank',
            'deadline' => null,
            'sort_order' => 5,
        ],
    ];

    /**
     * Run the seeder for the default test user.
     */
    public function run(): void
    {
        $user = \App\Models\User::first();

        if ($user) {
            $this->runForUser($user->id);
            $this->command?->info('Default envelopes seeded for user: '.$user->email);
        }
    }

    /**
     * Seed default envelopes for a specific user.
     * Uses firstOrCreate so it's safe to call multiple times.
     */
    public static function runForUser(int $userId): void
    {
        foreach (self::$defaultEnvelopes as $env) {
            Envelope::firstOrCreate(
                ['user_id' => $userId, 'name' => $env['name']],
                $env + ['user_id' => $userId]
            );
        }
    }
}
