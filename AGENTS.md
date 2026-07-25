# AI Agent Rules — YFlow

> **Read this file first before any AI agent begins work on YFlow.**

---

## Core Principles

### 1. Read Documentation First
- Always read `docs/01-vision.md` through `docs/20-release-management.md` before making changes
- Understand the domain model before modifying entities
- Check API specification before creating endpoints
- Review architecture documents before structural changes

### 2. Never Guess
- If uncertain about business logic, check `docs/06-business-rules.md`
- If uncertain about data model, check `docs/08-erd.md`
- If uncertain about API patterns, check `docs/09-api-specification.md`
- Ask for clarification rather than assuming

### 3. Backend Immutability After API Freeze
- Once API is frozen (v1.0+), breaking changes require:
  - New endpoint version (`/api/v2/...`)
  - Deprecation notice on old endpoint
  - Migration plan in documentation
- Database schema changes require migration files
- Never modify production data without rollback plan

### 4. One Module Per Session
- Complete one module fully before moving to next
- Each session must include:
  - Backend implementation
  - Frontend implementation (if applicable)
  - Tests
  - Documentation updates
- Do not leave partial implementations

### 5. Always Follow Business Rules
- Business rules in `docs/06-business-rules.md` are immutable
- Violations require explicit approval
- Document any exceptions

### 6. Update Documentation First
- Before coding, update relevant documentation
- After coding, verify documentation matches implementation
- Documentation drift is a bug

### 7. Never Skip Testing
- Every feature requires tests
- Backend: PHPUnit feature + unit tests
- Frontend: Vitest + React Testing Library
- Minimum 80% coverage for new code
- Tests must pass before marking complete

### 8. Stop After Module Completed
- When a module is complete, stop and report
- Wait for next instruction
- Do not start unrelated work

### 9. Wait for Next Instruction
- After completing assigned task, wait
- Do not assume next steps
- Confirm before proceeding

---

## File Structure Awareness

```
YFlow/
├── docs/                    # Single source of truth
│   ├── 01-vision.md
│   ├── 02-product-requirements.md
│   ├── ...
│   ├── phases/             # Phase documentation
│   ├── prompts/            # AI prompts
│   └── templates/          # Issue/PR templates
├── backend/
│   ├── app/
│   │   ├── Models/
│   │   ├── Http/
│   │   ├── Services/
│   │   └── ...
│   ├── database/
│   ├── tests/
│   └── routes/
├── frontend/
│   ├── src/
│   │   ├── features/
│   │   ├── components/
│   │   ├── services/
│   │   └── ...
│   ── tests/
├── .ai/                     # AI memory
└── AGENTS.md               # This file
```

---

## Coding Standards

### Backend (Laravel/PHP)
- PSR-12 coding style
- Type hints required
- PHPDoc for public methods
- Repository pattern for data access
- Service layer for business logic
- Form Requests for validation
- Resources for API responses
- Policies for authorization

### Frontend (React/TypeScript)
- Strict TypeScript
- Functional components with hooks
- TanStack Query for data fetching
- Zustand for global state
- Tailwind CSS for styling
- Shadcn/ui component library
- ESLint + Prettier enforced

### Git
- Conventional Commits
- Small, focused commits
- Descriptive commit messages
- Link issues in commits

---

## Decision Making

### When to Make Decisions Independently
✅ Clear documentation exists
✅ Pattern already established in codebase
✅ Low-risk cosmetic changes
✅ Bug fixes with obvious cause

### When to Ask for Clarification
❓ No documentation exists
 Multiple valid approaches exist
❓ Breaking changes required
❓ Security implications
❓ Performance-critical code
❓ Database schema changes

---

## Output Format

When reporting completion:

```markdown
## Completed: [Module Name]

### Changes Made
- File 1: Description
- File 2: Description

### Tests Added
- Test file descriptions

### Documentation Updated
- Doc file descriptions

### Next Steps Required
- What needs to be done next
```

---

## Emergency Procedures

### If Production Issue Detected
1. Stop all development
2. Check `docs/phases/` for incident response
3. Create hotfix branch
4. Minimal change to fix issue
5. Test thoroughly
6. Deploy immediately

### If Data Corruption Detected
1. Stop all write operations
2. Notify immediately
3. Check backup status
4. Prepare rollback plan

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*
*Status: Active*