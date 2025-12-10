import api from './api';

export interface Notification {
    id: number;
    account_id: number;
    risk_rule_id: number;
    trade_id: number | null;
    details: Record<string, unknown>;
    triggered_at: string;
    read_at: string | null;
    account?: {
        id: number;
        login: number;
    };
    riskRule?: {
        id: number;
        name: string;
    };
}

export interface UnreadResponse {
    success: boolean;
    data: {
        count: number;
        notifications: Notification[];
    };
}

export const notificationApi = {
    /**
     * Get unread notifications count and list.
     */
    getUnread: async (): Promise<UnreadResponse> => {
        const response = await api.get<UnreadResponse>('/incidents/unread');
        return response.data;
    },

    /**
     * Mark a single notification as read.
     */
    markAsRead: async (incidentId: number): Promise<void> => {
        await api.post(`/incidents/${incidentId}/read`);
    },

    /**
     * Mark all notifications as read.
     */
    markAllAsRead: async (): Promise<void> => {
        await api.post('/incidents/read-all');
    },
};
