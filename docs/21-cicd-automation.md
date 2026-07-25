# CI/CD Automation — YFlow

## Workflows Overview

### 1. CI Workflow (`ci.yml`)
**Triggers:** Push to `main`, `develop`, PRs

**Jobs:**
- `lint` - PHP Pint code style check
- `test` - PHPUnit tests with PostgreSQL + Redis
- `static-analysis` - PHPStan analysis

**Status:** ✅ Active

---

### 2. Deploy Workflow (`deploy.yml`)
**Triggers:** Push to `main`, manual dispatch

**Jobs:**
- `deploy` - Build and deploy artifacts
- `rollback` - Automatic rollback on failure

**Features:**
- Environment selection (staging/production)
- Artifact caching
- Deployment notifications
- Rollback capability

**Status:** ✅ Active

---

### 3. Release Workflow (`release.yml`)
**Triggers:** Manual dispatch

**Inputs:**
- Version type (patch/minor/major)
- Changelog summary

**Jobs:**
- `prepare-release` - Version bump, tag, GitHub release
- `notify-release` - Release notifications
- `update-docs` - Documentation updates

**Features:**
- Semantic versioning
- Auto-generated changelog
- Git tagging
- GitHub release creation

**Status:** ✅ Active

---

## Branch Protection Rules

### `main`
- Require PR approval
- Require CI passing
- Require branch up-to-date
- Block force pushes

### `develop`
- Require PR approval
- Require CI passing
- Block force pushes

---

## Secrets Required

| Secret | Purpose |
|--------|---------|
| `DEPLOY_HOST` | Server hostname |
| `DEPLOY_USER` | SSH username |
| `DEPLOY_KEY` | SSH private key |
| `SLACK_WEBHOOK` | Notification webhook |

---

## Usage

### Create Release
1. Go to Actions → Release
2. Select "Run workflow"
3. Choose version type
4. Enter changelog
5. Execute

### Deploy to Production
1. Go to Actions → Deploy
2. Select "Run workflow"
3. Choose environment
4. Execute

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*