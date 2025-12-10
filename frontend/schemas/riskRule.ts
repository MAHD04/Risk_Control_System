import { z } from 'zod';

/**
 * Zod schema for Risk Rule validation.
 */
export const riskRuleSchema = z.object({
    name: z
        .string()
        .min(1, 'Name is required')
        .max(255, 'Name cannot exceed 255 characters'),
    description: z
        .string()
        .max(1000, 'Description cannot exceed 1000 characters')
        .optional()
        .or(z.literal('')),
    rule_type: z.enum(['min_duration', 'volume_consistency', 'trade_frequency'], {
        message: 'Please select a valid rule type',
    }),
    severity: z.enum(['HARD', 'SOFT'], {
        message: 'Please select a severity level',
    }),
    incident_limit: z
        .number()
        .min(1, 'Incident limit must be at least 1')
        .default(1),
    is_active: z.boolean().default(true),
    parameters: z.record(z.string(), z.unknown()).optional(),
});

export type RiskRuleFormData = z.infer<typeof riskRuleSchema>;

/**
 * Schema for min_duration rule parameters.
 */
export const minDurationParamsSchema = z.object({
    min_duration_seconds: z
        .number()
        .min(1, 'Duration must be at least 1 second')
        .max(86400, 'Duration cannot exceed 24 hours'),
});

/**
 * Schema for volume_consistency rule parameters.
 */
export const volumeConsistencyParamsSchema = z.object({
    min_volume_factor: z
        .number()
        .min(0.01, 'Minimum factor must be at least 0.01')
        .max(10, 'Minimum factor cannot exceed 10'),
    max_volume_factor: z
        .number()
        .min(0.01, 'Maximum factor must be at least 0.01')
        .max(10, 'Maximum factor cannot exceed 10'),
});

/**
 * Schema for trade_frequency rule parameters.
 */
export const tradeFrequencyParamsSchema = z.object({
    max_trades_per_hour: z
        .number()
        .min(1, 'Must allow at least 1 trade per hour')
        .max(1000, 'Cannot exceed 1000 trades per hour'),
});

export default riskRuleSchema;

