import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import '@testing-library/jest-dom';
import { WorkflowListPage } from '../WorkflowListPage';

interface UseWorkflowsReturn {
  workflows: any[];
  isLoading: boolean;
  createWorkflow: () => void;
  deleteWorkflow: () => void;
}

const useWorkflows = vi.fn<() => UseWorkflowsReturn>();

const queryClient = new QueryClient({
  defaultOptions: { queries: { retry: false } },
});

vi.mock('@/hooks/useWorkspace', () => ({
  useWorkspace: () => ({ data: { id: 1 } }),
}));

vi.mock('../hooks/useWorkflows', () => ({
  useWorkflows: vi.fn(),
}));

const renderWithProviders = (component: React.ReactNode) => {
  return render(<QueryClientProvider client={queryClient}>{component}</QueryClientProvider>);
};

describe('WorkflowListPage', () => {
  beforeEach(() => {
    vi.mocked(useWorkflows).mockReturnValue({
      workflows: [],
      isLoading: false,
      createWorkflow: vi.fn(),
      deleteWorkflow: vi.fn(),
    });
  });

  it('renders workflow list page title', () => {
    renderWithProviders(<WorkflowListPage />);
    expect(screen.getByText('Workflows')).toBeDefined();
  });

  it('shows loading state', () => {
    vi.mocked(useWorkflows).mockReturnValue({
      workflows: [],
      isLoading: true,
      createWorkflow: vi.fn(),
      deleteWorkflow: vi.fn(),
    });
    renderWithProviders(<WorkflowListPage />);
    expect(screen.getByText(/loading/i)).toBeDefined();
  });

  it('opens create form when New Workflow button clicked', async () => {
    renderWithProviders(<WorkflowListPage />);
    const newButton = screen.getByText('New Workflow');
    await fireEvent.click(newButton);
    expect(screen.getByPlaceholderText(/workflow name/i)).toBeDefined();
  });
});
