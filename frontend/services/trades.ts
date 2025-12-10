import api from './api';
import type {
    Trade,
    PaginatedResponse,
    CreateTradeRequest,
    UpdateTradeRequest,
} from '@/types';

const ENDPOINT = '/trades';

export interface TradeFilters {
    account_id?: number;
    status?: 'OPEN' | 'CLOSED';
    page?: number;
    per_page?: number;
}

export const tradesApi = {
    /**
     * Get all trades with optional filters and pagination
     */
    async getAll(filters?: TradeFilters): Promise<PaginatedResponse<Trade>> {
        const params = new URLSearchParams();

        if (filters?.account_id) params.append('account_id', filters.account_id.toString());
        if (filters?.status) params.append('status', filters.status);
        if (filters?.page) params.append('page', filters.page.toString());
        if (filters?.per_page) params.append('per_page', filters.per_page.toString());

        const response = await api.get<PaginatedResponse<Trade>>(
            `${ENDPOINT}${params.toString() ? `?${params}` : ''}`
        );
        return response.data;
    },

    /**
     * Get a single trade by ID
     */
    async getById(id: number): Promise<Trade> {
        const response = await api.get<{ data: Trade }>(`${ENDPOINT}/${id}`);
        return response.data.data;
    },

    /**
     * Create a new trade (triggers risk evaluation)
     */
    async create(data: CreateTradeRequest): Promise<Trade> {
        const response = await api.post<{ data: Trade }>(ENDPOINT, data);
        return response.data.data;
    },

    /**
     * Update a trade (e.g., close it)
     */
    async update(id: number, data: UpdateTradeRequest): Promise<Trade> {
        const response = await api.put<{ data: Trade }>(`${ENDPOINT}/${id}`, data);
        return response.data.data;
    },
};
