<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Seed test accounts.
     */
    public function run(): void
    {
        $accounts = [
            [
                'login' => 21002025,
                'status' => 'enable',
                'trading_status' => 'enable',
            ],
            [
                'login' => 21002026,
                'status' => 'enable',
                'trading_status' => 'enable',
            ],
            [
                'login' => 21002027,
                'status' => 'enable',
                'trading_status' => 'enable',
            ],
        ];

        foreach ($accounts as $account) {
            Account::firstOrCreate(
                ['login' => $account['login']],
                $account
            );
        }
    }
}
