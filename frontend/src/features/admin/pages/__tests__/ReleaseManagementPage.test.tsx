import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ReleaseManagementPage } from '../ReleaseManagementPage';
import { releaseService } from '../../services/release';

vi.mock('../../services/release');

const queryClient = new QueryClient({
  defaultOptions: {
    queries: { retry: false },
  },
});

const renderWithProviders = (component: React.ReactNode) => {
  return render(
    <QueryClientProvider client={queryClient}>
      {component}
    </QueryClientProvider>
  );
};

describe('ReleaseManagementPage', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    queryClient.clear();
  });

  it('renders loading state', () => {
    vi.mocked(releaseService.list).mockReturnValue(new Promise(() => {}));
    
    renderWithProviders(<ReleaseManagementPage />);
    expect(screen.getByText(/loading releases/i)).toBeInTheDocument();
  });

  it('renders empty state when no releases', async () => {
    vi.mocked(releaseService.list).mockResolvedValue({ data: { data: [], meta: {} } });
    
    renderWithProviders(<ReleaseManagementPage />);
    
    await waitFor(() => {
      expect(screen.getByText(/no releases found/i)).toBeInTheDocument();
    });
  });

  it('renders releases list', async () => {
    const mockReleases = {
      data: [
        {
          id: 1,
          version: '1.0.0',
          formatted_version: 'v1.0.0',
          release_notes: 'Initial release',
          released_at: '2024-01-01',
          is_current: true,
          created_by: { id: 1, name: 'Admin' },
        },
      ],
      meta: {},
    };
    
    vi.mocked(releaseService.list).mockResolvedValue({ data: mockReleases });
    
    renderWithProviders(<ReleaseManagementPage />);
    
    await waitFor(() => {
      expect(screen.getByText('v1.0.0')).toBeInTheDocument();
      expect(screen.getByText('Current')).toBeInTheDocument();
    });
  });

  it('opens create modal when clicking New Release button', async () => {
    vi.mocked(releaseService.list).mockResolvedValue({ data: { data: [], meta: {} } });
    
    renderWithProviders(<ReleaseManagementPage />);
    
    await waitFor(() => {
      const newReleaseButton = screen.getByText(/new release/i);
      fireEvent.click(newReleaseButton);
    });
    
    expect(screen.getByText(/new release/i)).toBeInTheDocument();
  });

  it('submits new release form', async () => {
    vi.mocked(releaseService.list).mockResolvedValue({ data: { data: [], meta: {} } });
    vi.mocked(releaseService.create).mockResolvedValue({ data: {} });
    
    renderWithProviders(<ReleaseManagementPage />);
    
    await waitFor(() => {
      fireEvent.click(screen.getByText(/new release/i));
    });
    
    const versionInput = screen.getByPlaceholderText('1.0.0');
    fireEvent.change(versionInput, { target: { value: '1.0.0' } });
    
    const saveButton = screen.getByRole('button', { name: /save/i });
    fireEvent.click(saveButton);
    
    await waitFor(() => {
      expect(releaseService.create).toHaveBeenCalledWith(
        expect.objectContaining({ version: '1.0.0' })
      );
    });
  });

  it('validates version format', async () => {
    vi.mocked(releaseService.list).mockResolvedValue({ data: { data: [], meta: {} } });
    
    renderWithProviders(<ReleaseManagementPage />);
    
    await waitFor(() => {
      fireEvent.click(screen.getByText(/new release/i));
    });
    
    const versionInput = screen.getByPlaceholderText('1.0.0');
    fireEvent.change(versionInput, { target: { value: 'invalid' } });
    
    const saveButton = screen.getByRole('button', { name: /save/i });
    fireEvent.click(saveButton);
    
    await waitFor(() => {
      expect(screen.getByText(/format: x\.y\.z/i)).toBeInTheDocument();
    });
  });
});