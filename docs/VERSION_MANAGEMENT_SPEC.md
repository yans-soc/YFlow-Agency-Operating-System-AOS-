# YFlow — Version Management Feature Specification

**Version:** 1.0.0  
**Status:** Planning Complete  
**Owner:** Backend & Frontend Team  
**Target Release:** v1.0.1 (Q3 2026)

---

## 1. Overview

### 1.1 Purpose
Provide an Admin Dashboard feature to manage application version metadata (version string, release notes, release date) for display to end users — **without** touching Git tags, `package.json`, `composer.json`, or triggering deployments.

### 1.2 Scope
| In Scope | Out of Scope |
|----------|--------------|
| CRUD releases via Admin Dashboard | Git tag creation |
| Public API `/api/version/current` | `package.json` / `composer.json` version bump |
| Display version in UI (header, footer, about) | CI/CD pipeline trigger |
| Release notes history page | Deployment automation |
| Only one "current" release at a time | Semantic versioning enforcement (admin decides) |

---

## 2. Data Model

### 2.1 Migration
```php
// database/migrations/2026_07_25_000001_create_releases_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('version', 20)->unique();           // "1.0.1"
            $table->text('release_notes')->nullable();         // Markdown supported
            $table->date('released_at');                       // Tanggal rilis
            $table->boolean('is_current')->default(false);     // Hanya 1 yang true
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['is_current', 'released_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};
```

### 2.2 Model
```php
// app/Models/Release.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Release extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'release_notes',
        'released_at',
        'is_current',
        'created_by',
    ];

    protected $casts = [
        'released_at' => 'date',
        'is_current' => 'boolean',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('released_at');
    }

    // Events
    protected static function booted(): void
    {
        static::saving(function (Release $release) {
            if ($release->is_current) {
                // Unset other current releases
                static::where('is_current', true)
                    ->where('id', '!=', $release->id)
                    ->update(['is_current' => false]);
            }
        });
    }

    // Accessors
    public function getFormattedVersionAttribute(): string
    {
        return "v{$this->version}";
    }
}
```

### 2.3 Factory & Seeder
```php
// database/factories/ReleaseFactory.php
<?php

namespace Database\Factories;

use App\Models\Release;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReleaseFactory extends Factory
{
    protected $model = Release::class;

    public function definition(): array
    {
        return [
            'version' => $this->faker->unique()->regexify('1.0.[0-9]'),
            'release_notes' => $this->faker->optional()->paragraphs(3, true),
            'released_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'is_current' => false,
            'created_by' => User::factory(),
        ];
    }

    public function current(): static
    {
        return $this->state(['is_current' => true]);
    }
}
```

```php
// database/seeders/ReleaseSeeder.php
<?php

namespace Database\Seeders;

use App\Models\Release;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReleaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@yflow.local')->first();

        Release::create([
            'version' => '1.0.0',
            'release_notes' => <<<MD
# YFlow v1.0.0 - Initial Release

## Features
- Workspace & Project management
- Task boards with Kanban workflow
- Team collaboration
- AI-assisted planning
MD,
            'released_at' => now()->subMonths(2),
            'is_current' => false,
            'created_by' => $admin?->id,
        ]);

        Release::create([
            'version' => '1.0.1',
            'release_notes' => <<<MD
# YFlow v1.0.1 - Stability & Bug Fixes

## Fixed
- Task drag-and-drop on mobile
- Notification email delivery
- Calendar event timezone handling

## Improved
- Dashboard load time (-40%)
MD,
            'released_at' => now()->subWeek(),
            'is_current' => true,
            'created_by' => $admin?->id,
        ]);
    }
}
```

---

## 3. Backend API

### 3.1 Routes
```php
// routes/api.php (add to existing)
Route::prefix('releases')->middleware(['auth:sanctum', 'ability:admin'])->group(function () {
    Route::get('/', [ReleaseController::class, 'index']);
    Route::post('/', [ReleaseController::class, 'store']);
    Route::get('/{release}', [ReleaseController::class, 'show']);
    Route::put('/{release}', [ReleaseController::class, 'update']);
    Route::delete('/{release}', [ReleaseController::class, 'destroy']);
    Route::post('/{release}/set-current', [ReleaseController::class, 'setCurrent']);
});

// Public endpoint (no auth required)
Route::get('/version/current', [ReleaseController::class, 'current']);
```

