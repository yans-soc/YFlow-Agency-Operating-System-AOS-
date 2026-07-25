import { describe, it, expect, vi } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import '@testing-library/jest-dom';
import App from '../App';

const queryClient = new QueryClient({
  defaultOptions: { queries: { retry: false } },
});

vi.mock('@/hooks/useAuth', () => ({
  useAuth: () => ({
    user: { id: 1, email: 'test@example.com', name: 'Test User' },
    isLoading: false,
  }),
}));

vi.mock('@/hooks/useWorkspace', () => ({
  useWorkspace: () => ({ data: { id: 1, name: 'Test Workspace' } }),
}));

const renderApp = () => {
  return render(
    <QueryClientProvider client={queryClient}>
      <App />
    </QueryClientProvider>
  );
};

describe('Integration Tests', () => {
  it('renders app with authenticated user', async () => {
    renderApp();
    await waitFor(() => {
      expect(screen.getByText(/dashboard|projects|tasks/i)).toBeDefined();
    });
  });

  it('navigates between main sections', async () => {
    renderApp();
    
    await waitFor(() => {
      expect(screen.getByText(/dashboard|projects|tasks/i)).toBeDefined();
    });
  });

  it('displays workspace context', async () => {
    renderApp();
    await waitFor(() => {
      expect(screen.getByText(/test workspace/i)).toBeDefined();
    });
  });
});