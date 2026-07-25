# Deployment Guide — YFlow

## Infrastructure
- **Server**: Ubuntu 22.04 LTS
- **Web Server**: Nginx
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Queue**: Redis

## Backend Deployment
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Frontend Deployment
```bash
npm ci
npm run build
# Deploy dist/ to static server
```

## Environment Variables
See `.env.example` for required variables.

## CI/CD
- GitHub Actions for automated testing
- Manual deployment approval
- Rollback capability

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*