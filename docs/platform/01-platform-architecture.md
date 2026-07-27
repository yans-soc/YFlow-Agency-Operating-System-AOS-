# Platform Architecture — YANS DIGITAL AOS

> The reusable multi-application platform on which YFLOW, YPASS, YCRM, YTICKET,
> YCMS, YHRIS, YFINANCE and every future app run under **one standard**.

---

## 1. Topology

```
                          Internet
                             │
                    ┌────────▼─────────┐
                    │   Cloudflare      │  DNS + proxy (orange cloud) + WAF
                    │  yansdigital.com  │  Origin CA / Full (strict) TLS
                    └────────┬─────────┘
                             │  :443 (only 80/443 open to world)
        ┌────────────────────▼──────────────────────┐
        │  VPS1 — Application Server                  │
        │  host.yansdigital.com                      │
        │                                            │
        │  ┌──────────────────────────────────────┐  │
        │  │ Traefik (Coolify-managed proxy)       │  │
        │  │  - Let's Encrypt (HTTP-01/DNS-01)     │  │
        │  │  - Wildcard route *.apps.yansdigital  │  │
        │  └───────────────┬──────────────────────┘  │
        │                  │ Docker network per app   │
        │  ┌───────────────▼───────┐  ┌────────────┐  │
        │  │ Coolify control plane │  │ App stacks │  │
        │  │ coolify.yansdigital   │  │ yflow, …   │  │
        │  └───────────────────────┘  └─────┬──────┘  │
        └───────────────────────────────────┼─────────┘
                                             │ Tailscale (WireGuard, private)
        ┌────────────────────────────────────▼────────┐
        │  VPS2 — Data Server (db.yansdigital.com)      │
        │   - MariaDB  (per-app database + user)        │
        │   - Redis    (per-app logical DB index)       │
        │   - (future) Object storage (MinIO/S3)        │
        │   - (future) Queue broker                     │
        │  NO public ports. Reachable only via Tailscale│
        └───────────────────────────────────────────────┘
```

### Trust zones
| Zone | Members | Exposure |
|------|---------|----------|
| Edge | Cloudflare | Public 80/443 |
| App | VPS1 (Traefik, Coolify, app containers) | Only 80/443 via Cloudflare; SSH via Tailscale |
| Data | VPS2 (MariaDB, Redis, storage) | **No public ports.** Tailscale-only |
| Control | Coolify UI, GitHub App, Cloudflare API | Auth + IP/Tailscale restricted |

---

## 2. Domain plan

| Purpose | Hostname | Terminates at |
|---------|----------|---------------|
| Management | `coolify.yansdigital.com` | Coolify UI on VPS1 (restrict to Tailscale/allowlist) |
| Infra (VPS1) | `host.yansdigital.com` | VPS1 host record |
| Data (VPS2) | `db.yansdigital.com` | VPS2 host record (Tailscale IP; not public) |
| Applications | `*.apps.yansdigital.com` | Traefik wildcard → per-app container |

Application examples: `yflow.apps.yansdigital.com`, `ypass.apps.yansdigital.com`,
`ycrm.apps.yansdigital.com`, `ticketing.apps.yansdigital.com`.

**Wildcard strategy:** one Cloudflare `CNAME *.apps → host.yansdigital.com`
(proxied). Traefik/Coolify route by `Host()` header. New app = zero DNS work.
TLS for the wildcard uses a **DNS-01** Let's Encrypt cert (`*.apps.yansdigital.com`)
so per-app HTTP-01 challenges are not needed.

---

## 3. Component responsibilities

| Component | Owns | Does NOT own |
|-----------|------|--------------|
| Cloudflare | Public DNS, edge TLS, WAF, DDoS, caching | App routing internals |
| Traefik | Host routing, Let's Encrypt origin cert, HTTP→HTTPS | Business logic |
| Coolify | Build, deploy, env vars, health checks, rollback, logs | Data persistence |
| VPS2 MariaDB/Redis | Durable state, backups | Compute for apps |
| GitHub (org `YANS-DIGITAL-KREASI`) | Source, CI, GitHub App deploy trigger | Runtime |

---

## 4. Standard application shape

Every app is the **same three logical resources** inside one Coolify project:

1. **backend** — Laravel (PHP-FPM + Nginx or Octane), image built from `docker/`.
2. **frontend** — Vite/React static build served by Nginx (or SSR container).
3. **external data** — MariaDB database + Redis logical index on VPS2
   (declared as Coolify "external" resources, never provisioned per-app on VPS1).

Backend and frontend may be **one repo** (this repo's `backend/` + `frontend/`)
deployed as two Coolify resources, or a combined image. Data is **always external**.

---

## 5. Network & port policy

| Port | Where | Open to |
|------|-------|---------|
| 443 | VPS1 | Cloudflare only (firewall to CF IP ranges) |
| 80 | VPS1 | Cloudflare only → redirect 443 |
| 22 (SSH) | VPS1 & VPS2 | **Tailscale interface only** — never `0.0.0.0` |
| 3306 (MariaDB) | VPS2 | Tailscale CIDR only |
| 6379 (Redis) | VPS2 | Tailscale CIDR only, `requirepass` set |
| 8000 (Coolify) | VPS1 | localhost / Tailscale; public only behind Traefik + auth |

Docker inter-service traffic stays on **per-app Docker networks**; no container
publishes ports to the host except through Traefik.

---

## 6. Data flow (request lifecycle)

```
Browser → Cloudflare(443,WAF,cache) → VPS1:443 Traefik
        → Host() match → app container (backend/frontend)
        → (Tailscale) → VPS2 MariaDB/Redis → response
```

## 7. Deploy flow (summary — full detail in 09)

```
git push (org repo) → GitHub App webhook → Coolify
        → pull + build image → run migrations → health check
        → swap traffic → keep previous image for rollback
```

## 8. Design invariants (never violate)

1. **Data lives only on VPS2.** No production DB/Redis container on VPS1.
2. **No public port except 80/443 (Cloudflare-fronted).** SSH/DB via Tailscale.
3. **One standard per app** — same repo shape, same Coolify shape, same naming.
4. **Secrets never in git.** Injected via Coolify env / GitHub Actions secrets.
5. **Every mutation is reproducible** — scripted or documented, never ad-hoc.
6. **Adding an app changes data, not platform design** (see `11-scaling-architecture.md`).

---

## 9. Gap between this repo and target platform (as-found)

Inspection of this repository (see `11-infra-inspection-report.md`) found the
**codebase does not yet reference the Coolify platform**:

| Area | As-found in repo | Target |
|------|------------------|--------|
| Deploy config | `backend/.github/workflows/deploy.yml` = placeholder `echo` | GitHub App → Coolify |
| DB | CI/compose use **PostgreSQL** | Platform standard = **MariaDB** on VPS2 |
| Proxy | docs mention **Nginx**; no Traefik | Traefik via Coolify |
| Remote | `yans-soc` personal repo | org `YANS-DIGITAL-KREASI` |
| Reverse proxy / CF / Tailscale | not referenced anywhere | core of platform |

> **Decision required (⚠):** the platform standard here assumes **MariaDB** (per the
> VPS2 spec in the task). The YFlow app code currently targets **PostgreSQL**. Pick one:
> (a) keep MariaDB and migrate YFlow's DB driver, or (b) run PostgreSQL on VPS2 as the
> platform DB. This is tracked in `docs/platform/DECISIONS.md`.

---

*Owner: Platform Engineering — Version 1.0 — Status: Active*
