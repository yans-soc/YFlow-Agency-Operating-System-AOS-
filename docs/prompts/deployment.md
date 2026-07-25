# Deployment Prompt — YFlow

## Purpose
Guide AI agents when working on deployment and infrastructure tasks.

---

## Pre-Deployment Checklist

- [ ] All tests passing
- [ ] Code coverage >= 80%
- [ ] Documentation updated
- [ ] Migration files created
- [ ] Environment variables documented
- [ ] Release version bumped

---

## Deployment Steps

### Backend
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend
```bash
npm ci
npm run build
```

---

## Environment Variables

### Backend (.env)
```
APP_ENV=production
APP_DEBUG=false
DB_HOST=...
DB_DATABASE=...
```

### Frontend (.env)
```
VITE_API_URL=https://api.yflow.com
```

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*