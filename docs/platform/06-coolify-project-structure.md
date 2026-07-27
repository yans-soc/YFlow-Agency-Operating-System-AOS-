# Coolify Project Structure — YANS DIGITAL Platform (Task 6)

> Every application is modeled identically in Coolify. Create it once by following
> this exact shape; `07-application-onboarding.md` is the click-by-click runbook.

---

## 1. Hierarchy (identical for every app)

```
Project: <app>                         e.g. "yflow"
└── Environment: production            (+ optional "staging")
    ├── Resource: <app>-backend-<env>  Laravel API   (Dockerfile: docker/backend.Dockerfile)
    │     ├── Domain: <app>.apps.yansdigital.com  (Traefik + Let's Encrypt)
    │     ├── Health check: GET /api/health → 200
    │     ├── Port: 8080 (container) → Traefik
    │     └── Env: {{ shared }} + app-specific + secrets
    ├── Resource: <app>-frontend-<env> Static SPA   (docker/frontend.Dockerfile)
    │     ├── Domain: same host, path / (backend owns /api)  OR app.apps subdomain
    │     └── Env: VITE_API_URL etc.
    ├── External: <app>-db-<env>       MariaDB @ db.yansdigital.com:3306 (VPS2)
    └── External: <app>-redis-<env>    Redis   @ db.yansdigital.com:6379 (VPS2, index N)
```

**Database & Redis are External resources** — Coolify does NOT provision them; they
already live on VPS2. Register them so Coolify can inject connection info and health-check.

---

## 2. Per-resource settings standard

### Backend resource
| Setting | Value |
|---------|-------|
| Build | Dockerfile `docker/backend.Dockerfile` |
| Exposed port | `8080` |
| Domain | `https://<app>.apps.yansdigital.com` |
| Health check | `GET /api/health` interval 10s, retries 3 |
| Pre-deploy cmd | `php artisan migrate --force` |
| Post-deploy cmd | `php artisan config:cache && php artisan route:cache` |
| Restart policy | `unless-stopped` |
| Replicas | 1 (raise later; see scaling doc) |

### Frontend resource
| Setting | Value |
|---------|-------|
| Build | Dockerfile `docker/frontend.Dockerfile` (nginx serving `dist/`) |
| Exposed port | `80` |
| Domain | same app host (Traefik path priority: `/api*` → backend) |
| Health check | `GET /` → 200 |

### External DB / Redis
| Setting | Value |
|---------|-------|
| Type | External database / External Redis |
| Host | `db.yansdigital.com` (Tailscale IP) |
| Port | `3306` / `6379` |
| Credentials | app-specific secret (`yflow_prod` user) |

---

## 3. Variables layout

- **Team shared variables** → `08-shared-variables.md` §2/§3 (infra endpoints, TZ, mail).
- **Project shared variables** → only if a whole app family needs an override.
- **Resource variables** → all app-specific keys + all secrets (§4 of shared-variables).

Reference shared into resource: `DB_HOST={{ DATABASE_HOST }}`.

---

## 4. Domain & SSL

- Add domain in the backend resource → Coolify requests a Let's Encrypt cert via
  Traefik. For `*.apps.yansdigital.com`, prefer the **wildcard DNS-01** cert so new
  apps need no per-host issuance (see `01-platform-architecture.md` §2).
- Cloudflare record: `CNAME <app>.apps → host.yansdigital.com` (proxied).
- Force HTTPS = on. HSTS handled by app `SecurityHeaders` middleware.

---

## 5. Deploy triggers

- **Source:** GitHub App connection to `YANS-DIGITAL-KREASI/<APP>` (see Task 3 doc).
- **Auto deploy:** on push to `main` → webhook → build → deploy.
- **Rollback:** Coolify keeps the previous image; one-click / API rollback on failed
  health check (see `09-deployment-cicd.md`).

---

## 6. Golden-path checklist to create one app in Coolify

```
[ ] Create Project "<app>"
[ ] Add Environment "production"
[ ] Connect GitHub App → select repo YANS-DIGITAL-KREASI/<APP>
[ ] Add backend resource (Dockerfile docker/backend.Dockerfile, port 8080)
[ ] Add frontend resource (Dockerfile docker/frontend.Dockerfile, port 80)
[ ] Register External MariaDB (db.yansdigital.com:3306, db=<app>_prod)
[ ] Register External Redis (db.yansdigital.com:6379, index N)
[ ] Set resource env from .env.example + secrets
[ ] Add domain <app>.apps.yansdigital.com + enable SSL
[ ] Set pre-deploy migrate + health check /api/health
[ ] Trigger first deploy, verify health, verify rollback works
```

Because every app uses this identical shape, onboarding app #2..#50 is copy-paste.

*Owner: Platform Engineering — Version 1.0 — Status: Active*
