# Git Workflow — YFlow

## Branch Strategy
- `main` - Production (protected)
- `develop` - Integration (protected)
- `feature/*` - New features
- `bugfix/*` - Bug fixes
- `hotfix/*` - Critical production fixes
- `release/*` - Release preparation

## Commit Convention
```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types
- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation
- `style` - Formatting
- `refactor` - Code refactoring
- `test` - Tests
- `chore` - Maintenance

### Examples
```bash
git commit -m "feat(auth): add OAuth2 login support"
git commit -m "fix(tasks): resolve assignee duplication bug"
git commit -m "docs(api): update endpoint documentation"
```

## Pull Request Process
1. Create feature branch from `develop`
2. Make commits with conventional format
3. Push and create PR to `develop`
4. CI must pass (lint, test, analyze)
5. Peer review approval required
6. Squash merge to `develop`

## Hotfix Process
1. Create hotfix branch from `main`
2. Fix issue with minimal changes
3. PR to both `main` and `develop`
4. Deploy immediately after merge

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*