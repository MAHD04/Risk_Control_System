import { render, screen } from '@testing-library/react';
import Sidebar from '@/components/layout/Sidebar';

describe('Sidebar Component', () => {
    it('renders the sidebar navigation', () => {
        render(<Sidebar />);

        // Check for the main navigation element (aside)
        expect(screen.getByRole('navigation', { name: /main navigation/i })).toBeInTheDocument();
    });

    it('renders the logo', () => {
        render(<Sidebar />);

        expect(screen.getByText('RiskControl')).toBeInTheDocument();
    });

    it('renders all navigation items', () => {
        render(<Sidebar />);

        expect(screen.getByText('Dashboard')).toBeInTheDocument();
        expect(screen.getByText('Risk Rules')).toBeInTheDocument();
        expect(screen.getByText('Incidents')).toBeInTheDocument();
        expect(screen.getByText('Trades')).toBeInTheDocument();
        expect(screen.getByText('Accounts')).toBeInTheDocument();
    });

    it('renders navigation links with correct hrefs', () => {
        render(<Sidebar />);

        const dashboardLink = screen.getByText('Dashboard').closest('a');
        const rulesLink = screen.getByText('Risk Rules').closest('a');
        const incidentsLink = screen.getByText('Incidents').closest('a');

        expect(dashboardLink).toHaveAttribute('href', '/');
        expect(rulesLink).toHaveAttribute('href', '/rules');
        expect(incidentsLink).toHaveAttribute('href', '/incidents');
    });

    it('renders the Navigation section label', () => {
        render(<Sidebar />);

        expect(screen.getByText(/navigation/i)).toBeInTheDocument();
    });
});
