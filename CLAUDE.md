# CLAUDE — YFlow Development Guide

## How Claude Should Work on YFlow

---

## Project Conventions

### Documentation First
1. Read `docs/01-vision.md` before any task
2. Check relevant numbered docs (01-20) for context
3. Review `AGENTS.md` for global rules
4. Update documentation BEFORE coding

### Code Organization
- Backend: Laravel 11+, PHP 8.3+
- Frontend: React 18+, TypeScript, Vite
- Tests: PHPUnit (backend), Vitest + RTL (frontend)

### Naming Conventions
- Models: PascalCase singular (`Project`, `WorkflowStage`)
- Controllers: PascalCase with `Controller` suffix
- Requests: PascalCase with `Request` suffix
- Resources: PascalCase with `Resource` suffix
- Policies: PascalCase with `Policy` suffix
- Tables: snake_case plural (`project_members`)
- Columns: snake_case singular

---

## Coding Standards

### PHP/Backend
```php
// Type hints required
public function store(StoreProjectRequest $request): ProjectResource

// PHPDoc for public methods
/**
 * Create a new project with workflow
 */
public function createWithWorkflow(array $data): Project

// Repository pattern for data access
// Service layer for business logic
// Form Requests for validation
// Resources for API responses
// Policies for authorization
```

### TypeScript/Frontend
```typescript
// Strict TypeScript
interface Project {
  id: string;
  name: string;
  // ...
}

// Functional components with hooks
const ProjectList = () => {
  const { data } = useQuery({ queryKey: ['projects'], queryFn: fetchProjects });
  // ...
};

// TanStack Query for data fetching
// Zustand for global state
// Tailwind CSS for styling
```

---

## Reasoning Policy

### Before Making Changes
1. **Check documentation** — Is there existing guidance?
2. **Review related code** — What patterns exist?
3. **Assess impact** — What breaks if I change this?
4. **Consider alternatives** — Are there better approaches?

### Decision Framework
| Situation | Action |
|-----------|--------|
| Clear docs exists | Follow documentation |
| Pattern established | Follow existing pattern |
| Multiple valid approaches | Ask for clarification |
| Breaking change required | Propose migration plan |
| Security implications | Flag and confirm |

---

## Stop Policy

### When to Stop and Report
✅ Module completed (backend + frontend + tests + docs)
✅ All tests passing
✅ Documentation updated
️ Blocked by missing information
⏸️ Unclear requirements
⏸️ Need architectural decision

### Never Continue Without Confirmation
- After completing assigned module
- When encountering ambiguity
- Before making breaking changes
- When tests fail unexpectedly

---

## Documentation Policy

### Required Updates
1. **Before coding**: Update relevant docs if requirements changed
2. **After coding**: Verify docs match implementation
3. **On bug fix**: Document root cause and solution
4. **On new feature**: Add to API spec, architecture docs

### Documentation Drift = Bug
If code doesn't match docs:
1. Fix code to match docs, OR
2. Update docs to match code
3. Never leave drift unaddressed

---

## Output Format

### Task Completion Report
```markdown
## Completed: [Module Name]

### Changes Made
- `path/to/file`: Brief description

### Tests Added
- `tests/Feature/...`: Test descriptions

### Documentation Updated
- `docs/XX-name.md`: Section updates

### Next Steps
- What needs attention next
```

### Clarification Request
```markdown
## Need Clarification

**Context**: What I'm trying to do
**Question**: Specific question
**Options**: 
- Option A: Description
- Option B: Description
```

---

## Memory & Context

### Always Remember
- YFlow is manual-first, AI-assisted
- Workflow is core, not tasks
- API freezes at v1.0
- One module per session
- Tests are mandatory

### Context Sources
- `docs/` — Single source of truth
- `.ai/` — AI memory and decisions
- `AGENTS.md` — Global rules
- Code comments — Implementation details

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*