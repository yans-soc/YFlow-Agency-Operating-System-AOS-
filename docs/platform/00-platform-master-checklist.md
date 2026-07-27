# YANS DIGITAL — Platform Master Checklist

> Reusable production platform standard for all YANS DIGITAL applications
> (YFLOW, YPASS, YCRM, YTICKET, YCMS, YHRIS, YFINANCE, …).
>
> Platform: **Coolify** (self-hosted PaaS) + **Traefik** (reverse proxy) on VPS1,
> **MariaDB + Redis** on VPS2, private mesh over **Tailscale**, edge via **Cloudflare**.

---

## Legend

| Symbol | Meaning |
|--------|---------|
| ✓ | Completed — artifact delivered in this repo |
| ⚠ | Needs Approval / needs live execution on the VPS by an operator |
| ✗ | Failed / blocked — cause + fix documented |

---

## Execution environment reality check (read first)

This repository was configured from a **developer workstation** (macOS) that has
**no network path** to the live infrastructure:

- No SSH session to `host.yansdigital.com` (VPS1) or the DB host (VPS2).
- No Tailscale membership on this workstation.
- No Coolify API token, Cloudflare API token, or GitHub Organization admin token
  present in the environment.
- The git remote of THIS repo is `git@github.com:yans-soc/YFlow-Agency-Operating-System-AOS-.git`
  (a **personal** account `yans-soc`), **not** the target org `YANS-DIGITAL-KREASI`.

Therefore every task is split into two provable halves:

1. **Design / standard / IaC** — fully authored here, versioned, reviewable, reusable. `✓`
2. **Live mutation** — turned into an **idempotent inspection or apply script + runbook**
   the operator runs *on the VPS or against the real APIs*. Marked `⚠` (needs live run).
   Nothing about the running infra is claimed as "verified" — that would be a fabrication.

This is the correct production behaviour: **no invented inspection output.**

---

## Master Checklist

| # | Task | Status | Deliverable |
|---|------|--------|-------------|
| 1 | Inspect Coolify version & compatibility | ⚠ | `scripts/inspect/10-coolify.sh` + [`10-coolify-version-runbook.md`](./10-coolify-version-runbook.md) |
| 2 | Inspect Docker / Networks / Volumes / Traefik / SSL / DNS / Wildcard / Let's Encrypt / Cloudflare | ⚠ | `scripts/inspect/20-infra.sh` + [`11-infra-inspection-report.md`](./11-infra-inspection-report.md) |
| 3 | GitHub App integration (production) | ⚠ | [`30-github-app-integration.md`](./30-github-app-integration.md) |
| 4 | GitHub Organization audit | ⚠ | `scripts/inspect/40-github-org.sh` + [`31-github-org-audit.md`](./31-github-org-audit.md) |
| 5 | Repository standard | ✓ | [`04-repository-standard.md`](./04-repository-standard.md) + `templates/repo-scaffold/` |
| 6 | Coolify project structure | ✓ | [`06-coolify-project-structure.md`](./06-coolify-project-structure.md) |
| 7 | Naming conventions | ✓ | [`03-naming-conventions.md`](./03-naming-conventions.md) |
| 8 | Shared variables | ✓ | [`08-shared-variables.md`](./08-shared-variables.md) |
| 9 | Deployment workflow | ✓ | [`09-deployment-workflow.md`](./09-deployment-workflow.md) |
| 10 | Security audit | ⚠ | `scripts/inspect/50-security-audit.sh` + [`10-security-audit.md`](./10-security-audit.md) |
| 11 | Future scaling architecture | ✓ | [`11-scaling-architecture.md`](./11-scaling-architecture.md) |
| 12 | Documentation set | ✓ | this `docs/platform/` tree |

---

## Documentation index (Task 12)

| Doc | File |
|-----|------|
| Platform Architecture | [`01-platform-architecture.md`](./01-platform-architecture.md) |
| Repository Standard | [`04-repository-standard.md`](./04-repository-standard.md) |
| Deployment Guide | [`09-deployment-workflow.md`](./09-deployment-workflow.md) |
| Application Onboarding Guide | [`07-application-onboarding.md`](./07-application-onboarding.md) |
| Naming Convention | [`03-naming-conventions.md`](./03-naming-conventions.md) |
| CI/CD Flow | [`09-deployment-workflow.md`](./09-deployment-workflow.md) |
| Secrets Management | [`12-secrets-management.md`](./12-secrets-management.md) |
| Backup Strategy | [`13-backup-strategy.md`](./13-backup-strategy.md) |
| Restore Strategy | [`14-restore-strategy.md`](./14-restore-strategy.md) |
| Disaster Recovery | [`15-disaster-recovery.md`](./15-disaster-recovery.md) |
| Security Checklist | [`10-security-audit.md`](./10-security-audit.md) |

---

## How an operator finishes the `⚠` items

```bash
# 1. From a machine ON the Tailscale mesh with SSH access to VPS1:
ssh yans@host.yansdigital.com

# 2. Copy the inspection scripts up (or clone the repo on VPS1):
scp -r docs/platform/scripts/inspect yans@host.yansdigital.com:~/inspect

# 3. Run each inspector; it prints a report and exits non-zero on findings:
bash ~/inspect/10-coolify.sh      | tee coolify-report.txt
bash ~/inspect/20-infra.sh        | tee infra-report.txt
bash ~/inspect/50-security-audit.sh | tee security-report.txt

# 4. For GitHub org audit (needs a GH token with read:org):
GITHUB_TOKEN=ghp_xxx GH_ORG=YANS-DIGITAL-KREASI bash 40-github-org.sh
```

Paste the outputs back into the corresponding `⚠` report file, flip its status to `✓`,
and commit. The platform is then fully verified.

---

*Owner: Platform Engineering*
*Version: 1.0*
*Status: Active*