### 3.2 Requests
```php
// app/Http/Requests/Release/StoreReleaseRequest.php
<?php

namespace App\Http\Requests\Release;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-releases');
    }

    public function rules(): array
    {
        return [
            'version' => ['required', 'string', 'max:20', 'regex:/^\d+\.\d+\.\d+(-[a-z0-9]+)?$/', 'unique:releases,version'],
            'release_notes' => ['nullable', 'string', 'max:10000'],
            'released_at' => ['required', 'date', 'before_or_equal:today'],
            'is_current' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'version.regex' => 'Version must follow semantic versioning (e.g., 1.0.1 or 1.0.1-beta).',
        ];
    }
}
```

```php
// app/Http/Requests/Release/UpdateReleaseRequest.php
<?php

namespace App\Http\Requests\Release;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReleaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage-releases');
    }

    public function rules(): array
    {
        $releaseId = $this->route('release')->id;

        return [
            'version' => ['sometimes', 'string', 'max:20', 'regex:/^\d+\.\d+\.\d+(-[a-z0-9]+)?$/', Rule::unique('releases')->ignore($releaseId)],
            'release_notes' => ['nullable', 'string', 'max:10000'],
            'released_at' => ['sometimes', 'date', 'before_or_equal:today'],
            'is_current' => ['boolean'],
        ];
    }
}
```

### 3.3 Resource
```php
// app/Http/Resources/ReleaseResource.php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReleaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'version' => $this->version,
            'formatted_version' => $this->formatted_version,
            'release_notes' => $this->release_notes,
            'release_notes_html' => $this->whenLoaded('releaseNotesHtml'), // optional: parsed markdown
            'released_at' => $this->released_at?->format('Y-m-d'),
            'is_current' => $this->is_current,
            'created_by' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
            ],
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
```

```php
// app/Http/Resources/CurrentVersionResource.php (public endpoint)
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrentVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'version' => $this->version,
            'formatted_version' => $this->formatted_version,
            'released_at' => $this->released_at?->format('Y-m-d'),
            'release_notes' => $this->release_notes,
        ];
    }
}
```

### 3.4 Controller
```php
// app/Http/Controllers/Api/ReleaseController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Release\StoreReleaseRequest;
use App\Http\Requests\Release\UpdateReleaseRequest;
use App\Http\Resources\CurrentVersionResource;
use App\Http\Resources\ReleaseResource;
use App\Models\Release;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReleaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $releases = Release::with('creator')
            ->latest('released_at')
            ->paginate($request->integer('per_page', 15));

        return ReleaseResource::collection($releases)->response();
    }

    public function store(StoreReleaseRequest $request): JsonResponse
    {
        $release = Release::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return ReleaseResource::make($release->load('creator'))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Release $release): JsonResponse
    {
        return ReleaseResource::make($release->load('creator'))->response();
    }

    public function update(UpdateReleaseRequest $request, Release $release): JsonResponse
    {
        $release->update($request->validated());

        return ReleaseResource::make($release->load('creator'))->response();
    }

    public function destroy(Release $release): JsonResponse
    {
        // Prevent deleting current release
        if ($release->is_current) {
            return response()->json([
                'message' => 'Cannot delete the current release. Set another release as current first.',
            ], 422);
        }

        $release->delete();

        return response()->json(null, 204);
    }

    public function setCurrent(Release $release): JsonResponse
    {
        $release->update(['is_current' => true]);

        return ReleaseResource::make($release->load('creator'))->response();
    }

    // Public endpoint
    public function current(): JsonResponse
    {
        $release = Release::current()->firstOrFail();

        return CurrentVersionResource::make($release)->response();
    }
}
```

### 3.5 Policy
```php
// app/Policies/ReleasePolicy.php
<?php

namespace App\Policies;

use App\Models\Release;
use App\Models\User;

class ReleasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, Release $release): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Release $release): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Release $release): bool
    {
        return $user->hasRole('admin') && !$release->is_current;
    }

    public function setCurrent(User $user, Release $release): bool
    {
        return $user->hasRole('admin');
    }
}
```

Register in `AuthServiceProvider`:
```php
protected $policies = [
    Release::class => ReleasePolicy::class,
];
```

---

## 4. Frontend

