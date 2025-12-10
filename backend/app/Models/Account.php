<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'login',
        'status',
        'trading_status',
        'balance',
        'initial_balance',
    ];

    protected $casts = [
        'login' => 'integer',
        'balance' => 'decimal:2',
        'initial_balance' => 'decimal:2',
    ];

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function isEnabled(): bool
    {
        return $this->status === 'enable';
    }

    public function isTradingEnabled(): bool
    {
        return $this->trading_status === 'enable';
    }

    public function disableAccount(): void
    {
        $this->update(['status' => 'disable']);
    }

    public function disableTrading(): void
    {
        $this->update(['trading_status' => 'disable']);
    }
}
