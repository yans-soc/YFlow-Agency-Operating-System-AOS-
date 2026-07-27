# YFlow — API Freeze SOP & Governance Protocol

**Version:** 1.0.0  
**Status:** Active  
**Last Updated:** 2026-07-25  
**Owner:** Lead Backend QA Engineer  
**Applicability:** All Backend Developers, QA Engineers, DevOps

---

## 1. Purpose & Scope

### 1.1 Purpose
This Standard Operating Procedure (SOP) defines the rules, processes, and enforcement mechanisms for the **API Freeze** state in YFlow development lifecycle.

### 1.2 Why API Freeze?
After achieving v1.0.0 readiness, the API must remain stable to ensure:
- Frontend-backend contract integrity
- Third-party integration reliability
- Predictable release cycles
- Reduced regression risk

### 1.3 Scope
This policy applies to:
- All REST API endpoints under `/api/v1/*`
- Request schemas (headers, query params, body)
- Response schemas (status codes, structure, data types)
- Authentication & authorization behavior
- Error response formats

### 1.4 Effective Date
API Freeze becomes effective **immediately after**:
1. All QA gates passed (Contract, Security, Load, Performance)
2. Lead QA sign-off obtained
3. Release candidate tag created (`v1.0.0-rc1`)

---

## 2. API Freeze Triggers

### 2.1 Automatic Triggers
| Trigger | Action |
|---------|--------|
| CI pipeline passes all QA gates | Freeze status = ACTIVE |
| Git tag `v*.*.*-rc*` created | Freeze status = ACTIVE |
| Release marked as "Release Candidate" in system | Freeze status = ACTIVE |

### 2.2 Manual Triggers
| Trigger | Required Approval |
|---------|-------------------|
| CTO declaration | CTO |
| Lead QA declaration | Lead QA Engineer |
| Emergency security patch | CTO + Lead QA |

### 2.3 Freeze Status Communication
When freeze activates:
1. Slack announcement in #engineering
2. GitHub repository status updated
3. PR template updated with freeze notice
4. Changelog entry created

---

## 3. Zero-Tolerance Policy: Prohibited Changes

### 3.1 🚫 Response JSON Changes (STRICTLY PROHIBITED)

**What is prohibited:**
- Adding new fields to existing responses
- Removing existing fields from responses
- Changing field names (camelCase, snake_case, etc.)
- Changing data types (string → number, array → object, etc.)
- Changing nullability (nullable → required, required → nullable)
- Changing enum values or adding new enum options
- Changing date/time formats
- Changing nested object structures

**Example Violation:**
```json
// BEFORE (v1.0.0)
{
  "data": {
    "id": 1,
    "name": "Project Alpha",
    "status": "active",
    "created_at": "2026-01-01T00:00:00Z"
  }
}

// AFTER - VIOLATION ❌
{
  "data": {
    "id": 1,
    "name": "Project Alpha",
    "status": 1,              // Changed: string → number
    "created_at": "01/01/2026", // Changed: ISO8601 → custom format
    "description": null        // Added: new field
  }
}
```

**Allowed:**
- Adding new **optional** nested objects at root level (with deprecation notice)
- Internal refactoring that produces identical output

### 3.2 🚫 Endpoint Changes (STRICTLY PROHIBITED)

**What is prohibited:**
- Changing endpoint paths (`/api/v1/projects` → `/api/v1/project`)
- Changing HTTP methods (GET → POST, PUT → PATCH)
- Removing endpoints entirely
- Adding path parameters
- Changing route parameter names
- Adding new endpoints that conflict with existing patterns

**Example Violation:**
```php
// BEFORE
Route::get('/api/v1/projects', [ProjectController::class, 'index']);

// AFTER - VIOLATION ❌
Route::post('/api/v1/projects', [ProjectController::class, 'index']); // Method changed
Route::get('/api/v1/project', [ProjectController::class, 'index']);   // Path changed
```