### 4.1 Types
```typescript
// src/features/admin/types/release.ts
export interface Release {
  id: number;
  version: string;
  formatted_version: string;
  release_notes: string | null;
  release_notes_html?: string;
  released_at: string; // YYYY-MM-DD
  is_current: boolean;
  created_by: {
    id: number;
    name: string;
  };
  created_at: string;
  updated_at: string;
}

export interface CurrentVersion {
  version: string;
  formatted_version: string;
  released_at: string;
  release_notes: string | null;
}

export interface ReleaseFormData {
  version: string;
  release_notes: string;
  released_at: string;
  is_current: boolean;
}
```

### 4.2 API Service
```typescript
// src/features/admin/services/release.ts
import { api } from '@/lib/api';
import type { Release, ReleaseFormData, CurrentVersion } from '../types/release';

export const releaseService = {
  list: (params?: { page?: number; per_page?: number }) =>
    api.get<{ data: Release[]; meta: any }>('/releases', { params }),

  get: (id: number) =>
    api.get<Release>(`/releases/${id}`),

  create: (data: ReleaseFormData) =>
    api.post<Release>('/releases', data),

  update: (id: number, data: Partial<ReleaseFormData>) =>
    api.put<Release>(`/releases/${id}`, data),

  delete: (id: number) =>
    api.delete(`/releases/${id}`),

  setCurrent: (id: number) =>
    api.post<Release>(`/releases/${id}/set-current`),

  // Public
  getCurrentVersion: () =>
    api.get<CurrentVersion>('/version/current'),
};
```

### 4.3 Hooks
```typescript
// src/features/admin/hooks/useReleases.ts
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { releaseService } from '../services/release';
import type { Release, ReleaseFormData } from '../types/release';

export function useReleases(page = 1, perPage = 15) {
  return useQuery({
    queryKey: ['releases', page, perPage],
    queryFn: () => releaseService.list({ page, per_page: perPage }),
  });
}

export function useRelease(id: number) {
  return useQuery({
    queryKey: ['release', id],
    queryFn: () => releaseService.get(id),
    enabled: !!id,
  });
}

export function useCreateRelease() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (data: ReleaseFormData) => releaseService.create(data),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['releases'] }),
  });
}

export function useUpdateRelease() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<ReleaseFormData> }) =>
      releaseService.update(id, data),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['releases'] }),
  });
}

export function useDeleteRelease() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => releaseService.delete(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['releases'] }),
  });
}

export function useSetCurrentRelease() {
  const qc = useQueryClient();
  return useMutation({
    mutationFn: (id: number) => releaseService.setCurrent(id),
    onSuccess: () => qc.invalidateQueries({ queryKey: ['releases'] }),
  });
}

// Public hook for UI version display
export function useCurrentVersion() {
  return useQuery({
    queryKey: ['currentVersion'],
    queryFn: () => releaseService.getCurrentVersion(),
    staleTime: 5 * 60 * 1000, // 5 min cache
  });
}
```

### 4.4 Components

#### Release List Page
```tsx
// src/features/admin/ReleaseManagementPage.tsx
import { useState } from 'react';
import { useReleases, useDeleteRelease, useSetCurrentRelease } from '../hooks/useReleases';
import { ReleaseFormModal } from './ReleaseFormModal';
import { DataTable } from '@/components/ui/data-table';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { format } from 'date-fns';
import { Plus, Trash2, CheckCircle2 } from 'lucide-react';

export function ReleaseManagementPage() {
  const [page, setPage] = useState(1);
  const [modalOpen, setModalOpen] = useState(false);
  const [editingRelease, setEditingRelease] = useState<Release | null>(null);

  const { data, isLoading } = useReleases(page);
  const deleteMutation = useDeleteRelease();
  const setCurrentMutation = useSetCurrentRelease();

  const columns = [
    { accessorKey: 'formatted_version', header: 'Version' },
    { accessorKey: 'released_at', header: 'Released', cell: ({ row }) => format(new Date(row.getValue('released_at')), 'dd MMM yyyy') },
    { accessorKey: 'is_current', header: 'Current', cell: ({ row }) => row.getValue('is_current') ? <Badge variant="default">✓ Current</Badge> : <Badge variant="secondary">—</Badge> },
    { accessorKey: 'created_by.name', header: 'Created By' },
    {
      id: 'actions',
      header: 'Actions',
      cell: ({ row }) => {
        const release = row.original;
        return (
          <div className="flex items-center gap-2">
            <Button variant="ghost" size="sm" onClick={() => { setEditingRelease(release); setModalOpen(true); }}>
              Edit
            </Button>
            {!release.is_current && (
              <Button variant="ghost" size="sm" onClick={() => setCurrentMutation.mutate(release.id)} disabled={setCurrentMutation.isPending}>
                <CheckCircle2 className="w-4 h-4 mr-1" /> Set Current
              </Button>
            )}
            {!release.is_current && (
              <Button variant="ghost" size="sm" className="text-red-600 hover:text-red-700" onClick={() => deleteMutation.mutate(release.id)} disabled={deleteMutation.isPending}>
                <Trash2 className="w-4 h-4" />
              </Button>
            )}
          </div>
        );
      },
    },
  ];

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Release Management</h1>
        <Button onClick={() => { setEditingRelease(null); setModalOpen(true); }}>
          <Plus className="w-4 h-4 mr-2" /> New Release
        </Button>
      </div>

      <DataTable
        data={data?.data ?? []}
        columns={columns}
        isLoading={isLoading}
        pagination={{
          page,
          pageCount: data?.meta?.last_page ?? 1,
          onPageChange: setPage,
        }}
      />

      <ReleaseFormModal
        open={modalOpen}
        onClose={() => { setModalOpen(false); setEditingRelease(null); }}
        initialData={editingRelease ?? undefined}
      />
    </div>
  );
}
```

