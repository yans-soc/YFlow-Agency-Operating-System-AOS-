# CURSOR — YFlow Development Guide

## How Cursor IDE Should Work on YFlow

---

## Core Principles

1. **Read docs first** — Check `docs/` before any code change
2. **Follow AGENTS.md** — Global rules are mandatory
3. **One module per session** — Complete fully before moving on
4. **Tests required** — No code without tests
5. **Document everything** — Keep docs in sync

---

## Project Structure

```
YFlow/
├── docs/           # Single source of truth
├── backend/        # Laravel 11 + PHP 8.3
├── frontend/       # React 18 + TypeScript
├── .ai/            # AI memory
├── AGENTS.md       # Global rules
└── CURSOR.md       # This file
```

---

## Backend Standards (Laravel)

### File Locations
- Models: `app/Models/`
- Controllers: `app/Http/Controllers/Api/`
- Requests: `app/Http/Requests/`
- Resources: `app/Http/Resources/`
- Policies: `app/Policies/`
- Services: `app/Services/`
- Tests: `tests/Feature/` or `tests/Unit/`

### Required Patterns
- PSR-12 coding style
- Type hints on all methods
- PHPDoc for public methods
- Repository pattern for data access
- Service layer for business logic
- Form Requests for validation
- API Resources for responses
- Policies for authorization

---

## Frontend Standards (React/TypeScript)

### File Locations
- Features: `src/features/`
- Components: `src/components/`
- Services: `src/services/`
- Hooks: `src/hooks/`
- Stores: `src/stores/`

### Required Patterns
- Strict TypeScript
- Functional components with hooks
- TanStack Query for data fetching
- Zustand for global state
- Tailwind CSS for styling
- Shadcn/ui component library

---

## Git Workflow

### Branch Strategy
- `main` — Production
- `develop` — Integration
- `feature/*` — New features
- `release/*` — Release preparation
- `hotfix/*` — Critical fixes

### Commit Messages (Conventional Commits)
```
feat: add project workflow builder
fix: resolve task assignment bug
docs: update API specification
refactor: extract workflow service
test: add project CRUD tests
chore: update dependencies
```

---

## Testing Requirements

### Backend
- PHPUnit feature tests for API endpoints
- Unit tests for services
- Policy tests for authorization
- Minimum 80% coverage

### Frontend
- Vitest + React Testing Library
- Component rendering tests
- Hook tests
- User flow tests

---

## When to Stop

✅ Module complete (backend + frontend + tests + docs)
⏸️ Need clarification
⏸️ Tests failing
️ Breaking change required

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*