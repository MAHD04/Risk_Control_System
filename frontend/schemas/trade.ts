import { z } from 'zod';

/**
 * Zod schema for Trade validation.
 */
export const tradeSchema = z.object({
    account_id: z
        .number({ message: 'Account is required' })
        .int('Account ID must be an integer')
        .positive('Account ID must be positive'),
    type: z.enum(['BUY', 'SELL'], {
        message: 'Trade type must be BUY or SELL',
    }),
    volume: z
        .number({ message: 'Volume is required' })
        .min(0.01, 'Volume must be at least 0.01'),
    open_time: z
        .string()
        .min(1, 'Open time is required'),
    close_time: z
        .string()
        .optional()
        .or(z.literal('')),
    open_price: z
        .number({ message: 'Open price is required' })
        .min(0, 'Open price cannot be negative'),
    close_price: z
        .number()
        .min(0, 'Close price cannot be negative')
        .optional(),
    status: z.enum(['OPEN', 'CLOSED'], {
        message: 'Status must be OPEN or CLOSED',
    }),
});

export type TradeFormData = z.infer<typeof tradeSchema>;

/**
 * Zod schema for Account validation.
 */
export const accountSchema = z.object({
    login: z
        .number({ message: 'Login ID is required' })
        .int('Login must be an integer')
        .positive('Login must be positive'),
    status: z.enum(['enable', 'disable']).default('enable'),
    trading_status: z.enum(['enable', 'disable']).default('enable'),
});

export type AccountFormData = z.infer<typeof accountSchema>;

export default tradeSchema;

