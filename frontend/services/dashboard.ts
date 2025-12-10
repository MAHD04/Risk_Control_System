import api from './api';
import type { DashboardStats } from '@/types';

const ENDPOINT = '/dashboard';

export interface IncidentActivityData {
    name: string;
    incidents: number;
    severity: number;
}

export interface IncidentActivityResponse {
    chart_data: IncidentActivityData[];
    percentage_change: number;
}

export interface RecentIncident {
    id: number;
    rule: string;
    account: string;
    severity: 'HARD' | 'SOFT';
    time: string;
}

export interface SystemStatus {
    api_connection: string;
    rule_engine: string;
    event_listener: string;
    last_sync: string;
}

export const dashboardApi = {
    /**
     * Get dashboard statistics
     */
    async getStats(): Promise<DashboardStats> {
        const response = await api.get<{ success: boolean; data: DashboardStats }>(
            `${ENDPOINT}/stats`
        );
        return response.data.data;
    },

    /**
     * Get incident activity data for chart
     */
    async getIncidentActivity(): Promise<IncidentActivityResponse> {
        const response = await api.get<{ success: boolean; data: IncidentActivityResponse }>(
            `${ENDPOINT}/incident-activity`
        );
        return response.data.data;
    },

    /**
     * Get recent incidents for dashboard
     */
    async getRecentIncidents(): Promise<RecentIncident[]> {
        const response = await api.get<{ success: boolean; data: RecentIncident[] }>(
            `${ENDPOINT}/recent-incidents`
        );
        return response.data.data;
    },

    /**
     * Get system status
     */
    async getSystemStatus(): Promise<SystemStatus> {
        const response = await api.get<{ success: boolean; data: SystemStatus }>(
            `${ENDPOINT}/system-status`
        );
        return response.data.data;
    },
};

