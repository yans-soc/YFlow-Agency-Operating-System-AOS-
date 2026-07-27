# YFlow — Phase 13: Engineering Governance

**Generated:** 2026-07-25  
**Version:** 1.0.0  
**Status:** Planning Complete  
**Owner:** Engineering Leadership

---

## Executive Summary

Engineering Governance establishes the standards, processes, and accountability mechanisms that ensure YFlow maintains high code quality, architectural consistency, and engineering excellence as the team and codebase scale.

This phase delivers three core artifacts:
1. **Engineering Handbook** — Single source of truth for all engineering practices
2. **Governance Report** — Current state assessment and compliance gaps
3. **Technical Standards** — Enforceable coding, architecture, and review standards

---

## 1. Current State Assessment

### 1.1 Existing Standards & Tools

| Area | Current State | Tooling | Coverage |
|------|---------------|---------|----------|
| Backend Coding Standards | ✅ Defined | Laravel Pint (PSR-12) | Partial enforcement |
| Frontend Coding Standards | ✅ Defined | ESLint + Prettier | Configured but not enforced in CI |
| TypeScript Configuration | ✅ Defined | `tsconfig.json` | Strict mode enabled |
| Git Workflow | ⚠️ Informal | Git | No branch protection rules documented |
| PR Process | ⚠️ Ad-hoc | GitHub | No template, no required reviewers |
| Architecture Documentation | ✅ Good | Markdown docs | 20+ documents, may drift from code |
| ADR Practice | ❌ None | - | No decision records |
| Code Review Culture | ️ Variable | GitHub Reviews | Inconsistent depth |
| Technical Debt Tracking | ✅ Started | Phase 12 register | Not integrated into workflow |
| AI Code Generation | ⚠️ Unregulated | Cline/Copilot | No governance |

### 1.2 Compliance Gaps

| Gap | Risk | Impact |
|-----|------|--------|
| No branch protection | High | Main branch can be corrupted |
| No PR template | Medium | Incomplete PRs, missed requirements |
| No ADR practice | Medium | Architecture decisions lost to time |
| No pre-commit hooks | Medium | Bad code reaches review |
| No CI gates for lint/test | High | Broken code can merge |
| No AI code review policy | Medium | AI-generated bugs slip through |
| No documentation ownership | Medium | Docs become stale |
| No debt triage process | Medium | Debt accumulates silently |

---

## 2. Engineering Handbook

### 2.1 Table of Contents

```
ENGINEERING_HANDBOOK.md
├── 1. Welcome & Onboarding
│   ├── 1.1 Getting Started
│   ├── 1.2 Development Environment Setup
│   ├── 1.3 First Contribution Guide
│   └── 1.4 Team Structure & Roles
│
├── 2. Coding Standards
│   ├── 2.1 PHP/Laravel Standards
│   │   ├── Naming Conventions
│   │   ├── File Organization
│   │   ├── Type Declarations
│   │   ├── Error Handling
│   │   └── Documentation (PHPDoc)
│   ├── 2.2 TypeScript/React Standards
│   │   ├── Naming Conventions
│   │   ├── Component Patterns
│   │   ├── Hook Guidelines
│   │   ├── State Management
│   │   ── JSDoc/TSDoc
│   ├── 2.3 SQL/Database Standards
│   │   ├── Migration Conventions
│   │   ├── Query Style Guide
│   │   └── Index Strategy
│   └── 2.4 Configuration & Secrets
│       ├── Environment Variables
│       └── Config File Structure
│
├── 3. Architecture Principles
│   ├── 3.1 Layered Architecture
│   ├── 3.2 Domain-Driven Design
│   ├── 3.3 SOLID Principles
│   ├── 3.4 Dependency Injection
│   └── 3.5 Event-Driven Patterns
│
├── 4. Version Control
│   ├── 4.1 Branch Strategy
│   ├── 4.2 Commit Message Format (Conventional Commits)
│   ├── 4.3 Pull Request Process
│   ── 4.4 Code Review Guidelines
│
├── 5. Testing Standards
│   ├── 5.1 Test Pyramid
│   ├── 5.2 Unit Testing
│   ├── 5.3 Feature/Integration Testing
│   ├── 5.4 E2E Testing
│   └── 5.5 Test Data Management
│
├── 6. Documentation Standards
│   ├── 6.1 Inline Code Comments
│   ├── 6.2 API Documentation
│   ├── 6.3 Architecture Documentation
│   └── 6.4 Runbooks & Playbooks
│
├── 7. Security Practices
│   ├── 7.1 Secure Coding Guidelines
│   ├── 7.2 Secret Management
│   ├── 7.3 Dependency Updates
│   └── 7.4 Vulnerability Reporting
│
├── 8. DevOps & Deployment
│   ├── 8.1 CI/CD Pipeline
│   ├── 8.2 Environment Strategy
│   ├── 8.3 Release Process
│   └── 8.4 Rollback Procedures
│
├── 9. Observability
│   ├── 9.1 Logging Standards
│   ├── 9.2 Metrics & Dashboards
│   └── 9.3 Alerting Guidelines
│
├── 10. AI-Assisted Development
│   ├── 10.1 Approved AI Tools
│   ├── 10.2 Code Review for AI-Generated Code
│   ├── 10.3 Prompt Engineering Guidelines
│   └── 10.4 AI Output Validation
│
└── Appendices
    ├── A. Glossary
    ├── B. Useful Commands
    ├── C. Common Patterns
    └── D. Anti-Patterns to Avoid
```

