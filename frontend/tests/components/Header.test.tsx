import { render, screen } from '@testing-library/react';
import Header from '@/components/layout/Header';

// Mock the notification API
jest.mock('@/services/notificationApi', () => ({
    notificationApi: {
        getUnread: jest.fn().mockResolvedValue({
            data: { count: 0, notifications: [] }
        }),
        markAsRead: jest.fn().mockResolvedValue({}),
        markAllAsRead: jest.fn().mockResolvedValue({}),
    }
}));

describe('Header Component', () => {
    it('renders the header container', () => {
        render(<Header />);

        // Check that the header is rendered
        expect(screen.getByRole('banner')).toBeInTheDocument();
    });

    it('renders search input', () => {
        render(<Header />);

        expect(screen.getByPlaceholderText(/search/i)).toBeInTheDocument();
    });

    it('renders user profile text', () => {
        render(<Header />);

        // Check for user-related elements (Admin is the default)
        expect(screen.getByText(/admin/i)).toBeInTheDocument();
    });

    it('renders multiple interactive buttons', () => {
        render(<Header />);

        const buttons = screen.getAllByRole('button');
        // Should have notification, help, and user menu buttons
        expect(buttons.length).toBeGreaterThanOrEqual(2);
    });
});
