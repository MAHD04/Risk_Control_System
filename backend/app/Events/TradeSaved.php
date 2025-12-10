<?php

namespace App\Events;

use App\Models\Trade;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event fired when a Trade is saved (created or updated).
 */
class TradeSaved
{
    use Dispatchable, SerializesModels;

    public Trade $trade;

    /**
     * Create a new event instance.
     */
    public function __construct(Trade $trade)
    {
        $this->trade = $trade;
    }
}
