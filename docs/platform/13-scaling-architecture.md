# Scaling Architecture — YANS DIGITAL Platform (Task 11)

> The platform design does NOT change as app count grows from 1 → 50. Same repo
> standard, same Coolify shape, same domain scheme. You only add capacity.

---

## 1. Why the design already scales

- **Uniform app model** (`06-coolify-project-structure.md`): every app is one Coolify
  project with backend + frontend + external DB/Redis. Adding app #N is copy-paste.
- **Wildcard routing** (`*.apps.yansdigital.com`): no per-app DNS/cert work.
- **Data tier separated** (VPS2): apps stay stateless on VPS1; scale tiers independently.
- **Org-wide GitHub App**: one integration covers all repos automatically.

---

## 2. Capacity tiers

| Stage | Apps | VPS1 (app) | VPS2 (data) | Change needed |
|-------|------|-----------|-------------|---------------|
| Now | 1–5 | current single node | single MariaDB + Redis | none |
| Growth | ~10 | vertical bump (CPU/RAM) | monitor connections | tune `max_connections`, PHP-FPM workers |
| Scale | ~20 | add VPS1b as 2nd Coolify server | Redis maxmemory policy, DB backups tighter | Coolify multi-server; Traefik spans nodes |
| Large | ~50 | 2–3 app nodes, per-app replicas | MariaDB read replica / bigger box; managed object storage | horizontal app scaling + DB replica |

The **architecture is identical**; only node count / instance size changes.

---

## 3. Horizontal app scaling (per app)

- Increase replicas in Coolify (backend resource → replicas 2+).
- Requires: **stateless app** — sessions/cache in Redis (see shared vars
  `SESSION_DRIVER=redis`, `CACHE_STORE=redis`), uploads in object storage, not local disk.
- Traefik load-balances replicas automatically.
- Queue workers scale as separate Coolify resources (`<app>-worker-prod`).

---

## 4. Multi-server (VPS1 → VPS1 + VPS1b)

- Add the new server to Coolify (Servers → Add). Coolify installs its agent.
- Place new apps on either node; move existing apps by redeploy.
- Shared Traefik/Cloudflare front unchanged; wildcard still resolves to whichever
  node Coolify schedules (or a small LB in front).

---

## 5. Data tier scaling (VPS2)

- **MariaDB:** vertical first; then add a **read replica** for read-heavy apps.
  Per-app DB users/schemas already isolate tenants.
- **Redis:** per-app logical index now; move to separate Redis instances / Redis
  Cluster when memory or throughput demands.
- **Object storage:** the planned future service (S3-compatible, e.g. MinIO) absorbs
  file uploads so app nodes stay stateless — enables horizontal scaling cleanly.
- **Queues:** future dedicated queue service (or Redis-backed) as a separate resource.

---

## 6. Guardrails to keep scaling free

```
[ ] Apps remain stateless (no local session/upload writes)
[ ] Redis used for cache/session/queue
[ ] Uploads → object storage, never container FS
[ ] Every app isolated: own DB schema + user + Redis index
[ ] Health checks + auto-rollback on every app (no snowflakes)
[ ] Naming conventions followed so 50 apps stay legible
```

Follow these and app #50 onboards exactly like app #1.

*Owner: Platform Engineering — Version 1.0 — Status: Active*
