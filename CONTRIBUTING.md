# Contributing to YFlow

Thank you for contributing to YFlow – Agency Operating System.

## Getting Started

### Prerequisites
- PHP 8.4+
- Node.js 20+
- MySQL 8.0 or PostgreSQL 17
- Redis

### Setup Backend
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan test
```

### Setup Frontend
```bash
cd frontend
npm install
npm run dev
```

## Development Workflow

### 1. Read Documentation First
Before making changes, read:
- `docs/01-vision.md` - Product vision
- `docs/06-business-rules.md` - Business logic
- `docs/09-api-specification.md` - API contract
- `docs/19-engineering-constitution.md` - Engineering principles

### 2. Create Branch
```bash
git checkout develop
git pull
git checkout -b feature/your-feature-name
```

### 3. Make Changes
- Follow coding standards
- Add tests for new code
- Update documentation

### 4. Commit
```bash
git commit -m "feat(scope): description"
```

### 5. Push and Create PR
```bash
git push origin feature/your-feature-name
```

## Coding Standards

### Backend (PHP)
- PSR-12 coding style
- Type hints required
- PHPDoc for public methods
- Repository pattern for data access
- Service layer for business logic

### Frontend (TypeScript)
- Strict TypeScript
- Functional components with hooks
- ESLint + Prettier enforced
- Tailwind CSS for styling

## Testing
- Backend: `cd backend && php artisan test`
- Frontend: `cd frontend && npm test`
- Minimum 80% coverage for new code

## Pull Request Checklist
- [ ] Code follows standards
- [ ] Tests added and passing
- [ ] Documentation updated
- [ ] Conventional commits used
- [ ] Linked related issues

## Questions?
Check `docs/` folder or create an issue.

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*