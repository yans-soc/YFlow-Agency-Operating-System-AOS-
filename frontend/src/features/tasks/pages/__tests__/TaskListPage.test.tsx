import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import '@testing-library/jest-dom';
import { TaskListPage } from '../TaskListPage';

const queryClient = new QueryClient({
  defaultOptions: { queries: { retry: false } },
});

vi.mock('@/hooks/useWorkspace', () => ({
  useWorkspace: () => ({ data: { id: 1 } }),
}));

interface UseTasksReturn {
  tasks: any[];
  isLoading: boolean;
  createTask: () => void;
  updateTask: () => void;
  deleteTask: () => void;
}

const useTasks = vi.fn<() => UseTasksReturn>();

vi.mock('../hooks/useTasks', () => ({
  useTasks: vi.fn(),
}));

const renderWithProviders = (component: React.ReactNode) => {
  return render(<QueryClientProvider client={queryClient}>{component}</QueryClientProvider>);
};

describe('TaskListPage', () => {
  beforeEach(() => {
    vi.mocked(useTasks).mockReturnValue({
      tasks: [],
      isLoading: false,
      createTask: vi.fn(),
      updateTask: vi.fn(),
      deleteTask: vi.fn(),
    });
  });

  it('renders task list page title', () => {
    renderWithProviders(<TaskListPage />);
    expect(screen.getByText('Tasks')).toBeDefined();
  });

  it('shows loading state when tasks are loading', () => {
    vi.mocked(useTasks).mockReturnValue({
      tasks: [],
      isLoading: true,
      createTask: vi.fn(),
      updateTask: vi.fn(),
      deleteTask: vi.fn(),
    });
    renderWithProviders(<TaskListPage />);
    expect(screen.getByText(/loading/i)).toBeDefined();
  });
});