**Allowed:**
- Adding new endpoints under new paths (non-conflicting)
- Adding new optional query parameters

### 3.3  Request Changes (STRICTLY PROHIBITED)

**What is prohibited:**
- Adding required headers
- Removing existing headers
- Changing header names or expected values
- Adding required query parameters
- Removing existing query parameters
- Changing request body schema (adding/removing required fields)
- Changing validation rules (stricter validation)
- Changing authentication requirements

**Example Violation:**
```php
// BEFORE
$request->validate([
    'name' => 'required|string|max:255',
    'description' => 'nullable|string',
]);

// AFTER - VIOLATION ❌
$request->validate([
    'name' => 'required|string|max:100',   // Stricter: max 255 → 100
    'description' => 'required|string',     // Changed: nullable → required
    'priority' => 'required|integer',       // Added: new required field
]);
```

**Allowed:**
- Relaxing validation rules (less strict)
- Adding optional query parameters
- Adding optional request body fields

### 3.4 Summary Table

| Change Type | Allowed During Freeze | Requires Exception |
|-------------|----------------------|--------------------|
| Add response field | ⚠️ Optional only | Yes (if breaking) |
| Remove response field |  Never | Critical bug only |
| Change response type | ❌ Never | Critical bug only |
| Change endpoint path | ❌ Never | Critical bug only |
| Change HTTP method | ❌ Never | Critical bug only |
| Add required header | ❌ Never | Critical bug only |
| Add required param | ❌ Never | Critical bug only |
| Stricter validation | ❌ Never | Critical bug only |
| Relax validation | ✅ Yes | No |
| Bug fix (non-breaking) | ✅ Yes | No |
| Security patch | ️ With approval | Yes |

---

## 4. Exception Process

### 4.1 When Exceptions Are Allowed

Exceptions to the API Freeze policy are **ONLY** permitted for:

| Category | Definition | Examples |
|----------|------------|----------|
| **Critical Security** | Vulnerabilities that could lead to data breach, unauthorized access, or system compromise | SQL injection, authentication bypass, IDOR, XSS |
| **System Crash** | Bugs that cause server errors (5xx), application crashes, or complete service unavailability | Null pointer exceptions, database connection failures, memory exhaustion |
| **Blocker** | Bugs that prevent core functionality from working as documented | Payment processing failure, data loss, workflow stuck |

### 4.2 Exception Request Process

```
┌─────────────────────────────────────────────────────────────────
│                    EXCEPTION REQUEST WORKFLOW                   │
└─────────────────────────────────────────────────────────────────┘

1. Developer identifies critical issue
   ↓
2. Create Exception Request (see template below)
   ↓
3. Submit to Lead QA for review
   ↓
4. Lead QA evaluates:
   - Is it truly critical?
   - Is there a non-breaking workaround?
   - What's the impact of the change?
   ↓
5. Decision:
   ├─ APPROVED → Proceed with hotfix branch
   └─ REJECTED → Find alternative solution
   ↓
6. If approved:
   - Create hotfix branch
   - Implement minimal fix
   - Document change thoroughly
   - Update changelog
   - Create rollback plan
   ↓
7. Code review (Lead QA + Senior Dev required)
   ↓
8. Deploy to staging, verify fix
   ↓
9. Deploy to production (emergency deployment)
   ↓
10. Post-deployment monitoring (24 hours)
   ↓
11. Retrospective within 48 hours
```

### 4.3 Exception Request Template