#### Release Form Modal
```tsx
// src/features/admin/ReleaseFormModal.tsx
import { useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { useCreateRelease, useUpdateRelease } from '../hooks/useReleases';
import type { Release, ReleaseFormData } from '../types/release';

const schema = z.object({
  version: z.string().regex(/^\d+\.\d+\.\d+(-[a-z0-9]+)?$/, 'Format: 1.0.1 or 1.0.1-beta'),
  release_notes: z.string().max(10000).optional(),
  released_at: z.string().min(1, 'Required'),
  is_current: z.boolean(),
});

type FormData = z.infer<typeof schema>;

interface Props {
  open: boolean;
  onClose: () => void;
  initialData?: Release;
}

export function ReleaseFormModal({ open, onClose, initialData }: Props) {
  const isEditing = !!initialData;
  const createMutation = useCreateRelease();
  const updateMutation = useUpdateRelease();

  const form = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: {
      version: '',
      release_notes: '',
      released_at: new Date().toISOString().split('T')[0],
      is_current: false,
    },
  });

  useEffect(() => {
    if (initialData) {
      form.reset({
        version: initialData.version,
        release_notes: initialData.release_notes ?? '',
        released_at: initialData.released_at,
        is_current: initialData.is_current,
      });
    } else {
      form.reset({
        version: '',
        release_notes: '',
        released_at: new Date().toISOString().split('T')[0],
        is_current: false,
      });
    }
  }, [initialData, form]);

  const onSubmit = async (data: FormData) => {
    try {
      if (isEditing) {
        await updateMutation.mutateAsync({ id: initialData!.id, data });
      } else {
        await createMutation.mutateAsync(data);
      }
      onClose();
    } catch (e) {
      // Error handled by react-query toast
    }
  };

  return (
    <Dialog open={open} onOpenChange={onClose}>
      <DialogContent className="max-w-2xl">
        <DialogHeader>
          <DialogTitle>{isEditing ? 'Edit Release' : 'New Release'}</DialogTitle>
        </DialogHeader>
        <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4 py-4">
          <div className="grid gap-4">
            <div className="space-y-2">
              <Label htmlFor="version">Version *</Label>
              <Input
                id="version"
                {...form.register('version')}
                placeholder="1.0.1"
                disabled={isEditing}
              />
              {form.formState.errors.version && (
                <p className="text-sm text-red-500">{form.formState.errors.version.message}</p>
              )}
            </div>

            <div className="space-y-2">
              <Label htmlFor="released_at">Release Date *</Label>
              <Input
                id="released_at"
                type="date"
                {...form.register('released_at')}
                max={new Date().toISOString().split('T')[0]}
              />
            </div>

            <div className="space-y-2">
              <Label htmlFor="release_notes">Release Notes (Markdown)</Label>
              <Textarea
                id="release_notes"
                {...form.register('release_notes')}
                rows={6}
                placeholder="# What's New\n## Features\n- ...\n## Fixed\n- ..."
              />
            </div>

            <div className="flex items-center gap-2">
              <Checkbox
                id="is_current"
                checked={form.watch('is_current')}
                onCheckedChange={form.setValue('is_current')}
              />
              <Label htmlFor="is_current">Set as current version</Label>
            </div>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" onClick={onClose} disabled={createMutation.isPending || updateMutation.isPending}>
              Cancel
            </Button>
            <Button type="submit" disabled={createMutation.isPending || updateMutation.isPending}>
              {createMutation.isPending || updateMutation.isPending ? 'Saving...' : (isEditing ? 'Update' : 'Create')}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>
  );
}
```

