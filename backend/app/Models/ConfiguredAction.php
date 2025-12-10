<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ConfiguredAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'action_type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(RiskRule::class, 'risk_rule_action');
    }

    /**
     * Get a specific config value from the JSON config.
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }
}