---

### 2.2 Key Standards Summary

#### 2.2.1 Branch Strategy (GitFlow Variant)

```
main          ──●──────────────────────●────  Production (protected)
                \                    /
develop       ───●────●────●────────●──────  Integration/Staging (protected)
                  \    \    \      /
feature/*     ────●────●────●────        Feature branches (from develop)
                                          Prefix: feature/, fix/, chore/, docs/
hotfix/*      ───────────────●────────    Hotfixes (from main)
                                          Prefix: hotfix/
release/*     ───────────────────●──────  Release prep (from develop)
                                          Prefix: release/vX.Y.Z
```

**Branch Protection Rules:**
- `main`: Require PR, 2 approvals, status checks pass, no force push
- `develop`: Require PR, 1 approval, status checks pass, no force push
- Feature branches: No protection, delete after merge

#### 2.2.2 Commit Message Format (Conventional Commits)

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Test additions/changes
- `chore`: Build/tooling changes

**Examples:**
```
feat(auth): add rate limiting to login endpoint

Implemented throttle middleware on auth routes.
Configurable via config/rate-limits.php.

Closes #123
```

```
fix(projects): resolve N+1 query in project listing

Added eager loading for tasks and assignees.
Reduced query count from 51 to 3.

Fixes #456
```

#### 2.2.3 Pull Request Requirements

**PR Template (`/.github/PULL_REQUEST_TEMPLATE.md`):**

```markdown
## Description
<!-- What does this PR do? Why is it needed? -->

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing Done
- [ ] Unit tests added/updated
- [ ] Feature tests added/updated
- [ ] Manual testing completed
- [ ] E2E tests passing

## Checklist
- [ ] Code follows project standards
- [ ] Self-review completed
- [ ] Documentation updated
- [ ] No new warnings/errors
- [ ] Database migrations tested (if applicable)

## Related Issues
Closes #___

## Screenshots (if UI changes)

## Deployment Notes
<!-- Any special deployment considerations? -->
```

**PR Size Guidelines:**
- Small: < 200 lines changed (preferred)
- Medium: 200-500 lines (acceptable)
- Large: > 500 lines (requires breakdown or additional reviewer)

**Required Approvals:**
- `main` branch: 2 approvals (including tech lead)
- `develop` branch: 1 approval

**Required Status Checks:**
- Backend: `composer test`, `composer pint -- --test`, `composer analyse`
- Frontend: `npm run typecheck`, `npm run lint`, `npm test`
- Build: Docker build succeeds

---

## 3. Architecture Decision Records (ADR)

### 3.1 ADR Template

**Location:** `docs/adr/NNNN-short-title.md`

```markdown
# ADR-NNNN: [Short Title]

**Date:** YYYY-MM-DD  
**Status:** Proposed | Accepted | Deprecated | Superseded  
**Context:** [Link to related issues/discussions]

## Context

[Describe the situation requiring a decision. Include relevant background, constraints, and stakeholders.]

## Decision

[State the decision clearly and unambiguously.]

## Rationale

[Explain why this decision was made. Include alternatives considered and reasons for rejection.]

## Consequences

### Positive
- [Benefit 1]
- [Benefit 2]

### Negative
- [Cost/trade-off 1]
- [Cost/trade-off 2]

### Neutral
- [Side effect 1]

## Implementation Notes

[How will this be implemented? Any migration needed?]

## Related Decisions

- [ADR-NNNN: Related decision]
- [ADR-NNNN: Another related decision]

## References

- [Link to RFC, meeting notes, etc.]
```

### 3.2 Initial ADR Backlog

