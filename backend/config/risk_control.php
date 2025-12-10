<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Risk Control Configuration
    |--------------------------------------------------------------------------
    |
    | Centralized configuration for the Risk Control System.
    | These values can be overridden via environment variables.
    |
    */

    // Pagination
    'per_page' => env('RISK_CONTROL_PER_PAGE', 10),
    'max_per_page' => env('RISK_CONTROL_MAX_PER_PAGE', 100),

    // Incident Settings
    'incident' => [
        // Default time window for counting incidents (in hours)
        'count_window_hours' => env('INCIDENT_COUNT_WINDOW_HOURS', 24),
    ],

    // Action Settings
    'actions' => [
        // Email notification settings
        'email' => [
            'from_address' => env('RISK_ALERT_FROM_EMAIL', 'alerts@riskcontrol.com'),
            'from_name' => env('RISK_ALERT_FROM_NAME', 'Risk Control System'),
        ],
        
        // Slack notification settings
        'slack' => [
            'default_channel' => env('RISK_ALERT_SLACK_CHANNEL', '#risk-alerts'),
        ],
    ],

    // Supported action types
    'action_types' => [
        'NOTIFY_EMAIL',
        'NOTIFY_SLACK',
        'DISABLE_ACCOUNT',
        'DISABLE_TRADING',
        'ALERT',
        'CLOSE_TRADE',
    ],

    // Supported rule types
    'rule_types' => [
        'min_duration',
        'max_position_size',
        'max_daily_loss',
        'max_trade_volume',
        'volume_consistency',
        'trade_frequency',
    ],

    // Severity levels
    'severity_levels' => [
        'HARD',
        'SOFT',
    ],
];
