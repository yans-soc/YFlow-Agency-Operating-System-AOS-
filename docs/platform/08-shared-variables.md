# Shared Variables — YANS DIGITAL Platform (Task 8)

> Defines which environment variables are **shared across all apps** (set once as
> Coolify *team/project shared variables*) versus **app-specific** (set inside each
> resource). Rule: a variable is shared **only if changing it should affect every
> app identically**.

---

## 1. Two-tier model in Coolify

```
Team-level shared variables      →  identical for every app (infra endpoints, TZ)
   └─ Project-level shared vars   →  overrides/additions for one app family
        └─ Resource env vars       →  app-specific + all secrets
```

Reference shared vars from a resource with Coolify's `{{ }}` interpolation, e.g.
`DB_HOST={{ DATABASE_HOST }}`.

---

## 2. SHARED variables (team-level) — non-secret

| Variable | Example value | Why shared |
|----------|---------------|-----------|
| `APP_ENV` | `production` | Same env class for all prod apps |
| `TZ` | `Asia/Jakarta` | One org timezone |
| `LOG_LEVEL` | `warning` | Uniform log verbosity |
| `LOG_CHANNEL` | `stderr` | Containers log to stdout/stderr |
| `DATABASE_HOST` | `db.yansdigital.com` (Tailscale IP) | One data server |
| `DATABASE_PORT` | `3306` | MariaDB port |
| `DATABASE_DRIVER` | `mysql` | Platform DB engine (⚠ see DECISIONS) |
| `REDIS_HOST` | `db.yansdigital.com` (Tailscale IP) | One Redis host |
| `REDIS_PORT` | `6379` | Redis port |
| `MAIL_HOST` | `smtp.provider.tld` | One transactional mail relay |
| `MAIL_PORT` | `587` | STARTTLS |
| `MAIL_ENCRYPTION` | `tls` | Uniform |
| `MAIL_FROM_ADDRESS` | `no-reply@yansdigital.com` | Org sender |
| `QUEUE_CONNECTION` | `redis` | Uniform queue backend |
| `CACHE_STORE` | `redis` | Uniform cache backend (Laravel 11 key) |
| `SESSION_DRIVER` | `redis` | Uniform session backend |
| `BROADCAST_CONNECTION` | `redis` | Uniform (if websockets used) |
| `FILESYSTEM_DISK` | `s3` | Uniform once object storage lands |
| `S3_ENDPOINT` | `https://storage.yansdigital.com` | Future shared object storage |
| `S3_REGION` | `us-east-1` | MinIO default |

> Note the **key names** are the platform standard. Laravel apps whose config
> expects `DB_HOST`/`DB_CONNECTION`/`REDIS_HOST` map them in the resource env, e.g.
> `DB_HOST={{ DATABASE_HOST }}`, `DB_CONNECTION={{ DATABASE_DRIVER }}`.

---

## 3. SHARED **secrets** (team-level, marked secret in Coolify)

Only truly-common secrets belong here. Prefer per-app credentials for blast-radius.

| Secret | Shared? | Rationale |
|--------|---------|-----------|
| `MAIL_PASSWORD` | ✅ if one relay account | Single SMTP account |
| `MAIL_USERNAME` | ✅ if one relay account | Single SMTP account |
| `S3_ACCESS_KEY_ID` | ⚠ prefer per-app | Only if one bucket policy |
| `S3_SECRET_ACCESS_KEY` | ⚠ prefer per-app | Only if one bucket policy |
| `SENTRY_DSN` | ⚠ per-project usually | One DSN per project is cleaner |

**Never shared:** `DB_PASSWORD`, `REDIS_PASSWORD`, `APP_KEY`, JWT/OAuth secrets,
any third-party API key scoped to one app. Each app gets its **own** DB user/password
and Redis ACL (see `03-naming-conventions.md` §7–8).

---

## 4. APP-SPECIFIC variables (resource-level) — never shared

| Variable | Example | Why per-app |
|----------|---------|-------------|
| `APP_NAME` | `YFlow` | Identity |
| `APP_KEY` | `base64:…` (secret) | Unique per app |
| `APP_URL` | `https://yflow.apps.yansdigital.com` | Unique host |
| `DB_DATABASE` | `yflow_prod` | Unique DB |
| `DB_USERNAME` | `yflow_prod` | Unique user |
| `DB_PASSWORD` | *(secret)* | Unique secret |
| `REDIS_PASSWORD` | *(secret)* | Unique ACL |
| `REDIS_DB` | `0` | Unique index (registry) |
| `REDIS_PREFIX` | `yflow_prod:` | Unique namespace |
| `SANCTUM_STATEFUL_DOMAINS` | `yflow.apps.yansdigital.com` | Unique |
| `SESSION_DOMAIN` | `.yansdigital.com` or app host | App scope |
| `<APP>_*` feature flags | `YFLOW_AI_ENABLED=true` | App feature |
| any app 3rd-party key | `OPENAI_API_KEY` (secret) | App scope |

---

## 5. Resolution precedence

`resource env` **overrides** `project shared` **overrides** `team shared`.
If an app must differ from the platform default, set the override at resource level;
never mutate the team default for one app.

---

## 6. Canonical `.env.example` (platform baseline)

The repo scaffold ships `.env.example` (see `templates/repo-scaffold/.env.example`)
containing every key above with placeholders and `# shared` / `# app-specific`
annotations, so a new app starts standards-compliant.

---

## 7. Change control

- Adding/renaming a **shared** var = platform change → PR to this file + update the
  team variable in Coolify → note in `DECISIONS.md`.
- Adding an **app-specific** var = no platform change; document in the app's README.

*Owner: Platform Engineering — Version 1.0 — Status: Active*
