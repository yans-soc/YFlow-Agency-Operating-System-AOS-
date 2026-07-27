# Backend Architecture — YFlow

## Stack
- **Framework**: Laravel 11
- **PHP**: 8.4
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Queue**: Redis

## Architecture Pattern
Layered Architecture:
```
Controllers → Services → Repositories → Models
```

## Key Components

### Controllers
- RESTful API endpoints
- Request validation
- Authorization checks
- Resource responses

### Services
- Business logic
- Orchestration layer
- External integrations

### Repositories
- Data access abstraction
- Query building
- Eloquent wrapper

### Models
- Entity representation
- Relationships
- Accessors/mutators

## Middleware
- Authentication
- Authorization
- CORS
- Rate limiting
- Security headers

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*