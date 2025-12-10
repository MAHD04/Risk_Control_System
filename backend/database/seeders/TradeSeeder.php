<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TradeSeeder extends Seeder
{
    /**
     * Seed test trades.
     */
    public function run(): void
    {
        $accounts = Account::all();
        
        if ($accounts->isEmpty()) {
            $this->command->warn('No accounts found. Skipping TradeSeeder.');
            return;
        }

        $symbols = ['EURUSD', 'GBPUSD', 'USDJPY', 'AUDUSD', 'USDCAD'];
        $now = Carbon::now();

        foreach ($accounts as $account) {
            // Create 8-12 trades per account
            $numTrades = rand(8, 12);
            
            for ($i = 0; $i < $numTrades; $i++) {
                $openTime = $now->copy()->subHours(rand(1, 72))->subMinutes(rand(0, 59));
                $isClosed = rand(0, 10) > 3; // 70% closed
                $duration = $isClosed ? rand(5, 3600) : null; // 5 seconds to 1 hour
                $closeTime = $isClosed ? $openTime->copy()->addSeconds($duration) : null;

                $type = rand(0, 1) ? 'BUY' : 'SELL';
                $volume = rand(1, 50) / 10; // 0.1 to 5.0 lots
                $openPrice = $this->getRandomPrice($symbols[array_rand($symbols)]);
                $closePrice = $isClosed 
                    ? $openPrice + (rand(-100, 100) / 10000) 
                    : null;

                Trade::create([
                    'account_id' => $account->id,
                    'type' => $type,
                    'volume' => $volume,
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                    'open_price' => $openPrice,
                    'close_price' => $closePrice,
                    'status' => $isClosed ? 'CLOSED' : 'OPEN',
                ]);
            }
        }

        $this->command->info('Created ' . Trade::count() . ' test trades.');
    }

    private function getRandomPrice(string $symbol): float
    {
        $basePrices = [
            'EURUSD' => 1.0850,
            'GBPUSD' => 1.2650,
            'USDJPY' => 149.50,
            'AUDUSD' => 0.6550,
            'USDCAD' => 1.3600,
        ];

        $base = $basePrices[$symbol] ?? 1.0000;
        return $base + (rand(-500, 500) / 10000);
    }
}