```markdown
# API Freeze Exception Request

**Request ID:** EX-YYYY-NNN  
**Date:** YYYY-MM-DD  
**Requested By:** @username  
**Module:** [Module name]

## Issue Description
[Describe the critical bug in detail]

## Impact Assessment
- **Severity:** Critical / High / Medium / Low
- **Affected Users:** [Number/percentage]
- **Business Impact:** [Revenue, reputation, compliance]

## Proposed Change
[Describe the exact API change needed]

### Current Behavior
```json
// Show current response/request
```

### Proposed Behavior
```json
// Show proposed response/request
```

## Why This Can't Wait
[Explain why this requires immediate action vs. waiting for next major version]

## Alternative Solutions Considered
1. [Alternative 1] - Why rejected
2. [Alternative 2] - Why rejected

## Rollback Plan
[How to revert if something goes wrong]

## Testing Evidence
- [ ] Unit tests added/updated
- [ ] Integration tests passed
- [ ] Staging verification completed
- [ ] No regression in other endpoints

## Approvals
- [ ] Lead QA: @username (Approved/Rejected)
- [ ] CTO (if security-related): @username (Approved/Rejected)

---
**Decision:** APPROVED / REJECTED  
**Decision Date:** YYYY-MM-DD  
**Decision Notes:** [Notes from reviewer]
```

### 4.4 Emergency Hotfix Branch Strategy

```bash
# Create hotfix branch from production tag
git checkout v1.0.0
git checkout -b hotfix/EX-2026-001-auth-bypass

# Make minimal changes ONLY
# Commit with clear message
git commit -m "fix(auth): critical auth bypass vulnerability (EX-2026-001)"

# Push and create PR
git push origin hotfix/EX-2026-001-auth-bypass

# PR must reference exception request
# PR must have Lead QA approval
# PR must have rollback plan attached
```

---

## 5. Enforcement Mechanisms

### 5.1 CI/CD Pipeline Checks

The following automated checks will block merges during API Freeze:

| Check | Tool | Action on Failure |
|-------|------|-------------------|
| Schema Diff | Custom script | Block merge |
| OpenAPI Spec Validation | Spectral | Block merge |
| Breaking Change Detection | api-diff, openapi-diff | Block merge |
| Test Coverage | PHPUnit/Pest | Block if <80% |
| Security Scan | OWASP ZAP, Composer Audit | Block on Critical/High |

### 5.2 GitHub Configuration

#### Branch Protection Rules (main branch)
```yaml
branch_protection:
  require_pull_request_reviews: true
  required_approving_review_count: 2
  require_code_owner_reviews: true
  required_status_checks:
    - "API Contract Tests"
    - "Security Scan"
    - "Breaking Change Detection"
  enforce_admins: true
  restrictions:
    users: []
    teams: ["backend-leads", "qa-leads"]
```

#### PR Template (Updated for Freeze)
```markdown
## API Freeze Compliance

- [ ] This PR does NOT modify any API response structure
- [ ] This PR does NOT modify any API endpoint path or method
- [ ] This PR does NOT modify any API request schema
- [ ] OR: This is an approved exception (link: #EX-YYYY-NNN)

## Change Type

- [ ] Bug fix (non-breaking)
- [ ] Security patch (approved exception)
- [ ] Documentation only
- [ ] Infrastructure only

## Testing

- [ ] All existing tests pass
- [ ] New tests added for changes
- [ ] No regression in related endpoints

## Exception Request (if applicable)

Exception Request ID: #EX-YYYY-NNN  
Lead QA Approval: @username  
CTO Approval (if security): @username
```

### 5.3 Code Review Checklist

Reviewers must verify:

- [ ] No changes to API Resource classes (unless approved exception)
- [ ] No changes to route definitions (unless approved exception)
- [ ] No changes to Form Request validation (unless relaxing rules)
- [ ] No changes to response structure in controllers
- [ ] Exception request linked and approved (if applicable)
- [ ] Rollback plan documented (if applicable)
- [ ] Changelog updated

### 5.4 Automated Breaking Change Detection

Sample GitHub Action workflow snippet:

