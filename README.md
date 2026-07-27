# YFlow — Agency Operating System (AOS)

> **One Workspace. Every Workflow.**

YFlow is an **AI-native Agency Operating System (AOS)** designed to centralize every aspect of agency operations into a single, unified workspace. It provides a modern platform for managing projects, workflows, teams, clients, finances, knowledge, and business operations with a scalable, enterprise-ready architecture.

---

## Vision

Build a unified operating system that empowers agencies to manage their entire business from one platform while leveraging AI to automate repetitive work, improve decision-making, and increase operational efficiency.

---

## Core Modules

- **Authentication & Authorization** — Secure login, registration, RBAC
- **Dashboard & Analytics** — Real-time insights and metrics
- **Workspace Management** — Multi-workspace architecture
- **Organization Management** — Departments, teams, positions
- **People & Team Management** — Employee profiles, skills, assignments
- **Client Relationship Management (CRM)** — Client tracking and communication
- **Project Management** — Projects with custom workflows
- **Workflow Automation** — Visual workflow builder with stages
- **Task Management** — Task assignment, priorities, progress tracking
- **Calendar & Scheduling** — Events, meetings, milestones
- **Knowledge Base** — Notes and documentation
- **Notifications** — Real-time alerts and updates
- **AI Workspace** — AI-assisted planning and decision-making
- **Finance & Invoicing** — Billing and financial tracking (planned)
- **Reports & Analytics** — Business intelligence (planned)
- **Settings & Administration** — System configuration

---

## Key Features

- **AI-First Experience** — Built-in AI assistance throughout
- **Multi-Workspace Architecture** — Isolated environments per organization
- **Role-Based Access Control (RBAC)** — Granular permissions
- **Enterprise Security** — Bcrypt, Sanctum, policies, validation
- **Real-time Collaboration** — WebSocket-powered updates
- **Modular Architecture** — Feature-based code organization
- **Responsive User Interface** — Mobile-friendly design
- **RESTful API** — Versioned API endpoints
- **Comprehensive Audit Logs** — Activity tracking
- **Notification System** — Email and in-app notifications
- **Extensible Integration Support** — Plugin architecture ready
- **Scalable Foundation** — Docker, PostgreSQL, Redis, queue system

---

## Technology Stack

### Backend

- Laravel 12
- PHP 8.2+
- PostgreSQL
- Redis
- Laravel Reverb (WebSocket)
- Laravel Queue
- Laravel Scheduler
- Laravel Sanctum

### Frontend

- React 19
- Vite
- TypeScript
- React Router
- Tailwind CSS
- shadcn/ui (Radix UI components)
- TanStack Query
- Zustand
- React Hook Form
- Zod validation
- Axios

### Infrastructure

- Docker
- Docker Compose
- Nginx
- GitHub Actions (CI/CD)
- SSL/TLS
- Linux Server

---

## Engineering Principles

YFlow follows a documentation-first and AI-assisted engineering approach.

Core principles include:

- **Documentation before implementation** — Specs first, code second
- **Backend-first development** — API contracts defined upfront
- **Modular architecture** — Separation of concerns
- **Clean code** — Readable, maintainable, testable
- **SOLID principles** — Object-oriented design standards
- **Domain-driven design concepts** — Business logic isolation
- **API-first architecture** — Contract-driven development
- **One module per session** — Focused development
- **Comprehensive testing** — Unit, feature, integration tests
- **Enterprise engineering standards** — Production-ready code

---

## Documentation

The project is guided by a complete engineering documentation set:

| Document | Description |
|----------|-------------|
| [01-vision.md](docs/01-vision.md) | Project vision and goals |
| [02-product-requirements.md](docs/02-product-requirements.md) | Product requirements document |
| [03-information-architecture.md](docs/03-information-architecture.md) | Information architecture |
| [04-domain-model-vol1.md](docs/04-domain-model-vol1.md) | Domain models (core business) |
| [06-business-rules.md](docs/06-business-rules.md) | Business rules and constraints |
| [09-api-specification.md](docs/09-api-specification.md) | Complete API reference |
| [10-backend-architecture.md](docs/10-backend-architecture.md) | Backend technical architecture |
| [11-frontend-architecture.md](docs/11-frontend-architecture.md) | Frontend technical architecture |
| [17-testing-strategy.md](docs/17-testing-strategy.md) | Testing approach and standards |
| [18-deployment.md](docs/18-deployment.md) | Deployment procedures |
| [19-engineering-constitution.md](docs/19-engineering-constitution.md) | Engineering standards |
| [20-git-workflow.md](docs/20-git-workflow.md) | Git branching and commit conventions |
| [21-cicd-automation.md](docs/21-cicd-automation.md) | CI/CD pipeline configuration |
| [22-staging-deployment-guide.md](docs/22-staging-deployment-guide.md) | Staging environment setup |

