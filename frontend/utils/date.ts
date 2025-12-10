/**
 * Centralized date formatting utilities
 * Use these functions across the application for consistent date/time display
 */

const DEFAULT_LOCALE = 'en-US';

/**
 * Format a date string to a localized date and time string
 */
export function formatDateTime(dateString: string | undefined | null): string {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString(DEFAULT_LOCALE, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

/**
 * Format a date string to a localized date only string
 */
export function formatDate(dateString: string | undefined | null): string {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString(DEFAULT_LOCALE, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

/**
 * Format a date string to a localized time only string
 */
export function formatTime(dateString: string | undefined | null): string {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleTimeString(DEFAULT_LOCALE, {
        hour: '2-digit',
        minute: '2-digit',
    });
}

/**
 * Get a relative time string (e.g., "2 hours ago", "3 days ago")
 */
export function getRelativeTime(dateString: string | undefined | null): string {
    if (!dateString) return '-';

    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffSeconds = Math.floor(diffMs / 1000);
    const diffMinutes = Math.floor(diffSeconds / 60);
    const diffHours = Math.floor(diffMinutes / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffSeconds < 60) {
        return 'Just now';
    } else if (diffMinutes < 60) {
        return `${diffMinutes} minute${diffMinutes === 1 ? '' : 's'} ago`;
    } else if (diffHours < 24) {
        return `${diffHours} hour${diffHours === 1 ? '' : 's'} ago`;
    } else if (diffDays < 7) {
        return `${diffDays} day${diffDays === 1 ? '' : 's'} ago`;
    } else {
        return formatDate(dateString);
    }
}

/**
 * Calculate duration between two dates in a human-readable format
 */
export function calculateDuration(startDate: string | undefined | null, endDate: string | undefined | null): string {
    if (!startDate || !endDate) return '-';

    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffMs = end.getTime() - start.getTime();

    if (diffMs < 0) return '-';

    const diffSeconds = Math.floor(diffMs / 1000);
    const diffMinutes = Math.floor(diffSeconds / 60);
    const diffHours = Math.floor(diffMinutes / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffDays > 0) {
        const remainingHours = diffHours % 24;
        return `${diffDays}d ${remainingHours}h`;
    } else if (diffHours > 0) {
        const remainingMinutes = diffMinutes % 60;
        return `${diffHours}h ${remainingMinutes}m`;
    } else if (diffMinutes > 0) {
        const remainingSeconds = diffSeconds % 60;
        return `${diffMinutes}m ${remainingSeconds}s`;
    } else {
        return `${diffSeconds}s`;
    }
}
