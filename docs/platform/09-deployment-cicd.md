# Deployment & CI/CD Flow — YANS DIGITAL Platform (Task 9)

> The single production deployment pipeline every app uses. Push to `main`,
> Coolify builds, migrates, health-checks, and auto-rolls-back on failure.

---

## 1. Flow

```
 Developer
    │ git push origin main   (after PR + green CI)
    ▼
 GitHub (YANS-DIGITAL-KREASI/<APP>)
    │ push event → GitHub App webhook (HMAC-signed)
    ▼
 Coolify (coolify.yansdigital.com)
    │ 1. pull commit
    │ 2. docker build (docker/backend.Dockerfile, docker/frontend.Dockerfile)
    │ 3. pre-deploy: php artisan migrate --force
    │ 4. start new container(s)
    │ 5. health check  GET /api/health  (retries×interval)
    ├── healthy ──► swap Traefik traffic to new container ──► keep old image
    └── unhealthy ─► abort, keep OLD container live, mark deploy failed  (auto-rollback)
    ▼
 Traefik → Cloudflare → users
```

Zero-downtime: Traefik only switches after health passes. Old image retained for
manual rollback.

---

## 2. Two-stage pipeline

### Stage A — CI (GitHub Actions, `.github/workflows/ci.yml`)
Runs on every PR and push. **Blocks merge** if red.
- Backend: `composer install`, `php artisan test` / Pest, `phpstan`, `pint --test`.
- Frontend: `npm ci`, `npm run lint`, `npm run test`, `npm run build`.
- Optional: `docker build` smoke to catch Dockerfile drift.

### Stage B — Deploy (trigger only)
Preferred: **Coolify GitHub App auto-deploy** on push to `main` (no secrets in repo).
Fallback (`.github/workflows/deploy.yml`): POST the Coolify deploy webhook.

```yaml
# .github/workflows/deploy.yml (fallback trigger)
name: Deploy
on:
  push:
    branches: [main]
jobs:
  trigger-coolify:
    runs-on: ubuntu-latest
    steps:
      - name: Trigger Coolify deploy
        run: |
          curl -fsSL -X POST "${{ secrets.COOLIFY_WEBHOOK_URL }}" \
            -H "Authorization: Bearer ${{ secrets.COOLIFY_TOKEN }}"
```
Secrets `COOLIFY_WEBHOOK_URL`, `COOLIFY_TOKEN` live in GitHub repo/org secrets.

---

## 3. Health check contract

Every backend exposes:
```
GET /api/health  →  200 {"status":"ok","db":"ok","redis":"ok"}
```
Checks DB + Redis connectivity so a broken dependency fails the deploy.
(YFlow: add a lightweight route if not present — remediation item.)

---

## 4. Migrations

- Run as **pre-deploy** command `php artisan migrate --force`.
- Migrations must be **backward-compatible** (expand/contract) so old container keeps
  working if rollback happens. Never drop a column in the same release that stops using it.
- Destructive changes = two releases (deploy code that ignores column → later drop).

---

## 5. Rollback

| Trigger | Action |
|---------|--------|
| Health check fails | Coolify auto-aborts; old container stays live |
| Bad deploy noticed later | Coolify UI → Deployments → **Rollback** to previous image |
| DB migration broke | Restore from VPS2 backup (see ops doc) + redeploy prior tag |

Rollback target = previous successful image (retained). Always verify `/api/health`
after rollback.

---

## 6. Environments

| Env | Branch/trigger | Domain |
|-----|----------------|--------|
| production | push `main` | `<app>.apps.yansdigital.com` |
| staging (optional) | push `develop` or tag `-rc` | `<app>.staging.yansdigital.com` |

Staging uses `*_staging` DB/Redis on VPS2 — never shares prod data.

---

## 7. Guardrails

- No direct push to `main` (branch protection + required CI).
- Deploy secrets only in GitHub/Coolify, never in repo.
- Every deploy is a tagged, reproducible image; `latest` never deployed blindly.
- Alert on failed deploy (Coolify notification → email/Slack).

*Owner: Platform Engineering — Version 1.0 — Status: Active*