```yaml
name: API Breaking Change Detection

on:
  pull_request:
    branches: [main, develop]

jobs:
  detect-breaking-changes:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Generate current OpenAPI spec
        run: php artisan l5-swagger:generate
        
      - name: Download baseline spec
        run: curl -o baseline.json https://staging.yflow.com/api/docs/json
        
      - name: Run breaking change detection
        uses: openapi-diff/action@v1
        with:
          base: baseline.json
          head: storage/api-docs/openapi.json
          fail_on_breaking: true
          
      - name: Comment on PR if breaking changes detected
        if: failure()
        uses: actions/github-script@v6
        with:
          script: |
            github.rest.issues.createComment({
              issue_number: context.issue.number,
              owner: context.repo.owner,
              repo: context.repo.repo,
              body: '️ Breaking API changes detected! This PR cannot be merged during API Freeze. Please submit an exception request or revise your changes.'
            })
```

---

## 6. Communication Protocol

### 6.1 Freeze Activation Announcement

Template for Slack/Teams:

```
 API FREEZE ACTIVATED 🚨

Effective immediately, YFlow API v1.0.0 is now FROZEN.

📋 What this means:
• No changes to API response structures
• No changes to endpoint paths or methods  
• No changes to request schemas
• Only critical bug fixes allowed (with approval)

✅ What you CAN do:
• Bug fixes that don't change API contracts
• Performance optimizations
• Internal refactoring
• Documentation updates

 What you CANNOT do:
• Add/remove/change API fields
• Modify endpoints
• Change validation rules (making stricter)

 Exception process:
1. Create Exception Request (template: docs/templates/exception-request.md)
2. Get Lead QA approval
3. Follow hotfix procedure

Questions? Ask in #api-freeze-questions

Reference: docs/24-api-freeze-sop.md
```

### 6.2 Change Notification

For approved exceptions:

```
📢 API CHANGE NOTICE

Exception #EX-2026-001 has been APPROVED.

Endpoint: POST /api/v1/auth/login
Change: Added rate limiting (429 response for >10 requests/min)
Reason: Critical security - brute force protection
Impact: Clients may receive 429 Too Many Requests

Migration required: NO (backward compatible)
Deployed: 2026-07-25 14:00 UTC

Full details: [link to exception request]
```

### 6.3 Changelog Entry

```markdown
## [1.0.1] - 2026-07-25

### Security
- **BREAKING** (Exception #EX-2026-001): Added rate limiting to login endpoint
  - New response: `429 Too Many Requests` when limit exceeded
  - Headers added: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`
  - Migration: Clients should handle 429 responses gracefully

### Fixed
- Auth bypass vulnerability in password reset flow
```

---

## 7. Versioning Rules During/After Freeze

### 7.1 Version Number Format
YFlow follows Semantic Versioning: `MAJOR.MINOR.PATCH`

| Version Part | When to Increment |
|--------------|-------------------|
| MAJOR | Breaking API changes (new version, e.g., v2.0.0) |
| MINOR | New features (backward compatible) |
| PATCH | Bug fixes, security patches, performance improvements |

### 7.2 During API Freeze

| Change Type | Version Bump |
|-------------|--------------|
| Bug fix (non-breaking) | PATCH (1.0.0 → 1.0.1) |
| Security patch | PATCH (1.0.0 → 1.0.1) |
| Performance fix | PATCH (1.0.0 → 1.0.1) |
| Documentation update | No bump (or PATCH) |
| Approved breaking change | MAJOR (1.0.0 → 2.0.0) |

### 7.3 Post-Freeze (Normal Development)

After v1.0.0 release, normal versioning resumes:

| Change Type | Version Bump |
|-------------|--------------|
| New feature (backward compatible) | MINOR (1.0.0 → 1.1.0) |
| Breaking change | MAJOR (1.0.0 → 2.0.0) |
| Bug fix | PATCH (1.0.0 → 1.0.1) |

### 7.4 API Versioning Strategy

| Scenario | Strategy |
|----------|----------|
| Small backward-compatible changes | Same version (v1) |
| New features | Same version (v1), increment MINOR |
| Breaking changes | New version (v2) |
| Deprecating endpoints | Keep old version for 6 months, add deprecation headers |

**Deprecation Header Example:**
```http
Deprecation: true
Sunset: Sat, 01 Jan 2027 00:00:00 GMT
Link: </api/v2/projects>; rel="successor-version"
```

---

## 8. Violation Consequences

### 8.1 Severity Levels

| Level | Description | Consequence |
|-------|-------------|-------------|
| **L1** | Accidental minor violation, caught in PR review | PR blocked, fix required |
| **L2** | Violation merged to staging | Immediate revert, incident report |
| **L3** | Violation reaches production | Emergency rollback, full retrospective |
| **L4** | Repeated violations | Escalation to management, process review |

### 8.2 Incident Response

```
1. Violation detected
   ↓
