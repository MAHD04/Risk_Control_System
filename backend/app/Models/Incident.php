<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'risk_rule_id',
        'trade_id',
        'details',
        'triggered_at',
        'read_at',
    ];

    protected $casts = [
        'details' => 'array',
        'triggered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function riskRule(): BelongsTo
    {
        return $this->belongsTo(RiskRule::class);
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }
}