### 4.5 Version Display Hook & Components
```typescript
// src/hooks/useVersion.ts
import { useCurrentVersion } from '@/features/admin/hooks/useReleases';

export function useVersion() {
  const { data, isLoading } = useCurrentVersion();

  return {
    version: data?.formatted_version ?? 'v1.0.0',
    releasedAt: data?.released_at,
    releaseNotes: data?.release_notes,
    isLoading,
  };
}
```

```tsx
// src/components/ui/VersionBadge.tsx
import { useVersion } from '@/hooks/useVersion';

export function VersionBadge({ className = '' }: { className?: string }) {
  const { version, isLoading } = useVersion();

  if (isLoading) return <span className={`text-xs text-muted-foreground ${className}`}>v—</span>;

  return (
    <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-muted ${className}`}>
      {version}
    </span>
  );
}
```

```tsx
// src/features/about/ChangelogPage.tsx
import { useReleases } from '@/features/admin/hooks/useReleases';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Markdown } from '@/components/ui/markdown';
import { format } from 'date-fns';

export function ChangelogPage() {
  const { data } = useReleases(1, 50);

  return (
    <div className="max-w-3xl mx-auto space-y-6 py-8">
      <h1 className="text-3xl font-bold">Changelog</h1>
      {data?.data.map((release) => (
        <Card key={release.id} className={release.is_current ? 'ring-2 ring-primary' : ''}>
          <CardHeader>
            <div className="flex items-baseline gap-3">
              <CardTitle className="text-xl">{release.formatted_version}</CardTitle>
              <span className="text-muted-foreground">{format(new Date(release.released_at), 'dd MMMM yyyy')}</span>
              {release.is_current && <span className="text-xs bg-primary/10 text-primary px-2 py-0.5 rounded">Current</span>}
            </div>
          </CardHeader>
          <CardContent>
            {release.release_notes && <Markdown source={release.release_notes} />}
          </CardContent>
        </Card>
      ))}
    </div>
  );
}
```

---

## 5. Integration Points

### 5.1 Header / Footer
```tsx
// src/features/workspace/Header.tsx (add version)
import { VersionBadge } from '@/components/ui/VersionBadge';

export function Header() {
  return (
    <header className="flex items-center justify-between px-4 py-3 border-b">
      <div className="flex items-center gap-4">
        <Logo />
        <VersionBadge />
      </div>
      {/* ... user menu */}
    </header>
  );
}
```

### 5.2 About / Settings Page
```tsx
// src/features/workspace/SettingsPage.tsx (add version info)
import { useVersion } from '@/hooks/useVersion';

export function SettingsPage() {
  const { version, releasedAt, releaseNotes } = useVersion();

  return (
    <section>
      <h2>About YFlow</h2>
      <p>Version: {version}</p>
      <p>Released: {releasedAt}</p>
      {releaseNotes && <div className="prose max-w-none">{releaseNotes}</div>}
    </section>
  );
}
```

---

## 6. Navigation & Permissions

### 6.1 Sidebar Menu (Admin only)
```tsx
// src/features/workspace/Sidebar.tsx
const adminNav = [
  { label: 'Releases', href: '/admin/releases', icon: Tag, permission: 'manage-releases' },
];
```

### 6.2 Route Protection
```tsx
// src/App.tsx
<Route path="/admin/releases" element={
  <ProtectedRoute permission="manage-releases">
    <ReleaseManagementPage />
  </ProtectedRoute>
} />
```

---

## 7. Testing

### 7.1 Backend Tests
```php
// tests/Feature/ReleaseTest.php
<?php

namespace Tests\Feature;

use App\Models\Release;
use App\Models\User;
use Tests\TestCase;

