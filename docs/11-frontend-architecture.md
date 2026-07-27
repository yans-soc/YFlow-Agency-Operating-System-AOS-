# Frontend Architecture — YFlow

## Stack
- **Framework**: React 18
- **Language**: TypeScript
- **Build**: Vite
- **Styling**: Tailwind CSS
- **Components**: Shadcn/ui

## State Management
- **Server State**: TanStack Query
- **Global State**: Zustand
- **Form State**: React Hook Form

## Architecture Pattern
Feature-based structure:
```
src/
── features/     # Feature modules
├── components/   # Shared components
── services/     # API services
├── stores/       # Global stores
├── hooks/        # Custom hooks
└── providers/    # Context providers
```

## Key Patterns
- Functional components with hooks
- Custom hooks for data fetching
- Compound components for complex UI
- Render props for reusable logic

## Routing
- React Router v6
- Protected routes with auth guard
- Lazy loading for code splitting

## API Integration
- Axios for HTTP requests
- Interceptors for auth/token refresh
- Typed API responses

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*