Additional docs available in `docs/` folder.

---

## Release Strategy

YFlow follows **Semantic Versioning** (SemVer).

### Release Types

| Type | Version Pattern | Description |
|------|-----------------|-------------|
| Patch | v1.0.1 | Bug fixes, security patches |
| Minor | v1.1.0 | New features, backward compatible |
| Major | v2.0.0 | Breaking changes |

### Every Release Includes

- Release notes
- Changelog
- Regression testing results
- Deployment validation checklist
- Rollback strategy

See [VERSION_MANAGEMENT_SPEC.md](docs/VERSION_MANAGEMENT_SPEC.md) for details.

---

## Development Lifecycle

The project follows a structured engineering lifecycle:

1. **Planning & Documentation** — Requirements and specs
2. **Backend Development** — API implementation
3. **Backend QA** — Tests and code review
4. **Frontend Setup** — Component scaffolding
5. **Frontend Development** — UI implementation
6. **Frontend QA** — Tests and accessibility
7. **System Integration** — End-to-end testing
8. **User Acceptance Testing** — Stakeholder validation
9. **Production Deployment** — Release to production
10. **Production Validation (Hypercare)** — Monitoring and fixes
11. **Operations & Monitoring** — Ongoing maintenance
12. **Continuous Improvement** — Iterative enhancements

---

## Project Status

**Current Version:** v1.0.0-rc1

**Status:** 🧊 API FROZEN — Backend Complete, Frontend In Progress

**QA Sign-Off:** [docs/25-qa-signoff-api-freeze.md](docs/25-qa-signoff-api-freeze.md)

### Completed Phases
- ✅ Phase 1: Restructure docs/
- ✅ Phase 2: AI configs (AGENTS.md, CLAUDE.md, etc.)
- ✅ Phase 3: .ai/ memory content
- ✅ Phase 4: Git standardization
- ✅ Phase 5: CI/CD automation + Staging
- ✅ Backend Core Modules (Auth, Workspace, Project, Task, Workflow, People, Releases)
- ✅ Backend QA — 94 tests passing (315 assertions)
- ✅ Security Audit — 0 vulnerabilities
- ✅ API Freeze Declaration

### In Progress
-  Sprint 2: Frontend 8 modules completion
- 🚧 Sprint 3: Frontend QA & Integration Testing

### Upcoming
- ⏳ Sprint 4: System Integration & E2E Tests
-  Sprint 5: User Acceptance Testing (UAT)
- ⏳ Sprint 6: Release Candidate
- ⏳ Sprint 7: Production Deployment
-  Sprint 8: Hypercare → v1.0.0 Launch
cd backend

# Copy environment file
cp .env.example .env

# Start containers
docker compose up -d

# Install dependencies
composer install

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed demo data
php artisan db:seed --class=DemoSeeder

# Start server
php artisan serve

# Start queue worker (new terminal)
php artisan queue:work

# Start WebSocket server (new terminal)
php artisan reverb:start
```

### Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Start dev server
npm run dev
```

### Access Application

- **Frontend:** http://localhost:5173
- **Backend API:** http://localhost:8000
- **Demo Login:** alex@demo.yflow / password

---

## Project Structure

