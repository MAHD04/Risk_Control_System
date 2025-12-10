import { riskRulesApi } from '@/services/riskRules';
import api from '@/services/api';

// Mock the base API module
jest.mock('@/services/api', () => ({
    __esModule: true,
    default: {
        get: jest.fn(),
        post: jest.fn(),
        put: jest.fn(),
        delete: jest.fn(),
    },
}));

const mockedApi = api as jest.Mocked<typeof api>;

describe('riskRulesApi Service', () => {
    beforeEach(() => {
        jest.clearAllMocks();
    });

    describe('getAll', () => {
        it('fetches all risk rules', async () => {
            const mockRules = [
                { id: 1, name: 'Test Rule', rule_type: 'min_duration', severity: 'HARD', is_active: true },
            ];
            mockedApi.get.mockResolvedValue({ data: { success: true, data: mockRules } });

            const result = await riskRulesApi.getAll();

            expect(mockedApi.get).toHaveBeenCalledWith('/risk-rules');
            expect(result.data).toEqual(mockRules);
        });
    });

    describe('getById', () => {
        it('fetches a single rule by id', async () => {
            const mockRule = {
                id: 1, name: 'Test', rule_type: 'min_duration',
                severity: 'SOFT', is_active: true,
            };
            mockedApi.get.mockResolvedValue({ data: { data: mockRule } });

            const result = await riskRulesApi.getById(1);

            expect(mockedApi.get).toHaveBeenCalledWith('/risk-rules/1');
            expect(result).toEqual(mockRule);
        });
    });

    describe('create', () => {
        it('creates a new risk rule', async () => {
            const newRule = {
                name: 'New Rule',
                rule_type: 'min_duration' as const,
                severity: 'HARD' as const,
                parameters: { min_duration_seconds: 30 },
                is_active: true,
            };
            mockedApi.post.mockResolvedValue({ data: { data: { id: 1, ...newRule } } });

            const result = await riskRulesApi.create(newRule);

            expect(mockedApi.post).toHaveBeenCalledWith('/risk-rules', newRule);
            expect(result.name).toBe('New Rule');
        });
    });

    describe('update', () => {
        it('updates an existing rule', async () => {
            const updated = { id: 1, name: 'Updated Name' };
            mockedApi.put.mockResolvedValue({ data: { data: updated } });

            const result = await riskRulesApi.update(1, { name: 'Updated Name' });

            expect(mockedApi.put).toHaveBeenCalledWith('/risk-rules/1', { name: 'Updated Name' });
            expect(result.name).toBe('Updated Name');
        });
    });

    describe('delete', () => {
        it('deletes a rule', async () => {
            mockedApi.delete.mockResolvedValue({ data: { success: true } });

            await riskRulesApi.delete(1);

            expect(mockedApi.delete).toHaveBeenCalledWith('/risk-rules/1');
        });
    });

    describe('listActions', () => {
        it('fetches available actions', async () => {
            const mockActions = [{ id: 1, name: 'Email', action_type: 'EMAIL' }];
            mockedApi.get.mockResolvedValue({ data: { data: mockActions } });

            const result = await riskRulesApi.listActions();

            expect(mockedApi.get).toHaveBeenCalledWith('/risk-rules/actions');
            expect(result).toEqual(mockActions);
        });
    });
});
