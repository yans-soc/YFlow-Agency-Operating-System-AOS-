# Application Onboarding Guide — YANS DIGITAL Platform

> Step-by-step to add a NEW app (e.g. YPASS, YCRM, YTICKET) to the platform. Every app
> follows the exact same path. Target: from empty to live in under an hour.

Replace `<app>` with the lowercase app code (e.g. `ypass`).

---

## 0. Prerequisites (one-time platform state)
- Coolify reachable at `coolify.yansdigital.com`, GitHub App connected (Task 3).
- VPS2 MariaDB + Redis reachable over Tailscale.
- Cloudflare wildcard `*.apps.yansdigital.com` → VPS1, SSL Full (strict).

---

## 1. Create the repository
```
Org: YANS-DIGITAL-KREASI
Repo: <APP>            (e.g. YPASS)  — private
Default branch: main
```
Scaffold from the standard (`04-repository-standard.md`):
```
<APP>/
├── backend/  frontend/  docker/  docs/
├── .github/  .env.example  README.md
```
Enable branch protection on `main` (PR + CI). Add `.github/workflows/ci.yml`.

---

## 2. Provision data (VPS2, over Tailscale)
```sql
-- MariaDB
CREATE DATABASE <app>_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER '<app>_prod'@'%' IDENTIFIED BY '<strong-password>';
GRANT ALL PRIVILEGES ON <app>_prod.* TO '<app>_prod'@'%';
FLUSH PRIVILEGES;
```
Redis: assign next free logical DB index (e.g. `REDIS_DB=3`). Record in naming registry.

---

## 3. Create the Coolify project
Follow `06-coolify-project-structure.md`:
```
Project:      <app>
Environment:  production
Resources:
  - <app>-backend-prod   (repo backend/, Dockerfile)
  - <app>-frontend-prod  (repo frontend/)
Source: GitHub App → YANS-DIGITAL-KREASI/<APP>, branch main, auto-deploy ON
```

---

## 4. Domains
```
Backend/API : api.<app>.apps.yansdigital.com   (or <app>.apps.../api)
Frontend    : <app>.apps.yansdigital.com
```
Covered by wildcard cert — no new DNS record needed. Enable HTTPS in Coolify.

---

## 5. Shared + app variables
Attach shared env group (`08-shared-variables.md`): `APP_ENV, TZ, LOG_LEVEL,
DATABASE_HOST, DATABASE_PORT, REDIS_HOST, REDIS_PORT, MAIL_HOST, MAIL_PORT,
QUEUE_CONNECTION, CACHE_STORE, SESSION_DRIVER`.

App-specific (in the resource, not shared):
```
APP_NAME=<APP>
APP_KEY=<php artisan key:generate>
APP_URL=https://<app>.apps.yansdigital.com
DATABASE_NAME=<app>_prod
DATABASE_USERNAME=<app>_prod
DATABASE_PASSWORD=<secret>
REDIS_DB=<index>
```

---

## 6. Health check + rollback
- Backend health endpoint: `/api/health` (add if missing).
- Coolify: set health check path, enable auto-rollback on failure
  (see `09-deployment-cicd.md`).

---

## 7. First deploy
```
git push origin main   →  GitHub App webhook  →  Coolify build  →  deploy
```
Watch Coolify build logs; confirm health green.

---

## 8. Verify
```
[ ] https://<app>.apps.yansdigital.com loads (frontend)
[ ] API responds at the API domain, /api/health = 200
[ ] DB migrations ran against <app>_prod
[ ] Redis connective (cache/session)
[ ] SSL padlock valid (wildcard)
[ ] Auto-deploy fires on next push
[ ] Rollback tested (deploy bad build → auto-revert)
```

---

## 9. Onboarding checklist (copy per app)
```
[ ] Repo <APP> created in org, from standard, main protected
[ ] DB <app>_prod + user + Redis index provisioned (VPS2)
[ ] Coolify project <app> / production with backend+frontend
[ ] GitHub App source connected, auto-deploy ON
[ ] Domains set (wildcard), HTTPS on
[ ] Shared vars attached + app-specific vars set
[ ] Health check + auto-rollback configured
[ ] First deploy green; verification passed
[ ] Naming registry + DECISIONS updated
```

Same nine steps for app #1 and app #50.

*Owner: Platform Engineering — Version 1.0 — Status: Active*