```
YFlow/
├── backend/              # Laravel API
│   ├── app/
│   │   ├── Http/        # Controllers, Requests, Resources
│   │   ├── Models/      # Eloquent models
│   │   ├── Policies/    # Authorization policies
│   │   ├── Services/    # Business logic layer
│   │   ├── Events/      # Domain events
│   │   ── Jobs/        # Queue jobs
│   ├── database/
│   │   ├── migrations/  # Database schema
│   │   ├── seeders/     # Demo data
│   │   └── factories/   # Test data factories
│   ├── routes/          # API routes
│   ├── tests/           # PHPUnit tests
│   └── docker/          # Docker configurations
├── frontend/            # React application
│   ├── src/
│   │   ├── features/    # Feature-based modules
│   │   ├── components/  # Reusable UI components
│   │   ├── providers/   # Context providers
│   │   ├── stores/      # State management (Zustand)
│   │   ├── services/    # API service layer
│   │   └── hooks/       # Custom React hooks
│   └── public/
└── docs/                # Engineering documentation
    ├── phases/          # Phase documentation
    ├── prompts/         # AI agent prompts
    ├── templates/       # Issue/PR templates
    └── .ai/             # AI memory
```

---

## API Endpoints

### Authentication
- `POST /api/v1/auth/register` — Register new user
- `POST /api/v1/auth/login` — Login
- `POST /api/v1/auth/logout` — Logout
- `GET /api/v1/auth/me` — Get current user

### Workspaces
- `GET /api/v1/workspaces` — List workspaces
- `POST /api/v1/workspaces` — Create workspace
- `GET /api/v1/workspaces/{id}` — Get workspace
- `PUT /api/v1/workspaces/{id}` — Update workspace
- `DELETE /api/v1/workspaces/{id}` — Delete workspace

### Projects
- `GET /api/v1/projects` — List projects
- `POST /api/v1/projects` — Create project
- `GET /api/v1/projects/{id}` — Get project
- `PUT /api/v1/projects/{id}` — Update project
- `DELETE /api/v1/projects/{id}` — Delete project

### Tasks
- `GET /api/v1/tasks` — List tasks
- `POST /api/v1/tasks` — Create task
- `GET /api/v1/tasks/{id}` — Get task
- `PUT /api/v1/tasks/{id}` — Update task
- `DELETE /api/v1/tasks/{id}` — Delete task

### People
- `GET /api/v1/people` — List people
- `POST /api/v1/people` — Create person
- `GET /api/v1/people/{id}` — Get person
- `PUT /api/v1/people/{id}` — Update person
- `DELETE /api/v1/people/{id}` — Delete person

### Workflows
- `GET /api/v1/workflows` — List workflows
- `POST /api/v1/workflows` — Create workflow
- `GET /api/v1/workflows/{id}` — Get workflow
- `PUT /api/v1/workflows/{id}` — Update workflow
- `DELETE /api/v1/workflows/{id}` — Delete workflow

See [API_DOCUMENTATION.md](docs/API_DOCUMENTATION.md) for complete reference.

---

## Demo Data

The included seeder creates:

- 1 Workspace
- 3 Departments (Design, Development, Marketing)
- 5 Team members
- 4 Projects with workflows
- 25-50 Tasks with assignees
- Notes, events, notifications, and AI conversations

**Demo Login:** alex@demo.yflow / password

---

## Testing

### Backend Tests

```bash
cd backend

# Run all tests
php artisan test

# Run specific test
php artisan test --filter=ProjectTest

# Run with coverage
php artisan test --coverage
```

### Frontend Tests

```bash
cd frontend

# Run tests
npm run test

# Run with coverage
npm run test:coverage
```

---

## Deployment

See [Deployment Guide](docs/DEPLOYMENT.md) and [Staging Guide](docs/22-staging-deployment-guide.md) for:

- Docker production setup
- Traditional server deployment
- SSL configuration
- Performance optimization
- Backup strategy
- CI/CD automation

---

## Security

- Bcrypt password hashing
- Sanctum token authentication
- Policy-based authorization
- Input validation (Form Requests)
- CORS protection
- Rate limiting
- SQL injection prevention (Eloquent ORM)
- XSS protection
- CSRF protection
- Security headers middleware

---

## License

**Proprietary Software**

Copyright © 2026 YFlow. All rights reserved.

Unauthorized copying, distribution, modification, or commercial use is prohibited without written permission.

---

## Support

For issues or questions:

- Check [API Documentation](docs/API_DOCUMENTATION.md)
- Review [Deployment Guide](docs/DEPLOYMENT.md)
- Read [Engineering Constitution](docs/19-engineering-constitution.md)
- Check logs: `backend/storage/logs/`
- Review tests: `backend/tests/`

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*