2. Assess impact (which endpoints, how many clients affected)
   ↓
3. Containment:
   ├─ If in PR: Block merge, request changes
   ├─ If in staging: Revert immediately
   └─ If in production: Evaluate rollback vs. emergency fix
   ↓
4. Root cause analysis:
   - How did this bypass controls?
   - Were CI checks insufficient?
   - Was training inadequate?
   ↓
5. Remediation:
   - Fix the violation
   - Strengthen controls
   - Update documentation/training
   ↓
6. Retrospective meeting (within 48 hours)
   ↓
7. Action items tracked to completion
```

### 8.3 Retrospective Template

```markdown
# API Freeze Violation Retrospective

**Incident ID:** INC-YYYY-NNN  
**Date:** YYYY-MM-DD  
**Facilitator:** Name

## Timeline
- HH:MM - Violation introduced
- HH:MM - Violation detected
- HH:MM - Containment action taken
- HH:MM - Resolution confirmed

## Impact
- Endpoints affected:
- Clients affected:
- Duration:
- Business impact:

## Root Cause (5 Whys)
1. Why did the violation occur?
2. Why wasn't it caught earlier?
3. Why did our controls fail?
4. ...
5. ...

## Contributing Factors
- 
- 

## What Went Well
- 
- 

## What Could Be Improved
- 
- 

## Action Items
| Item | Owner | Due Date | Status |
|------|-------|----------|--------|
| | | | |
| | | | |

## Lessons Learned
- 
- 
```

---

## 9. Roles & Responsibilities

| Role | Responsibilities |
|------|------------------|
| **Developer** | Follow freeze rules, request exceptions when needed, implement approved changes |
| **Code Reviewer** | Verify freeze compliance, check for violations, approve/reject PRs |
| **Lead QA** | Review exception requests, approve/reject exceptions, oversee enforcement |
| **DevOps** | Maintain CI/CD checks, monitor for violations, manage deployments |
| **CTO** | Final authority on freeze activation/deactivation, approve security exceptions |

---

## 10. References

- [docs/23-qa-strategy-execution-plan.md](./23-qa-strategy-execution-plan.md)
- [docs/09-api-specification.md](./09-api-specification.md)
- [docs/20-git-workflow.md](./20-git-workflow.md)
- [docs/VERSION_MANAGEMENT_SPEC.md](./VERSION_MANAGEMENT_SPEC.md)
- [Semantic Versioning](https://semver.org/)

---

## Appendix A: Quick Reference Card

### ✅ ALLOWED During Freeze
- Bug fixes (non-breaking)
- Performance optimizations
- Internal refactoring (same output)
- Documentation updates
- Test additions/improvements
- Relaxing validation rules
- Adding optional fields (carefully)

### ❌ PROHIBITED During Freeze
- Changing response structure
- Changing endpoint paths/methods
- Changing request schemas
- Adding required fields/params
- Making validation stricter
- Removing endpoints
- Changing data types

### 🔑 Exception Process
1. Fill out Exception Request template
2. Get Lead QA approval
3. Create hotfix branch
4. Implement minimal fix
5. Code review (Lead QA required)
6. Deploy to staging
7. Deploy to production
8. Monitor & retrospective

---

*Document Version: 1.0.0*  
*Status: Active*  
*Last Updated: 2026-07-25*