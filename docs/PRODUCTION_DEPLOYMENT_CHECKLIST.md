# Production Deployment Checklist — YFlow

> **CRITICAL SECURITY NOTICE**
> 
> Follow this checklist exactly to ensure secure production deployment.
> 
> Last Updated: 2026-07-26
> Version: 1.0

---

## Pre-Deployment Security Audit

### Environment Configuration
- [ ] Copy `.env.example` to `.env` on server
- [ ] Generate new APP_KEY: `php artisan key:generate`
- [ ] Set `APP_DEBUG=false` (REQUIRED - exposes sensitive info)
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_URL=https://yourdomain.com` (HTTPS required)
- [ ] Set `LOG_LEVEL=info` or `warning` (not debug)
- [ ] Set `SESSION_ENCRYPT=true`
- [ ] Configure database credentials securely

### Database Security
- [ ] Use strong database password (min 32 chars)
- [ ] Restrict database access to application server only
- [ ] Enable database encryption at rest (if supported)
- [ ] Create dedicated database user (not root)
- [ ] Limit user privileges to minimum required

### File Permissions
```bash
# Execute on server after deployment
cd /path/to/yflow/backend

# Set ownership
sudo chown -R www-data:www-data .

# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Special permissions for storage and cache
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Deployment Steps

### 1. Server Preparation
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install dependencies
sudo apt install -y php8.4 php8.4-cli php8.4-fpm php8.4-mysql \
    php8.4-curl php8.4-gd php8.4-mbstring php8.4-xml \
    php8.4-bcmath php8.4-redis nginx mysql-server git curl

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js (for frontend build)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

### 2. Code Deployment
```bash
# Clone repository
cd /var/www
sudo git clone git@github.com:yans-soc/YFlow-Agency-Operating-System-AOS-.git yflow
cd yflow/backend

# Install dependencies (production)
composer install --no-dev --optimize-autoloader --classmap-authoritative

# Environment setup
cp .env.example .env
nano .env  # Edit with production values

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 3. Frontend Build
```bash
cd ../frontend

# Install dependencies
npm ci

# Build for production
npm run build

# Copy build output to public
cp -r dist/* ../backend/public/
```

### 4. Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers on;

    # HSTS Header
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" always;

    root /var/www/yflow/backend/public;
    index index.php;

    # Security Headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "DENY" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
}
```

### 5. SSL Certificate (Let's Encrypt)
```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com

# Auto-renewal test
sudo certbot renew --dry-run
```

### 6. Supervisor Configuration (Queue Workers)
```ini
# /etc/supervisor/conf.d/yflow-worker.conf
[program:yflow-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/yflow/backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
numprocs=2
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/yflow/backend/storage/logs/worker.log
```

```bash
# Apply configuration
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yflow-worker:*
```

### 7. Cron Jobs (Scheduler)
```bash
# Edit crontab
crontab -e

# Add Laravel scheduler (runs every minute)
* * * * * cd /var/www/yflow/backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## Post-Deployment Verification

### Health Check
```bash
# Test health endpoint
curl https://yourdomain.com/api/v1/health

# Expected response:
{
  "status": "healthy",
  "timestamp": "...",
  "services": {
    "database": "connected",
    "redis": "connected"
  },
  "version": "1.0.0",
  "environment": "production"
}
```

### Security Headers Check
```bash
curl -I https://yourdomain.com

# Verify headers present:
# X-Content-Type-Options: nosniff
# X-Frame-Options: DENY
# X-XSS-Protection: 1; mode=block
# Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
# Content-Security-Policy: default-src 'self'...
```

### Functional Tests
- [ ] User registration works
- [ ] User login works
- [ ] API authentication (Sanctum) works
- [ ] Protected endpoints require authentication
- [ ] File uploads work correctly
- [ ] Queue workers processing jobs
- [ ] Scheduled tasks running

---

## Monitoring Setup

### Log Rotation
```bash
# /etc/logrotate.d/yflow
/var/www/yflow/backend/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.4-fpm
    endscript
}
```

### Application Monitoring
- [ ] Enable error tracking (Sentry, Bugsnag, etc.)
- [ ] Set up uptime monitoring
- [ ] Configure alert notifications
- [ ] Monitor disk space usage
- [ ] Monitor memory usage
- [ ] Monitor queue worker status

---

## Rollback Procedure

If deployment fails:

```bash
# 1. Revert code
cd /var/www/yflow
git reset --hard <previous-commit-hash>

# 2. Clear caches
cd backend
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 3. Restore database (if migration failed)
php artisan migrate:rollback --step=1

# 4. Restart services
sudo systemctl restart php8.4-fpm
sudo systemctl restart nginx
sudo supervisorctl restart yflow-worker:*
```

---

## Security Hardening Checklist

- [ ] Firewall enabled (UFW): `ufw allow 'Nginx Full' && ufw enable`
- [ ] SSH key authentication only (disable password)
- [ ] Fail2ban installed and configured
- [ ] Database not exposed to public internet
- [ ] Regular security updates scheduled
- [ ] Backup strategy implemented and tested
- [ ] Rate limiting configured on API
- [ ] CORS properly configured
- [ ] No debug tools in production (Telescope, Debugbar)

---

## Emergency Contacts

| Role | Contact | Phone |
|------|---------|-------|
| DevOps Lead | TBD | TBD |
| Backend Lead | TBD | TBD |
| On-Call Engineer | TBD | TBD |

---

## Deployment Sign-off

| Task | Completed By | Date | Notes |
|------|--------------|------|-------|
| Pre-deployment audit | | | |
| Code deployment | | | |
| Frontend build | | | |
| SSL configuration | | | |
| Health check passed | | | |
| Security headers verified | | | |
| Monitoring active | | | |

**Deployment Approved By:** ___________________  
**Date:** _______________  
**Time:** _______________

---

*Document Version: 1.0*  
*Status: Active*