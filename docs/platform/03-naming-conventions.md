# Naming Conventions — YANS DIGITAL Platform (Task 7)

> One naming standard for every object across the platform. Deterministic, so any
> engineer or agent can derive a name without asking.

---

## 0. Global rules

- **App slug** = lowercase, no `y` prefix stripping — the product code lowercased:
  `yflow`, `ypass`, `ycrm`, `yticket`, `ycms`, `yhris`, `yfinance`.
  (Marketing may brand `ticketing` → the slug is still the canonical `yticket`; a
  domain alias may point to it.)
- **Environment tokens:** `prod` | `staging` | `dev`.
- Separators: kebab-case for infra objects (`yflow-backend-prod`),
  snake_case for DB identifiers (`yflow_prod`), SCREAMING_SNAKE for env vars.
- ASCII only. No spaces. Max 63 chars (DNS/Docker limit).

---

## 1. Applications (product codes)

| Object | Pattern | Example |
|--------|---------|---------|
| Product code | `Y<NAME>` uppercase | `YFLOW`, `YCRM` |
| App slug | `<name>` lowercase | `yflow`, `ycrm` |

## 2. Servers

| Object | Pattern | Example |
|--------|---------|---------|
| Role hostname | `<role>.yansdigital.com` | `host.yansdigital.com`, `db.yansdigital.com` |
| Tailscale node | `yd-<role><n>` | `yd-app1`, `yd-data1` |
| Cloud provider label | `yd-vps-<role>-<n>` | `yd-vps-app-1`, `yd-vps-data-1` |

## 3. Coolify projects / environments / resources

| Object | Pattern | Example |
|--------|---------|---------|
| Project | `<app-slug>` | `yflow` |
| Environment | `production` \| `staging` | `production` |
| Resource (backend) | `<app>-backend-<env>` | `yflow-backend-prod` |
| Resource (frontend) | `<app>-frontend-<env>` | `yflow-frontend-prod` |
| Resource (external DB) | `<app>-db-<env>` | `yflow-db-prod` |
| Resource (external Redis) | `<app>-redis-<env>` | `yflow-redis-prod` |

## 4. Repositories (org `YANS-DIGITAL-KREASI`)

| Object | Pattern | Example |
|--------|---------|---------|
| App monorepo | `<APP>` or `<app>` | `YFLOW` / `yflow` |
| Infra/platform repo | `platform` | `YANS-DIGITAL-KREASI/platform` |
| Shared library | `lib-<name>` | `lib-php-common` |
| Default branch | `main` | protected |
| Release branch | `release/x.y` | `release/1.2` |
| Feature branch | `feat/<ticket>-<slug>` | `feat/YF-42-workflow-builder` |
| Fix branch | `fix/<ticket>-<slug>` | `fix/YF-99-null-guard` |
| Hotfix branch | `hotfix/<slug>` | `hotfix/token-expiry` |

> Branch/commit conventions must stay consistent with `docs/20-git-workflow.md`
> (Conventional Commits). This file only adds the platform-scoped repo names.

## 5. Domains

| Object | Pattern | Example |
|--------|---------|---------|
| App production | `<app>.apps.yansdigital.com` | `yflow.apps.yansdigital.com` |
| App staging | `<app>.staging.yansdigital.com` | `yflow.staging.yansdigital.com` |
| Branded alias | `<brand>.apps.yansdigital.com` → app | `ticketing.apps…` → `yticket` |
| API subpath | *(same host)* `/api/v1` | `yflow.apps.yansdigital.com/api/v1` |

## 6. Docker networks & volumes

| Object | Pattern | Example |
|--------|---------|---------|
| App network | `<app>-<env>-net` | `yflow-prod-net` |
| Named volume | `<app>-<env>-<purpose>` | `yflow-prod-storage`, `yflow-prod-uploads` |
| Coolify system net | *(managed)* `coolify` | leave as Coolify sets it |

> Production data volumes should be minimal on VPS1 — durable data lives on VPS2.
> Only ephemeral/app-cache/uploads-pending-offload volumes belong on VPS1.

## 7. Databases (MariaDB on VPS2)

| Object | Pattern | Example |
|--------|---------|---------|
| Database | `<app>_<env>` | `yflow_prod`, `yflow_staging` |
| DB user | `<app>_<env>` | `yflow_prod` |
| Grant scope | user limited to its own DB only | `GRANT ALL ON yflow_prod.* TO 'yflow_prod'@'%'` |

## 8. Redis (VPS2)

Redis has no named DBs, only numeric indices. Assign a **fixed index per app+env**
and always set key prefixes.

| Object | Pattern | Example |
|--------|---------|---------|
| Logical DB index | assigned in registry (below) | `0` = yflow_prod |
| Key prefix | `<app>_<env>:` | `yflow_prod:` |
| ACL user (Redis ≥6) | `<app>_<env>` | `yflow_prod` |

**Redis index registry** (single source of truth — extend, never reuse):

| Index | App / Env |
|-------|-----------|
| 0 | yflow_prod |
| 1 | yflow_staging |
| 2 | ypass_prod |
| 3 | ypass_staging |
| 4 | ycrm_prod |
| … | assign sequentially on onboarding |

## 9. Environment variables

| Object | Pattern | Example |
|--------|---------|---------|
| Standard var | `SCREAMING_SNAKE_CASE` | `DATABASE_HOST` |
| App-specific | `<APP>_<NAME>` if collision risk | `YFLOW_FEATURE_X` |
| Never | secrets in var *names* | — |

## 10. Secrets

| Object | Pattern | Example |
|--------|---------|---------|
| Coolify secret | same as env var name | `APP_KEY`, `DB_PASSWORD` |
| GitHub Actions secret | `SCREAMING_SNAKE` | `COOLIFY_TOKEN`, `COOLIFY_WEBHOOK_YFLOW` |
| Deploy key title | `<app>-deploy-<env>` | `yflow-deploy-prod` |
| GitHub App | `yd-coolify-deploy` | one org-wide app |

---

## 11. Worked example — YFLOW production

```
Product code ......... YFLOW
App slug ............. yflow
Repo ................. YANS-DIGITAL-KREASI/YFLOW
Coolify project ...... yflow
Environment .......... production
Backend resource ..... yflow-backend-prod
Frontend resource .... yflow-frontend-prod
Domain ............... yflow.apps.yansdigital.com
Docker network ....... yflow-prod-net
Volume ............... yflow-prod-storage
MariaDB database ..... yflow_prod
MariaDB user ......... yflow_prod
Redis index .......... 0    (prefix yflow_prod:)
GH secret (webhook) .. COOLIFY_WEBHOOK_YFLOW
```

*Owner: Platform Engineering — Version 1.0 — Status: Active*
