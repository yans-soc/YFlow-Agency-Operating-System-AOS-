# Testing Strategy — YFlow

## Backend Testing (PHPUnit)

### Feature Tests
- API endpoint tests
- Authentication/authorization
- Full request lifecycle

### Unit Tests
- Service layer
- Repository layer
- Utility classes

### Policy Tests
- Authorization rules
- Permission checks

### Coverage Target
- Minimum 80% for new code
- Critical paths: 90%+

---

## Frontend Testing (Vitest + RTL)

### Component Tests
- Rendering
- User interactions
- Props validation

### Hook Tests
- Custom hook logic
- State management

### Integration Tests
- Component composition
- Data fetching

---

## CI/CD Integration
- Tests run on every PR
- Block merge on failure
- Coverage reporting

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*