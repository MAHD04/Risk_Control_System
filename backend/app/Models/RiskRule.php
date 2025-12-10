<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiskRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'rule_type',
        'parameters',
        'severity',
        'incident_limit',
        'is_active',
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_active' => 'boolean',
        'incident_limit' => 'integer',
    ];

    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(ConfiguredAction::class, 'risk_rule_action');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function isHard(): bool
    {
        return $this->severity === 'HARD';
    }

    public function isSoft(): bool
    {
        return $this->severity === 'SOFT';
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get a specific parameter value from the JSON parameters.
     */
    public function getParameter(string $key, mixed $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }
}
