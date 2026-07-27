# YFlow Deployment Guide

## Table of Contents
1. [Production Requirements](#production-requirements)
2. [Environment Configuration](#environment-configuration)
3. [Docker Deployment](#docker-deployment)
4. [Traditional Server Deployment](#traditional-server-deployment)
5. [Database Setup](#database-setup)
6. [SSL Configuration](#ssl-configuration)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring & Logging](#monitoring--logging)
9. [Backup Strategy](#backup-strategy)
10. [Troubleshooting](#troubleshooting)

---

## Production Requirements

### System Requirements
- **CPU:** 2+ cores
- **RAM:** 4GB minimum, 8GB recommended
- **Storage:** 20GB+ SSD
- **OS:** Linux (Ubuntu 20.04+, CentOS 8+)

### Software Requirements
- **PHP:** 8.2+
- **PostgreSQL:** 14+
- **Redis:** 6.2+
- **Node.js:** 18+
- **Composer:** 2.x
- **Nginx:** 1.20+ (for traditional deployment)

---

## Environment Configuration

### Backend `.env` Production Template

```env
# Application
APP_NAME=YFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Security
APP_KEY=base64:your-generated-key-here
SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true

# Database
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=yflow_production
DB_USERNAME=yflow_user
DB_PASSWORD=strong-password-here

# Redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=redis-password-here
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis

# Cache
CACHE_STORE=database

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# API
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,www.yourdomain.com

# Reverb (WebSocket)
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST="yourdomain.com"
REVERB_PORT=443
REVERB_SCHEME=https
```

### Frontend `.env` Production Template

```env
VITE_API_BASE_URL=https://api.yourdomain.com
VITE_APP_NAME=YFlow
```

---

## Docker Deployment

### Production Docker Compose

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  # PostgreSQL
  postgres:
    image: postgres:15-alpine
    container_name: yflow_postgres
    environment:
      POSTGRES_DB: yflow_production
      POSTGRES_USER: yflow_user
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - yflow_network
    restart: always
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U yflow_user"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Redis
  redis:
    image: redis:7-alpine
    container_name: yflow_redis
    command: redis-server --requirepass ${REDIS_PASSWORD}
    volumes:
      - redis_data:/data
    networks:
      - yflow_network
    restart: always
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Laravel Backend
  laravel:
    build:
      context: ./backend
      dockerfile: docker/8.4/Dockerfile
    container_name: yflow_laravel
    environment:
      APP_ENV: production
      APP_DEBUG: 'false'
      DB_HOST: postgres
      REDIS_HOST: redis
    volumes:
      - ./backend:/var/www/html
      - storage_data:/var/www/html/storage
    depends_on:
      postgres:
        condition: service_healthy
      redis:
        condition: service_healthy
    networks:
      - yflow_network
    restart: always

  # Nginx
  nginx:
    image: nginx:alpine
    container_name: yflow_nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./backend:/var/www/html
      - ./docker/nginx:/etc/nginx/conf.d
      - ./ssl:/etc/nginx/ssl
    depends_on:
      - laravel
    networks:
      - yflow_network
    restart: always

  # Node (for building frontend)
  node:
    image: node:18-alpine
    container_name: yflow_node
    working_dir: /app
    volumes:
      - ./frontend:/app
    command: sh -c "npm install && npm run build"
    networks:
      - yflow_network

networks:
  yflow_network:
    driver: bridge

volumes:
  postgres_data:
  redis_data:
  storage_data:
```

### Deploy with Docker

```bash
# Set environment variables
export DB_PASSWORD=your-strong-password
export REDIS_PASSWORD=your-redis-password

# Build and start containers
docker compose -f docker-compose.prod.yml up -d --build

# Run migrations inside container
docker compose exec laravel php artisan migrate --force

# Seed database (optional)
docker compose exec laravel php artisan db:seed --class=DemoSeeder --force

# Optimize Laravel
docker compose exec laravel php artisan config:cache
docker compose exec laravel php artisan route:cache
docker compose exec laravel php artisan view:cache
docker compose exec laravel php artisan event:cache

# Start queue worker
docker compose exec -d laravel php artisan queue:work --tries=3

# Start WebSocket server
docker compose exec -d laravel php artisan reverb:start
```

---

## Traditional Server Deployment

### 1. Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install dependencies
sudo apt install -y nginx php8.2 php8.2-fpm php8.2-pgsql php8.2-redis \
    php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip composer \
    postgresql postgresql-contrib redis-server nodejs npm git

# Install Node via nvm (recommended)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
nvm install 18
nvm use 18
```

### 2. Clone Repository

```bash
cd /var/www
git clone https://github.com/your-org/yflow.git
cd yflow

# Install backend dependencies
cd backend
composer install --no-dev --optimize-autoloader

# Install frontend dependencies
cd ../frontend
npm install
npm run build
```

### 3. Environment Setup

```bash
cd /var/www/yflow/backend
cp .env.example .env
nano .env  # Edit with production values

# Generate app key
php artisan key:generate
```

### 4. Database Setup

```bash
# Create database
sudo -u postgres psql
CREATE DATABASE yflow_production;
CREATE USER yflow_user WITH PASSWORD 'strong-password';
GRANT ALL PRIVILEGES ON DATABASE yflow_production TO yflow_user;
\q

# Run migrations
php artisan migrate --force

# Seed data (optional)
php artisan db:seed --class=DemoSeeder --force
```

### 5. Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/yflow/backend/storage
sudo chown -R www-data:www-data /var/www/yflow/backend/bootstrap/cache

# Set permissions
sudo chmod -R 775 /var/www/yflow/backend/storage
sudo chmod -R 775 /var/www/yflow/backend/bootstrap/cache
```

### 6. Nginx Configuration

Create `/etc/nginx/sites-available/yflow`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/yflow/frontend/dist;

    # Redirect all traffic to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com www.yourdomain.com;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/yourdomain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/yourdomain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Frontend
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Backend API
    location /api {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # WebSocket
    location /apps {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/yflow /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 7. Supervisor Configuration

Create `/etc/supervisor/conf.d/yflow-worker.conf`:

```ini
[program:yflow-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/yflow/backend/artisan queue:work --tries=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/yflow/backend/storage/logs/worker.log
stopwaitsecs=3600
```

Create `/etc/supervisor/conf.d/yflow-reverb.conf`:

```ini
[program:yflow-reverb]
command=php /var/www/yflow/backend/artisan reverb:start
directory=/var/www/yflow/backend
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/yflow/backend/storage/logs/reverb.log
```

Apply configuration:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start yflow-worker:*
sudo supervisorctl start yflow-reverb
```

### 8. Cron Jobs

```bash
crontab -e

# Add these lines:
* * * * * cd /var/www/yflow/backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## Database Setup

### PostgreSQL Optimization

```sql
-- In postgresql.conf
shared_buffers = 256MB
effective_cache_size = 1GB
maintenance_work_mem = 64MB
max_connections = 100
work_mem = 4MB

-- Create indexes for common queries
CREATE INDEX CONCURRENTLY idx_tasks_project_id ON tasks(project_id);
CREATE INDEX CONCURRENTLY idx_tasks_status ON tasks(status);
CREATE INDEX CONCURRENTLY idx_notifications_recipient ON notifications(recipient_id, is_read);
CREATE INDEX CONCURRENTLY idx_activities_workspace ON activities(workspace_id);
```

### Database Backup Script

Create `/usr/local/bin/yflow-backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/backups/yflow"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="yflow_production"
DB_USER="yflow_user"

mkdir -p $BACKUP_DIR

# Dump database
pg_dump -U $DB_USER -F c -b -v -f "$BACKUP_DIR/db_$DATE.dump" $DB_NAME

# Keep only last 7 days
find $BACKUP_DIR -name "*.dump" -mtime +7 -delete

echo "Backup completed: $BACKUP_DIR/db_$DATE.dump"
```

Make executable and add to cron:
```bash
chmod +x /usr/local/bin/yflow-backup.sh
crontab -e
# Add: 0 2 * * * /usr/local/bin/yflow-backup.sh
```

---

## SSL Configuration

### Let's Encrypt (Certbot)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal test
sudo certbot renew --dry-run
```

### SSL Best Practices

```nginx
# Add to nginx config
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
```

---

## Performance Optimization

### Laravel Optimization

```bash
# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer dump-autoload --optimize --no-dev

# Enable OPcache (in php.ini)
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

### Frontend Optimization

```bash
# Build optimized bundle
npm run build

# Enable gzip compression in nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml;
```

### Database Optimization

```bash
# Analyze tables
php artisan db:show

# Run vacuum analyze
sudo -u postgres psql -d yflow_production -c "VACUUM ANALYZE;"
```

---

## Monitoring & Logging

### Laravel Logging

Configure `config/logging.php` for production:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'bugsnag'],
        'ignore_exceptions' => false,
    ],
],
```

### Log Rotation

Create `/etc/logrotate.d/yflow`:

```
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
        systemctl reload php8.2-fpm
    endscript
}
```

### Health Check Endpoint

Add route in `routes/api.php`:

```php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'redis' => Redis::ping() ? 'connected' : 'disconnected',
    ]);
});
```

---

## Backup Strategy

### Automated Backups

| Type | Frequency | Retention | Location |
|------|-----------|-----------|----------|
| Database | Daily | 7 days | Local + S3 |
| Files | Daily | 7 days | Local + S3 |
| Full System | Weekly | 4 weeks | Off-site |

### Restore Procedure

```bash
# Restore database
pg_restore -U yflow_user -d yflow_production /backups/yflow/db_YYYYMMDD_HHMMSS.dump

# Restore files
rsync -av /backups/yflow/files/ /var/www/yflow/backend/storage/
```

---

## Troubleshooting

### Common Issues

**Queue Worker Not Processing:**
```bash
sudo supervisorctl status yflow-worker
sudo supervisorctl restart yflow-worker:*
tail -f /var/www/yflow/backend/storage/logs/worker.log
```

**WebSocket Connection Failed:**
```bash
sudo supervisorctl status yflow-reverb
sudo supervisorctl restart yflow-reverb
netstat -tlnp | grep 8080
```

**Database Connection Error:**
```bash
sudo systemctl status postgresql
sudo -u postgres psql -c "SELECT 1"
tail -f /var/log/postgresql/postgresql-*.log
```

**Permission Denied:**
```bash
sudo chown -R www-data:www-data /var/www/yflow/backend/storage
sudo chmod -R 775 /var/www/yflow/backend/storage
```

### Debug Mode (Temporary)

```bash
# Enable debug mode temporarily
nano /var/www/yflow/backend/.env
APP_DEBUG=true

# Clear cache
php artisan config:clear

# Remember to disable after debugging!
```

---

## Security Checklist

- [ ] HTTPS enabled with valid SSL certificate
- [ ] `APP_DEBUG=false` in production
- [ ] Strong database passwords
- [ ] Redis password protected
- [ ] Firewall configured (ufw)
- [ ] Fail2ban installed
- [ ] Regular security updates
- [ ] Database backups verified
- [ ] CORS properly configured
- [ ] Rate limiting enabled
- [ ] Session security settings
- [ ] File upload validation
- [ ] SQL injection protection
- [ ] XSS protection headers

---

## Support

For issues or questions:
- Documentation: `/docs` folder
- API Reference: `docs/API_DOCUMENTATION.md`
- Logs: `backend/storage/logs/`