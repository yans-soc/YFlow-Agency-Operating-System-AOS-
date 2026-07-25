# Frontend Development Prompt — YFlow

## Purpose
Guide AI agents when working on frontend (React/TypeScript) development tasks.

---

## Pre-Development Checklist

1. **Read Documentation**
   - `docs/01-vision.md` — Product vision
   - `docs/11-frontend-architecture.md` — Architecture guidelines
   - `docs/12-design-system.md` — UI components & patterns

2. **Check Existing Code**
   - Review similar features/components
   - Follow established patterns
   - Check naming conventions

3. **Plan Implementation**
   - Identify required components
   - Define data fetching needs
   - Plan state management
   - List required tests

---

## Development Workflow

### 1. Feature Structure
```
src/features/
── projects/
    ├── ProjectListPage.tsx
    ├── ProjectDetailPage.tsx
    ├── ProjectCard.tsx
    ├── ProjectModal.tsx
    └── hooks/
        └── useProjects.ts
```

### 2. Component Patterns
```typescript
// Page component
export const ProjectListPage = () => {
  const { data: projects } = useQuery({
    queryKey: ['projects'],
    queryFn: fetchProjects
  });
  
  return <div>{/* render */}</div>;
};

// Presentational component
interface ProjectCardProps {
  project: Project;
  onClick: (id: string) => void;
}

export const ProjectCard = ({ project, onClick }: ProjectCardProps) => {
  return <Card onClick={() => onClick(project.id)}>{/* ... */}</Card>;
};
```

### 3. Data Fetching
```typescript
// Service layer
export const fetchProjects = async (): Promise<Project[]> => {
  const response = await api.get('/projects');
  return response.data.data;
};

// Custom hook
export const useProjects = () => {
  return useQuery({
    queryKey: ['projects'],
    queryFn: fetchProjects
  });
};
```

### 4. State Management
```typescript
// Zustand store
interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  login: (user: User) => void;
  logout: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  isAuthenticated: false,
  login: (user) => set({ user, isAuthenticated: true }),
  logout: () => set({ user: null, isAuthenticated: false })
}));
```

---

## Code Standards

### TypeScript
- Strict mode enabled
- No `any` types
- Interfaces for all props/data
- Proper error handling

### Components
- Functional components only
- Hooks for state/logic
- Tailwind CSS for styling
- Shadcn/ui for base components

### File Naming
- PascalCase for components
- camelCase for utilities/hooks
- Descriptive names

---

## Testing Requirements

```typescript
import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';

describe('ProjectCard', () => {
  it('renders project name', () => {
    render(<ProjectCard project={mockProject} onClick={vi.fn()} />);
    expect(screen.getByText(mockProject.name)).toBeInTheDocument();
  });
});
```

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*