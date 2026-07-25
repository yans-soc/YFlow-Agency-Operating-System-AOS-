# Staging Deployment Guide — YFlow

## Overview
This guide covers staging environment setup and deployment procedures.

---

## Prerequisites

### Server Requirements
- Ubuntu 22.04 LTS or later
- Docker 24.0+
- Docker Compose 2.20+
- 4GB RAM minimum (8GB recommended)
- 20GB disk space

### GitHub Setup Required
1. Repository secrets configured
2. Branch protection rules enabled
3. Deploy key added to server

---

## Files Created for Staging

| File | Purpose |
|------|---------|
| `docker-compose.staging.yml` | Staging Docker compose configuration |
| `.env.staging` | Environment template for staging |
| `docker/nginx/staging.conf` | Nginx configuration for staging |

---

## Initial Server Setup

### 1. Install Dependencies
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker --version
docker-compose --version
```

### 2. Generate Deploy Key (on local machine)
```bash
ssh-keygen -t ed25519 -f ~/.ssh/yflow_deploy -N ""
cat ~/.ssh/yflow_deploy.pub
```

Add public key to server `~/.ssh/authorized_keys`

### 3. Configure GitHub Secrets
Go to GitHub → Settings → Secrets and variables → Actions

| Secret | Value |
|--------|-------|
| `DEPLOY_HOST` | Your server IP/domain |
| `DEPLOY_USER` | SSH username (e.g., `deploy`) |
| `DEPLOY_KEY` | Content of `~/.ssh/yflow_deploy` (private key) |

---

## Deploying to Staging

### Option A: Via GitHub Actions (Recommended)

1. Push code to `develop` branch
2. Go to Actions → Deploy workflow
3. Click "Run workflow"
4. Select environment: `staging`
5. Execute

### Option B: Manual Deployment

```bash
# On staging server
cd /var/www/yflow

# Pull latest code
git pull origin develop

# Copy staging env
cp .env.staging .env

# Generate app key if new
php artisan key:generate --force

# Build and start containers
docker-compose -f docker-compose.staging.yml up -d --build

# Run migrations
docker-compose -f docker-compose.staging.yml exec app php artisan migrate --force

# Seed database (optional)
docker-compose -f docker-compose.staging.yml exec app php artisan db:seed

# Check logs
docker-compose -f docker-compose.staging.yml logs -f app
```

---

## Environment Configuration

Edit `.env` on staging server:

```bash
APP_KEY=base64:... # Generate with: php artisan key:generate
DB_PASSWORD=secure_password_here
REVERB_APP_ID=staging_app_id
REVERB_APP_KEY=staging_app_key
REVERB_APP_SECRET=staging_app_secret
APP_URL=https://staging.yourdomain.com
```

---

## Verification Checklist

After deployment, verify:

- [ ] Application responds at `http://server-ip`
- [ ] `/health` endpoint returns 200
- [ ] Database migrations completed
- [ ] WebSocket connection works (port 8080)
- [ ] Mailpit accessible (port 8025)
- [ ] No errors in application logs

```bash
# Health check
curl http://localhost/health

# Check container status
docker-compose -f docker-compose.staging.yml ps

# View logs
docker-compose -f docker-compose.staging.yml logs app
```

---

## Common Issues

### Container won't start
```bash
docker-compose -f docker-compose.staging.yml down
docker-compose -f docker-compose.staging.yml up -d --build
```

### Database connection failed
```bash
# Wait for PostgreSQL healthcheck
docker-compose -f docker-compose.staging.yml logs pgsql
```

### Permission denied
```bash
sudo chown -R $USER:$USER /var/www/yflow/storage
```

---

## Updating Staging

```bash
# Pull latest code
git pull origin develop

# Rebuild and restart
docker-compose -f docker-compose.staging.yml up -d --build

# Run new migrations
docker-compose -f docker-compose.staging.yml exec app php artisan migrate --force
```

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*