| ADR # | Title | Priority | Status |
|-------|-------|----------|--------|
| ADR-0001 | Use Laravel Service Layer Pattern | High | Accepted |
| ADR-0002 | TanStack Query for Server State | High | Accepted |
| ADR-0003 | Zustand for Client State | Medium | Accepted |
| ADR-0004 | PostgreSQL as Primary Database | High | Accepted |
| ADR-0005 | Redis for Caching & Queues | Medium | Accepted |
| ADR-0006 | UUID Primary Keys | High | Accepted |
| ADR-0007 | API Versioning Strategy (/api/v1/) | High | Proposed |
| ADR-0008 | Feature Flags with Laravel Pennant | Medium | Proposed |
| ADR-0009 | OpenAPI/Swagger for API Docs | Medium | Proposed |
| ADR-0010 | Circuit Breaker Pattern for External Services | Low | Proposed |

---

## 4. Technical Debt Governance

### 4.1 Debt Classification

| Severity | Definition | SLA | Examples |
|----------|------------|-----|----------|
| Critical | Blocks releases, security risk | 1 week | Auth bypass, data corruption risk |
| High | Impacts reliability/performance | 2 weeks | N+1 queries, missing error handling |
| Medium | Increases maintenance burden | 1 month | Code duplication, missing tests |
| Low | Minor inconvenience | Backlog | Naming inconsistencies, TODO comments |

### 4.2 Debt Triage Process

**Weekly (Engineering Lead):**
- Review new debt items from PRs
- Assign severity and owner
- Add to sprint backlog if High/Critical

**Monthly (Team):**
- Review debt register
- Re-prioritize based on impact
- Celebrate debt paydown

**Quarterly (Leadership):**
- Debt budget allocation (e.g., 20% of capacity)
- Strategic debt initiatives
- Update governance if needed

### 4.3 Debt Registration Template

```markdown
## TD-XXXX: [Title]

**Severity:** Critical | High | Medium | Low  
**Created:** YYYY-MM-DD  
**Owner:** [Team/Person]  
**Status:** Identified | Triaged | Scheduled | In Progress | Resolved

### Location
[File(s), module, component]

### Symptoms
[What problems does this cause?]

### Root Cause
[Why does this exist?]

### Remediation
[What needs to be done?]

### Estimated Effort
[X days/story points]

### Dependencies
[Blocks/blocked by what?]

### Resolution Date
[When resolved]
```

---

## 5. AI Agent Governance

### 5.1 Approved AI Tools

| Tool | Purpose | Approval Status | Restrictions |
|------|---------|-----------------|--------------|
| Cline | Code generation, refactoring | ✅ Approved | Human review required |
| GitHub Copilot | Autocomplete | ✅ Approved | Verify suggestions |
| Cursor | IDE with AI | ️ Experimental | Not for production code yet |
| Custom LLM | Internal tools | ❌ Not approved | Security review pending |

### 5.2 AI Code Review Requirements

**All AI-generated code must:**
1. Be reviewed by a human engineer (not just merged)
2. Pass all existing tests
3. Include tests for new functionality
4. Have clear commit message noting AI assistance
5. Not introduce new dependencies without approval

**Commit message format for AI-assisted work:**
```
feat(tasks): add bulk assignment feature

[AI-assisted] Generated initial implementation with Cline.
Human review: Added validation, error handling, tests.

Closes #789
```

### 5.3 AI Output Validation Checklist

- [ ] Logic is correct and complete
- [ ] No security vulnerabilities introduced
- [ ] Follows project coding standards
- [ ] No hallucinated imports/functions
- [ ] Edge cases handled
- [ ] Tests included and passing
- [ ] Documentation updated

---

## 6. Engineering Metrics

### 6.1 DORA Metrics (DevOps Research & Assessment)

| Metric | Target | Measurement | Frequency |
|--------|--------|-------------|-----------|
| Deployment Frequency | Daily | Deploys to prod | Weekly report |
| Lead Time for Changes | < 1 day | Commit → Deploy | Weekly report |
| Change Failure Rate | < 10% | Failed deploys / total | Weekly report |
| Mean Time to Recovery | < 1 hour | Incident → Resolved | Per incident |

### 6.2 Code Quality Metrics

| Metric | Target | Tool | Frequency |
|--------|--------|------|-----------|
| Test Coverage (Backend) | > 80% | PHPUnit + Xdebug | CI gate |
| Test Coverage (Frontend) | > 70% | Vitest | CI gate |
| Static Analysis Errors | 0 | PHPStan, ESLint | CI gate |
| Tech Debt Ratio | < 5% | SonarQube (optional) | Monthly |
| PR Review Time | < 4 hours | GitHub API | Weekly |

### 6.3 Dashboard Requirements

**Engineering Dashboard should display:**
- Current sprint velocity
- Open vs closed PRs
- Test coverage trend
- Build success rate
- Incident count (MTTR)
- Tech debt items by severity

---

