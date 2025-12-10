import api from './api';
import type {
    RiskRule,
    ConfiguredAction,
    PaginatedResponse,
    CreateRiskRuleRequest,
    UpdateRiskRuleRequest,
    AttachActionsRequest,
} from '@/types';

const ENDPOINT = '/risk-rules';

export const riskRulesApi = {
    /**
     * Get all risk rules with optional pagination
     */
    async getAll(page?: number, perPage?: number): Promise<PaginatedResponse<RiskRule>> {
        const params = new URLSearchParams();
        if (page) params.append('page', page.toString());
        if (perPage) params.append('per_page', perPage.toString());

        const response = await api.get<{ success: boolean; data: RiskRule[] }>(
            `${ENDPOINT}${params.toString() ? `?${params}` : ''}`
        );

        // Wrap in PaginatedResponse format for compatibility
        const rules = response.data.data;
        return {
            data: rules,
            current_page: 1,
            last_page: 1,
            per_page: rules.length,
            total: rules.length,
            from: rules.length > 0 ? 1 : null,
            to: rules.length > 0 ? rules.length : null,
        };
    },

    /**
     * Get a single risk rule by ID
     */
    async getById(id: number): Promise<RiskRule> {
        const response = await api.get<{ data: RiskRule }>(`${ENDPOINT}/${id}`);
        return response.data.data;
    },

    /**
     * Create a new risk rule
     */
    async create(data: CreateRiskRuleRequest): Promise<RiskRule> {
        const response = await api.post<{ data: RiskRule }>(ENDPOINT, data);
        return response.data.data;
    },

    /**
     * Update an existing risk rule
     */
    async update(id: number, data: UpdateRiskRuleRequest): Promise<RiskRule> {
        const response = await api.put<{ data: RiskRule }>(`${ENDPOINT}/${id}`, data);
        return response.data.data;
    },

    /**
     * Delete a risk rule
     */
    async delete(id: number): Promise<void> {
        await api.delete(`${ENDPOINT}/${id}`);
    },

    /**
     * Attach actions to a risk rule
     */
    async attachActions(ruleId: number, data: AttachActionsRequest): Promise<RiskRule> {
        const response = await api.post<{ data: RiskRule }>(
            `${ENDPOINT}/${ruleId}/actions`,
            data
        );
        return response.data.data;
    },

    /**
     * Get all available configured actions
     */
    async listActions(): Promise<ConfiguredAction[]> {
        const response = await api.get<{ data: ConfiguredAction[] }>(`${ENDPOINT}/actions`);
        return response.data.data;
    },
};
