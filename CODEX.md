# CODEX CLI — YFlow Development Guide

## How Codex Should Work on YFlow

---

## Core Principles

1. **Read docs first** — Always check `docs/` before coding
2. **Follow AGENTS.md** — Global rules are immutable
3. **One module per session** — Complete fully before moving on
4. **Tests required** — No code without tests
5. **Document everything** — Docs must match implementation

---

## Project Structure

```
YFlow/
├── docs/           # Read first
├── backend/        # Laravel + PHP
── frontend/       # React + TypeScript
├── .ai/            # Context memory
└── AGENTS.md       # Global rules
```

---

## Backend Patterns (Laravel)

### Standard File Locations
- Models: `app/Models/`
- Controllers: `app/Http/Controllers/Api/`
- Requests: `app/Http/Requests/`
- Resources: `app/Http/Resources/`
- Policies: `app/Policies/`
- Services: `app/Services/`
- Tests: `tests/Feature/` or `tests/Unit/`

### Required Patterns
```php
// Controller
class ProjectController extends Controller {
    public function index(ProjectService $service) {
        return ProjectResource::collection($service->all());
    }
}

// Request Validation
class StoreProjectRequest extends FormRequest {
    public function rules(): array {
        return ['name' => ['required', 'string', 'max:255']];
    }
}

// Policy Authorization
public function update(UpdateProjectRequest $request, Project $project) {
    $this->authorize('update', $project);
    // ...
}
```

---

## Frontend Patterns (React/TypeScript)

### Standard File Locations
- Features: `src/features/`
- Components: `src/components/`
- Services: `src/services/`
- Hooks: `src/hooks/`
- Stores: `src/stores/`
- Types: `src/types/` or inline

### Required Patterns
```typescript
// TanStack Query for data
const { data } = useQuery({
  queryKey: ['projects'],
  queryFn: () => api.get('/projects')
});

// Zustand for global state
const useProjectStore = create((set) => ({
  projects: [],
  setProjects: (projects) => set({ projects })
}));

// Component structure
export const ProjectList = () => {
  // hooks
  // handlers
  // render
};
```

---

## Git Conventions

### Commit Messages (Conventional Commits)
```
feat: add project workflow builder
fix: resolve task assignment bug
docs: update API specification
refactor: extract workflow service
test: add project CRUD tests
chore: update dependencies
```

### Branch Naming
```
feature/project-workflow
fix/task-assignment
docs/api-update
release/v1.0.0
hotfix/critical-bug
```

---

## Testing Requirements

### Backend (PHPUnit)
- Feature tests for API endpoints
- Unit tests for services
- Policy tests for authorization
- Minimum 80% coverage

### Frontend (Vitest + RTL)
- Component rendering tests
- Hook tests
- Integration tests
- User flow tests

---

## When to Stop

✅ Module complete (backend + frontend + tests + docs)
️ Need clarification on requirements
⏸️ Tests failing unexpectedly
⏸️ Breaking change required

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*