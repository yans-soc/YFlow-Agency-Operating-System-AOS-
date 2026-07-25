# Backend Development Prompt — YFlow

## Purpose
Guide AI agents when working on backend (Laravel/PHP) development tasks.

---

## Pre-Development Checklist

Before writing any code:

1. **Read Documentation**
   - `docs/01-vision.md` — Understand product vision
   - `docs/06-business-rules.md` — Check business logic constraints
   - `docs/08-erd.md` — Review database schema
   - `docs/09-api-specification.md` — Check API patterns
   - `docs/10-backend-architecture.md` — Follow architecture guidelines

2. **Check Existing Code**
   - Review similar controllers/models/services
   - Follow established patterns
   - Check naming conventions

3. **Plan Implementation**
   - Identify required migrations
   - List new models/resources needed
   - Define API endpoints
   - Plan tests

---

## Development Workflow

### 1. Database Layer
```bash
# Create migration
php artisan make:migration create_x_table

# Create model
php artisan make:model ModelName

# Create factory
php artisan make:factory ModelFactory --model=ModelName
```

**Requirements:**
- UUID primary keys
- Timestamps
- Soft deletes if applicable
- Proper indexes
- Foreign key constraints

### 2. API Layer
```bash
# Create controller
php artisan make:controller Api/EntityController --api

# Create request
php artisan make:request Entity/StoreEntityRequest

# Create resource
php artisan make:resource EntityResource
```

**Requirements:**
- Type hints on all methods
- Form Request validation
- API Resource responses
- Policy authorization
- Proper HTTP status codes

### 3. Business Logic Layer
```bash
# Create service
php artisan make:service EntityService
```

**Requirements:**
- Service layer for complex logic
- Repository pattern for data access
- Dependency injection
- No business logic in controllers

### 4. Authorization Layer
```bash
# Create policy
php artisan make:policy EntityPolicy --model=Entity
```

**Requirements:**
- Policy for every model
- Check permissions before actions
- Return proper error messages

### 5. Testing Layer
```bash
# Create feature test
php artisan make:test Feature/EntityTest

# Create unit test
php artisan make:test Unit/EntityServiceTest
```

**Requirements:**
- Feature tests for API endpoints
- Unit tests for services
- Minimum 80% coverage
- All tests must pass

---

## Code Standards

### Controllers
```php
class ProjectController extends Controller
{
    public function index(ProjectService $service): ProjectCollection
    {
        $this->authorize('viewAny', Project::class);
        return ProjectCollection::make($service->all());
    }
    
    public function store(StoreProjectRequest $request, ProjectService $service): ProjectResource
    {
        $this->authorize('create', Project::class);
        $project = $service->create($request->validated());
        return new ProjectResource($project);
    }
}
```

### Models
```php
class Project extends Model
{
    use HasFactory, HasUuids;
    
    protected $fillable = ['name', 'code', 'status'];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }
}
```

### Requests
```php
class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy handles authorization
    }
    
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'unique:projects,code'],
        ];
    }
}
```

### Resources
```php
class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

---

## Testing Requirements

### Feature Test Example
```php
class ProjectTest extends TestCase
{
    public function test_can_create_project(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'Test Project',
            'code' => 'TEST-001',
        ]);
        
        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Project');
    }
}
```

---

## Post-Development Checklist

After completing implementation:

1. **Run Tests**
   ```bash
   php artisan test
   ```

2. **Update Documentation**
   - Update `docs/09-api-specification.md` with new endpoints
   - Update `docs/08-erd.md` if schema changed

3. **Code Quality**
   ```bash
   composer pint          # Format code
   composer phpstan       # Static analysis
   ```

4. **Verify**
   - All tests passing
   - No PHPStan errors
   - Documentation matches implementation

---

## Common Patterns

### Repository Pattern
```php
interface ProjectRepositoryInterface
{
    public function all(): Collection;
    public function find(string $id): ?Project;
    public function create(array $data): Project;
    public function update(Project $project, array $data): bool;
    public function delete(Project $project): bool;
}

class ProjectRepository implements ProjectRepositoryInterface
{
    public function all(): Collection
    {
        return Project::with('members')->get();
    }
    // ...
}
```

### Service Pattern
```php
class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $repository
    ) {}
    
    public function create(array $data): Project
    {
        $project = $this->repository->create($data);
        event(new ProjectCreated($project));
        return $project;
    }
}
```

---

*Document Version: 1.0*
*Last Updated: 2026-07-25*