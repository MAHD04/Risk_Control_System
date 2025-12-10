<?php

namespace App\Models;

use App\Events\TradeSaved;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    use HasFactory;

    /**
     * Dispatch events when the model is created or updated.
     */
    protected $dispatchesEvents = [
        'created' => TradeSaved::class,
        'updated' => TradeSaved::class,
    ];

    protected $fillable = [
        'account_id',
        'type',
        'volume',
        'open_time',
        'close_time',
        'open_price',
        'close_price',
        'stop_loss',
        'status',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
        'open_price' => 'decimal:5',
        'close_price' => 'decimal:5',
        'stop_loss' => 'decimal:5',
        'open_time' => 'datetime',
        'close_time' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /**
     * Get the duration of the trade in seconds.
     * Returns null if the trade is still open.
     */
    public function getDurationInSeconds(): ?int
    {
        if (!$this->close_time) {
            return null;
        }

        return (int) abs($this->close_time->diffInSeconds($this->open_time));
    }

    /**
     * Check if the trade is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'CLOSED';
    }

    /**
     * Check if the trade is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'OPEN';
    }
}
