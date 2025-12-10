/**
 * Application-wide configuration constants
 * Centralized location for magic numbers and configurable values
 */

// Pagination
export const DEFAULT_PAGE_SIZE = 10;
export const PAGE_SIZE_OPTIONS = [10, 25, 50, 100];

// Polling intervals (in milliseconds)
export const NOTIFICATION_POLL_INTERVAL = 30000; // 30 seconds
export const DASHBOARD_REFRESH_INTERVAL = 60000; // 1 minute

// API
export const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
export const API_TIMEOUT = 30000; // 30 seconds

// UI
export const TOAST_DURATION = 5000; // 5 seconds
export const MODAL_ANIMATION_DURATION = 200; // milliseconds

// Pagination display
export const MAX_VISIBLE_PAGES = 5;

// Date/Time
export const DEFAULT_LOCALE = 'en-US';

// Feature flags (for future use)
export const FEATURES = {
    GLOBAL_SEARCH: false,
    TWO_FACTOR_AUTH: false,
    SESSION_MANAGEMENT: false,
} as const;
