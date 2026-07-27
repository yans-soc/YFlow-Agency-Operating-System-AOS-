# Repository Standard — YANS DIGITAL Platform (Task 5)

> Every application repo in `YANS-DIGITAL-KREASI` uses this identical structure.
> Copy `templates/repo-scaffold/` to bootstrap a new app.

---

## 1. Canonical structure

```
<APP>/
├── backend/              # Laravel API
│   ├── app/ config/ database/ routes/ tests/ …
│   ├── composer.json
│   └── .env.example
├── frontend/             # Vite + React + TS
│   ├── src/ public/
│   ├── package.json
│   └── .env.example
├── docker/               # ← Deployment images (top-level, platform-standard)
│   ├── backend.Dockerfile
│   ├── frontend.Dockerfile
│   ├── nginx/
│   │   └── app.conf
│   └── php/
│       ├── php.ini
│       └── supervisord.conf
├── docs/                 # App documentation (this platform tree lives in the platform repo)
│   └── …
├── .github/
│   ├── workflows/
│   │   ├── ci.yml        # lint + test + static analysis
│   │   └── deploy.yml    # trigger Coolify deploy on main
│   ├── ISSUE_TEMPLATE/
│   └── pull_request_template.md
├── .env.example          # ← top-level platform baseline (shared + app-specific keys)
├── .gitignore
├── .editorconfig
├── docker-compose.yml    # local dev only (not used in prod)
└── README.md
```

### Deviation of THIS repo (YFlow) as-found
- `docker/` exists only under `backend/docker/` (no top-level `docker/`).
- No top-level `.env.example` (only `backend/.env.example`).
- CI/CD lives in `backend/.github/`, plus a root `.github/workflows/auto-sync.yml`.

These are recorded as remediation items in `07-application-onboarding.md` §"Bringing
YFlow to standard" and in `DECISIONS.md`. New repos start compliant via the scaffold.

---

## 2. Rules

| Rule | Requirement |
|------|-------------|
| Monorepo | backend + frontend in one repo per app |
| Docker at top level | Production images built from `docker/`, referenced by Coolify |
| One `.env.example` at root | Platform baseline; sub-app examples may add specifics |
| `main` protected | PR + green CI required; no direct pushes |
| Conventional Commits | per `docs/20-git-workflow.md` |
| Secrets never committed | enforced by `.gitignore` + secret scanning |
| CI mandatory | `ci.yml` must pass before merge |
| Deploy via Coolify | `deploy.yml` triggers Coolify webhook; no SSH secrets in repo |

---

## 3. Required root files

| File | Purpose |
|------|---------|
| `README.md` | App overview, local run, deploy pointer to platform docs |
| `.env.example` | Every env key (shared + app), placeholder values |
| `.gitignore` | Ignore `.env`, build output, vendor, node_modules |
| `.editorconfig` | Consistent whitespace |
| `docker-compose.yml` | Local dev stack (Postgres/MariaDB + Redis + app) |
| `.github/workflows/ci.yml` | Lint, test, static analysis |
| `.github/workflows/deploy.yml` | Coolify deploy trigger |

---

## 4. Branch & release model

Aligned with `docs/20-git-workflow.md`:
- `main` — always deployable, protected.
- `feat/*`, `fix/*` — short-lived, squash-merged.
- `release/x.y` — stabilization for a version.
- `hotfix/*` — cut from `main`, fast-tracked.
- Tags `vX.Y.Z` — trigger release workflow.

---

## 5. Scaffold usage

```bash
# From the platform repo:
cp -r docs/platform/templates/repo-scaffold /tmp/NEWAPP
cd /tmp/NEWAPP

# Replace placeholders (APP slug, product code):
grep -rl '__APP__'  . | xargs sed -i '' 's/__APP__/ycrm/g'
grep -rl '__PRODUCT__' . | xargs sed -i '' 's/__PRODUCT__/YCRM/g'

git init && git remote add origin git@github.com:YANS-DIGITAL-KREASI/YCRM.git
```

Then follow `07-application-onboarding.md`.

*Owner: Platform Engineering — Version 1.0 — Status: Active*
