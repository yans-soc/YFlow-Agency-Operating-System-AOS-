# QA Testing Prompt — YFlow

## Purpose
Guide AI agents when writing tests and performing quality assurance.

---

## Testing Strategy

### Backend (PHPUnit)
- Feature tests for API endpoints
- Unit tests for services
- Policy tests for authorization
- Minimum 80% coverage

### Frontend (Vitest + RTL)
- Component rendering tests
- Hook tests
- Integration tests
- User flow tests

---

## Test Structure

### Backend Feature Test
```php
public function test_can_create_project(): void
{
    $user = User::factory()->create();
    $response = $this->actingAs($user)->postJson('/api/v1/projects', [
        'name' => 'Test Project',
        'code' => 'TEST-001',
    ]);
    $response->assertStatus(201);
}
```

### Frontend Component Test
```typescript
describe('ProjectCard', () => {
  it('renders project name', () => {
    render(<ProjectCard project={mockProject} onClick={vi.fn()} />);
    expect(screen.getByText(mockProject.name)).toBeInTheDocument();
  });
});
```

---

## QA Checklist

- [ ] All tests passing
- [ ] Code coverage >= 80%
- [ ] No PHPStan errors
- [ ] No ESLint errors
- [ ] Documentation updated
- [ ] API spec matches implementation

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*