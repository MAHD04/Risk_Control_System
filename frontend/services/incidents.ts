import api from './api';
import type { Incident, AccountStats, PaginatedResponse } from '@/types';

const ENDPOINT = '/incidents';

export interface IncidentFilters {
    account_id?: number;
    risk_rule_id?: number;
    page?: number;
    per_page?: number;
}

export const incidentsApi = {
    /**
     * Get all incidents with optional filters and pagination
     */
    async getAll(filters?: IncidentFilters): Promise<PaginatedResponse<Incident>> {
        const params = new URLSearchParams();

        if (filters?.account_id) params.append('account_id', filters.account_id.toString());
        if (filters?.risk_rule_id) params.append('risk_rule_id', filters.risk_rule_id.toString());
        if (filters?.page) params.append('page', filters.page.toString());
        if (filters?.per_page) params.append('per_page', filters.per_page.toString());

        const response = await api.get<PaginatedResponse<Incident>>(
            `${ENDPOINT}${params.toString() ? `?${params}` : ''}`
        );
        return response.data;
    },

    /**
     * Get a single incident by ID
     */
    async getById(id: number): Promise<Incident> {
        const response = await api.get<{ data: Incident }>(`${ENDPOINT}/${id}`);
        return response.data.data;
    },

    /**
     * Get statistics for a specific account
     */
    async getAccountStats(accountId: number): Promise<AccountStats> {
        const response = await api.get<{ data: AccountStats }>(
            `${ENDPOINT}/account/${accountId}/stats`
        );
        return response.data.data;
    },
};
