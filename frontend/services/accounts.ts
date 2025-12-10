import api from './api';
import type { Account, PaginatedResponse, CreateAccountRequest } from '@/types';

const ENDPOINT = '/accounts';

export interface AccountFilters {
    status?: 'enable' | 'disable';
    page?: number;
    per_page?: number;
}

export const accountsApi = {
    /**
     * Get all accounts with optional filters and pagination
     */
    async getAll(filters?: AccountFilters): Promise<PaginatedResponse<Account>> {
        const params = new URLSearchParams();

        if (filters?.status) params.append('status', filters.status);
        if (filters?.page) params.append('page', filters.page.toString());
        if (filters?.per_page) params.append('per_page', filters.per_page.toString());

        const response = await api.get<PaginatedResponse<Account>>(
            `${ENDPOINT}${params.toString() ? `?${params}` : ''}`
        );
        return response.data;
    },

    /**
     * Get a single account by ID
     */
    async getById(id: number): Promise<Account> {
        const response = await api.get<{ data: Account }>(`${ENDPOINT}/${id}`);
        return response.data.data;
    },

    /**
     * Create a new account
     */
    async create(data: CreateAccountRequest): Promise<Account> {
        const response = await api.post<{ data: Account }>(ENDPOINT, data);
        return response.data.data;
    },

    /**
     * Restore a disabled account (re-enable it)
     */
    async restore(id: number): Promise<Account> {
        const response = await api.post<{ data: Account }>(`${ENDPOINT}/${id}/restore`);
        return response.data.data;
    },
};
