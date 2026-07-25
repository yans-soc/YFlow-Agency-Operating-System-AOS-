# GEMINI — YFlow Development Guide

## How Gemini Should Work on YFlow

---

## Core Rules

1. **Read `docs/` first** — Never code without checking documentation
2. **Follow `AGENTS.md`** — Global rules are mandatory
3. **One module per session** — Complete backend + frontend + tests + docs
4. **Tests required** — Minimum 80% coverage
5. **Stop when done** — Wait for next instruction

---

## Documentation Priority

| Order | Document | When to Read |
|-------|----------|--------------|
| 1 | `docs/01-vision.md` | Always first |
| 2 | `docs/06-business-rules.md` | Before logic changes |
| 3 | `docs/08-erd.md` | Before data changes |
| 4 | `docs/09-api-specification.md` | Before API changes |
| 5 | Relevant numbered doc | Task-specific |

---

## Code Standards

### Backend (Laravel/PHP)
- PSR-12 style
- Type hints required
- Repository pattern
- Service layer
- Form Requests
- API Resources
- Policies

### Frontend (React/TypeScript)
- Strict TypeScript
- Functional components
- TanStack Query
- Zustand state
- Tailwind CSS
- Shadcn/ui

---

## Git Conventions

**Commits:** Conventional Commits
```
feat: add feature
fix: fix bug
docs: update docs
refactor: refactor code
test: add tests
chore: maintenance
```

**Branches:**
```
feature/name
fix/name
release/vX.Y.Z
hotfix/name
```

---

## When to Stop

✅ Module complete
⏸️ Need clarification
️ Tests failing
️ Breaking change needed

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*