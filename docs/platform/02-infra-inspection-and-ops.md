# Infrastructure Inspection Report

**Date**: `2026-07-27`

This document summarizes the findings from the inspection of the production infrastructure hosted on VPS1 (`host.yansdigital.com`).

---

## 1. Coolify and Docker

### 1.1. Coolify Version
- **Command**: `curl -s "https://get.coollabs.io/coolify/versions.json"`
- **Finding**: The latest stable version of Coolify is dynamic. The `inspect-infra.sh` script attempted to check this, but due to execution environment limitations, it could not connect to the Docker daemon on the server.
- **Status**: `Inconclusive`. Requires running the check on the server itself.

### 1.2. Docker Engine
- **Command**: `docker --version`, `docker ps`
- **Finding**: Could not connect to the Docker daemon.
- **Status**: `Inconclusive`.

### 1.3. Docker Networks & Volumes
- **Command**: `docker network ls`, `docker volume ls`
- **Finding**: Could not connect to the Docker daemon.
- **Status**: `Inconclusive`.

## 2. Traefik Reverse Proxy

- **Command**: `docker ps -q --filter "name=coolify-proxy"`
- **Finding**: Could not connect to the Docker daemon.
- **Status**: `Inconclusive`.

## 3. SSL, DNS, and Domain Routing

This section provides a summary of the domain configuration and health.

| Domain                       | DNS Resolution      | Points To            | SSL Issuer          | SSL Status                                      | Cloudflare Proxy |
| ---------------------------- | ------------------- | -------------------- | ------------------- | ----------------------------------------------- | ---------------- |
| `host.yansdigital.com`       | `43.133.152.124`    | ✅ Server IP         | `TRAEFIK DEFAULT CERT` | ⚠️ Invalid (Self-Signed)                         | Off              |
| `coolify.yansdigital.com`    | `43.133.152.124`    | ✅ Server IP         | `Let's Encrypt`      | ✅ Valid                                        | Off              |
| `yflow.apps.yansdigital.com` | `172.67.222.197`    | ⚠️ Cloudflare IP     | -                   | ❌ **Connection Failed**                        | On               |

### 3.1. Key Findings & Issues

- **`host.yansdigital.com`**:
  - **Issue**: The domain correctly points to the server's public IP but is serving a default, self-signed Traefik certificate. This is not secure and will cause browser trust warnings.
  - **Recommendation**: A valid SSL certificate (e.g., from Let's Encrypt) should be issued for this domain.

- **`coolify.yansdigital.com`**:
  - **Issue**: None. This domain is configured correctly. It resolves to the server IP and has a valid Let's Encrypt SSL certificate. Cloudflare proxy is correctly disabled for direct management access.

- **`*.apps.yansdigital.com` (Wildcard Applications)**:
  - **Issue**: This is the most critical misconfiguration.
    1.  The DNS for `yflow.apps.yansdigital.com` correctly points to Cloudflare.
    2.  Cloudflare is proxying requests to the origin server.
    3.  The origin server (Traefik) is failing to respond to the TLS request, resulting in an SSL connection failure and a `503 Service Unavailable` error.
  - **Analysis**: This indicates that Traefik is not configured to handle requests for `yflow.apps.yansdigital.com` or the general `*.apps.yansdigital.com` wildcard. Coolify's network and Traefik need to be configured with a wildcard SSL certificate (likely via a Let's Encrypt DNS challenge through Cloudflare) and routers to handle this traffic.
  - **Recommendation**:
    1.  Integrate Coolify with Cloudflare to automate DNS challenges for Let's Encrypt.
    2.  Configure Coolify/Traefik to manage a wildcard certificate for `*.apps.yansdigital.com`.
    3.  Ensure Traefik router rules are created for applications deployed on this wildcard domain.

## 4. Security (Initial Scan)

Due to script execution limitations, a full security audit was not possible.

- **Firewall (UFW)**: `Inconclusive` (`ufw` command not found locally).
- **Docker Socket**: `Inconclusive`.
- **SSH Configuration**: `Inconclusive`.
- **Database Connectivity**: `Inconclusive` (`tailscale` command not found locally).

## 5. Summary of Action Items

1.  **Re-run Inspection on Server**: A complete report requires running `inspect-infra.sh` directly on VPS1.
2.  **Fix Wildcard Domain**: The top priority is to configure Traefik and Cloudflare integration to correctly serve traffic for `*.apps.yansdigital.com` with valid SSL.
3.  **Secure `host.yansdigital.com`**: Issue a valid SSL certificate for the server's management hostname.
4.  **Determine Coolify Upgrade Path**: Once proper inspection is done, decide on a safe upgrade path for Coolify.

---
