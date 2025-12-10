<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user (use firstOrCreate to avoid duplicates)
        User::firstOrCreate(
            ['email' => 'admin@mmtech.com'],
            [
                'name' => 'Test Admin',
                'email' => 'admin@mmtech.com',
                'password' => bcrypt('password'),
            ]
        );

        // Seed in order: Actions first, then Rules (which attach actions), then Accounts, Trades, Incidents
        $this->call([
            ConfiguredActionSeeder::class,
            RiskRuleSeeder::class,
            AccountSeeder::class,
            TradeSeeder::class,
            IncidentSeeder::class,
        ]);
    }
}