class ReleaseTest extends TestCase
{
    public function test_admin_can_create_release(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/releases', [
                'version' => '1.0.2',
                'release_notes' => '# Bug fixes',
                'released_at' => now()->toDateString(),
                'is_current' => true,
            ]);

        $response->assertCreated()
            ->assertJsonPath('version', '1.0.2');

        $this->assertDatabaseHas('releases', [
            'version' => '1.0.2',
            'is_current' => true,
        ]);
    }

    public function test_only_one_current_release(): void
    {
        $admin = User::factory()->admin()->create();
        Release::factory()->current()->create();
        Release::factory()->create(['version' => '1.0.0']);

        $this->actingAs($admin, 'sanctum')
            ->postJson('/api/releases', [
                'version' => '1.0.1',
                'released_at' => now()->toDateString(),
                'is_current' => true,
            ]);

        $this->assertEquals(1, Release::where('is_current', true)->count());
    }

    public function test_public_current_version_endpoint(): void
    {
        Release::factory()->current()->create(['version' => '1.0.1']);

        $response = $this->getJson('/api/version/current');

        $response->assertOk()
            ->assertJsonPath('version', '1.0.1');
    }

    public function test_cannot_delete_current_release(): void
    {
        $admin = User::factory()->admin()->create();
        $release = Release::factory()->current()->create();

        $response = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/releases/{$release->id}");

        $response->assertStatus(422);
    }
}
```

### 7.2 Frontend Tests
```tsx
// src/features/admin/__tests__/ReleaseManagementPage.test.tsx
import { render, screen, waitFor } from '@testing-library/react';
import { ReleaseManagementPage } from '../ReleaseManagementPage';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { http, HttpResponse } from 'msw';
import { setupServer } from 'msw/node';

const server = setupServer(
  http.get('/api/releases', () => HttpResponse.json({
    data: [{ id: 1, version: '1.0.1', formatted_version: 'v1.0.1', released_at: '2026-07-20', is_current: true, release_notes: null, created_by: { id: 1, name: 'Admin' }, created_at: '', updated_at: '' }],
    meta: { current_page: 1, last_page: 1 }
  })),
  http.post('/api/releases', () => HttpResponse.json({ id: 2, version: '1.0.2', formatted_version: 'v1.0.2', released_at: '2026-07-25', is_current: true, release_notes: '# New', created_by: { id: 1, name: 'Admin' }, created_at: '', updated_at: '' }, { status: 201 })),
);

beforeAll(() => server.listen());
afterEach(() => server.resetHandlers());
afterAll(() => server.close());

test('renders release list and can create new', async () => {
  const queryClient = new QueryClient();
  render(
    <QueryClientProvider client={queryClient}>
      <ReleaseManagementPage />
    </QueryClientProvider>
  );

  await waitFor(() => expect(screen.getByText('v1.0.1')).toBeInTheDocument());
  expect(screen.getByText('New Release')).toBeInTheDocument();
});
```

---

## 8. Deployment Notes

### 8.1 Migration Order
Run after existing migrations:
```bash
php artisan migrate
```

### 8.2 Seeder
```bash
php artisan db:seed --class=ReleaseSeeder
```

### 8.3 Cache Clear
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 9. Future Enhancements (Post-MVP)

| Feature | Description |
|---------|-------------|
| Markdown preview in modal | Live preview tab in ReleaseFormModal |
| Release comparison | Diff between two versions |
| Changelog categories | Auto-group notes by "Added", "Fixed", "Changed" |
| Scheduled releases | Set `is_current` at future date via scheduler |
| Slack/Discord webhook | Notify on new release creation |
| Version badge API | `/api/version/badge` → shields.io compatible SVG |

---

## 10. Acceptance Criteria

- [ ] Admin can create release with version, notes, date
- [ ] Only one release marked as `is_current` at any time
- [ ] Public `GET /api/version/current` returns current version
- [ ] Version displayed in header/footer via `useVersion()` hook
- [ ] Changelog page shows all releases with markdown rendering
- [ ] Cannot delete current release
- [ ] Semantic version regex validation (major.minor.patch[-prerelease])
- [ ] Tests pass (backend + frontend)
- [ ] No Git tags, package.json, composer.json modified by this feature

---

**Document Control**

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-07-25 | Engineering | Initial specification |

**Approval:** Pending Review