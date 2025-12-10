import { incidentsApi } from '@/services/incidents';
import api from '@/services/api';

// Mock the base API module
jest.mock('@/services/api', () => ({
    __esModule: true,
    default: {
        get: jest.fn(),
        post: jest.fn(),
    },
}));

const mockedApi = api as jest.Mocked<typeof api>;

describe('incidentsApi Service', () => {
    beforeEach(() => {
        jest.clearAllMocks();
    });

    describe('getAll', () => {
        it('fetches paginated incidents', async () => {
            const mockResponse = {
                data: [{ id: 1, account_id: 1, risk_rule_id: 1 }],
                current_page: 1,
                total: 10,
            };
            mockedApi.get.mockResolvedValue({ data: mockResponse });

            const result = await incidentsApi.getAll({ page: 1, per_page: 10 });

            expect(mockedApi.get).toHaveBeenCalled();
            expect(result.data).toHaveLength(1);
        });

        it('filters by account_id', async () => {
            mockedApi.get.mockResolvedValue({ data: { data: [] } });

            await incidentsApi.getAll({ account_id: 5 });

            expect(mockedApi.get).toHaveBeenCalled();
        });
    });

    describe('getById', () => {
        it('fetches single incident', async () => {
            const mockIncident = { id: 1, message: 'Test incident' };
            mockedApi.get.mockResolvedValue({ data: { data: mockIncident } });

            const result = await incidentsApi.getById(1);

            expect(mockedApi.get).toHaveBeenCalledWith('/incidents/1');
            expect(result.id).toBe(1);
        });
    });

    describe('getAccountStats', () => {
        it('fetches account incident stats', async () => {
            const mockStats = { total_incidents: 5, incidents_by_rule: [] };
            mockedApi.get.mockResolvedValue({ data: { data: mockStats } });

            const result = await incidentsApi.getAccountStats(1);

            expect(mockedApi.get).toHaveBeenCalledWith('/incidents/account/1/stats');
            expect(result.total_incidents).toBe(5);
        });
    });
});