## 7. Documentation Governance

### 7.1 Documentation Ownership

| Document Type | Owner | Review Cadence | Source of Truth |
|---------------|-------|----------------|-----------------|
| API Spec | Backend Lead | Per release | Code annotations + Markdown |
| Architecture Docs | Tech Lead | Quarterly | `/docs/*.md` |
| Runbooks | DevOps Lead | Per incident | `/docs/runbooks/` |
| ADRs | Engineering Team | Per decision | `/docs/adr/` |
| Engineering Handbook | Eng Manager | Quarterly | This document |
| README | Repo owners | As needed | Root + subdirs |

### 7.2 Documentation Standards

**Markdown Conventions:**
- Use `.md` extension
- Title case for headers
- Tables for structured data
- Code blocks with language hint
- Relative links for internal refs
- Absolute URLs for external refs

**Documentation Review Checklist:**
- [ ] Spelling/grammar checked
- [ ] Links are valid
- [ ] Code examples are accurate
- [ ] Diagrams are up to date
- [ ] Owner assigned
- [ ] Review date set

### 7.3 Doc-as-Code Principle

- Documentation lives in same repo as code
- Docs changes require PR review
- CI validates link integrity (optional)
- Version docs with releases

---

## 8. Implementation Plan

### 8.1 Phase 13 Deliverables

| Deliverable | File Path | Owner | Est. Days |
|-------------|-----------|-------|-----------|
| Engineering Handbook | `docs/ENGINEERING_HANDBOOK.md` | Eng Manager | 3 |
| PR Template | `.github/PULL_REQUEST_TEMPLATE.md` | Tech Lead | 0.5 |
| Contributing Guide | `CONTRIBUTING.md` | Eng Manager | 1 |
| ADR Template + Index | `docs/adr/` | Tech Lead | 1 |
| Initial ADRs (10) | `docs/adr/0001-*.md` | Tech Lead | 2 |
| Branch Protection Setup | GitHub Settings | DevOps | 0.5 |
| CI Gates Configuration | `.github/workflows/` | DevOps | 1 |
| AI Governance Doc | `docs/AI_AGENT_GOVERNANCE.md` | Tech Lead | 1 |
| Debt Register Template | `docs/TECHNICAL_DEBT_REGISTER.md` | Eng Manager | 0.5 |

**Total Effort:** ~10 days

### 8.2 Rollout Sequence

**Week 1:**
- Day 1-2: Draft Engineering Handbook
- Day 3: Create PR template, Contributing guide
- Day 4: Set up ADR structure, write first 5 ADRs
- Day 5: Configure branch protection rules

**Week 2:**
- Day 1: Write remaining ADRs
- Day 2: Configure CI gates (lint, test, typecheck)
- Day 3: Draft AI Governance doc
- Day 4: Draft Technical Debt process
- Day 5: Team review, finalize all docs

**Week 3:**
- Team training session on new standards
- First PR using new process
- Retrospective and adjustments

---

## 9. Success Criteria

| Criterion | Measurement | Target |
|-----------|-------------|--------|
| Handbook adopted | % of engineers referencing | > 80% within 1 month |
| PR template used | % of PRs with template | 100% |
| ADR practice | # of ADRs created | 2/month minimum |
| CI gates passing | Build success rate | > 95% |
| Tech debt tracked | Items in register | 100% of known debt |
| AI governance followed | AI-assisted PRs reviewed | 100% |
| Branch protection | Protected branches | main, develop |

---

## 10. Risks & Mitigations

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Standards too burdensome | Medium | High | Start minimal, iterate based on feedback |
| Team resistance to process | Medium | Medium | Explain rationale, involve team in design |
| Docs become stale | High | Medium | Assign owners, set review cadence, automate where possible |
| CI gates slow development | Low | Medium | Optimize CI pipeline, parallelize jobs |
| AI governance too restrictive | Low | Low | Balance safety with productivity, allow exceptions with approval |

---

## Appendix A: Quick Reference

### Common Commands

```bash
# Backend
composer install
composer test
composer pint -- --test
composer analyse
php artisan migrate
php artisan db:seed

# Frontend
npm install
npm run dev
npm run build
npm run typecheck
npm run lint
npm test

# Git
git checkout -b feature/my-feature
git commit -m "feat(scope): description"
git push origin feature/my-feature
```

### PR Checklist (Quick)
- [ ] Tests passing
- [ ] Linting clean
- [ ] Types valid
- [ ] Docs updated
- [ ] Self-reviewed
- [ ] Linked issues

---

**Document Control**

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-07-25 | Engineering Team | Initial Engineering Governance plan |

**Approval Status:** Pending Review  
**Next Review Date:** Quarterly