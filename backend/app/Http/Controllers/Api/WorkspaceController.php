<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Models\Person;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkspaceController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Workspace::class, 'workspace');
    }

    /**
     * Get current user's workspace.
     */
    public function current(Request $request): JsonResponse
    {
        $person = $request->user()->person;
        
        if (!$person || !$person->workspace_id) {
            return response()->json([
                'success' => false,
                'message' => 'No workspace found'
            ], 404);
        }

        $workspace = Workspace::with(['departments', 'owner'])->find($person->workspace_id);

        return response()->json([
            'success' => true,
            'data' => $workspace,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $workspaces = Workspace::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $workspaces,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:150|unique:workspaces,slug',
            'timezone' => 'nullable|string|max:50',
            'logo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $workspace = Workspace::create([
            'name' => $request->name,
            'slug' => $request->slug ?? \Str::slug($request->name),
            'timezone' => $request->timezone ?? 'UTC',
            'status' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'data' => $workspace,
        ], 201);
    }

    public function show(Workspace $workspace): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $workspace->load(['departments', 'projects', 'people']),
        ]);
    }

    public function update(Request $request, Workspace $workspace): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:workspaces,slug,' . $workspace->id,
            'timezone' => 'sometimes|string',
            'status' => 'sometimes|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $workspace->update($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $workspace,
        ]);
    }

    public function destroy(Workspace $workspace): JsonResponse
    {
        $workspace->delete();

        return response()->json([
            'success' => true,
            'message' => 'Workspace deleted successfully',
        ]);
    }

    /**
     * Get workspace members.
     */
    public function members(Request $request, Workspace $workspace): JsonResponse
    {
        $members = Person::where('workspace_id', $workspace->id)
            ->with('position', 'department')
            ->get()
            ->map(function ($person) {
                return [
                    'id' => $person->id,
                    'user_id' => $person->user_id,
                    'name' => $person->name,
                    'email' => $person->email,
                    'role' => $person->role,
                    'avatar' => $person->avatar_url,
                    'department' => $person->department?->name,
                    'position' => $person->position?->name,
                    'joined_at' => $person->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    /**
     * Invite a new member to the workspace.
     */
    public function inviteMember(Request $request, Workspace $workspace): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'role' => 'required|in:admin,member,viewer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user already exists
        $existingPerson = Person::where('email', $request->email)
            ->where('workspace_id', $workspace->id)
            ->first();

        if ($existingPerson) {
            return response()->json([
                'success' => false,
                'message' => 'User is already a member of this workspace'
            ], 422);
        }

        // Create person record (would typically send invitation email)
        $person = Person::create([
            'workspace_id' => $workspace->id,
            'name' => explode('@', $request->email)[0],
            'email' => $request->email,
            'role' => $request->role,
            'status' => 'invited',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $person->id,
                'user_id' => $person->user_id,
                'name' => $person->name,
                'email' => $person->email,
                'role' => $person->role,
                'joined_at' => $person->created_at->toISOString(),
            ],
        ], 201);
    }

    /**
     * Remove a member from the workspace.
     */
    public function removeMember(Request $request, Workspace $workspace, string $memberId): JsonResponse
    {
        $member = Person::where('id', $memberId)
            ->where('workspace_id', $workspace->id)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        // Cannot remove owner
        if ($member->role === 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove workspace owner'
            ], 422);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member removed successfully'
        ]);
    }

    /**
     * Update member role.
     */
    public function updateMember(Request $request, Workspace $workspace, string $memberId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:admin,member,viewer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $member = Person::where('id', $memberId)
            ->where('workspace_id', $workspace->id)
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found'
            ], 404);
        }

        // Cannot change owner role
        if ($member->role === 'owner') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot change owner role'
            ], 422);
        }

        $member->update(['role' => $request->role]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $member->id,
                'user_id' => $member->user_id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $member->role,
                'joined_at' => $member->created_at->toISOString(),
            ],
        ]);
    }

    /**
     * Get workspace statistics.
     */
    public function stats(Request $request, Workspace $workspace): JsonResponse
    {
        $totalProjects = Project::where('workspace_id', $workspace->id)->count();
        $activeProjects = Project::where('workspace_id', $workspace->id)
            ->where('status', 'active')
            ->count();
        $totalMembers = Person::where('workspace_id', $workspace->id)->count();
        $totalTasks = Task::whereHas('project', function ($query) use ($workspace) {
            $query->where('workspace_id', $workspace->id);
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_projects' => $totalProjects,
                'active_projects' => $activeProjects,
                'total_members' => $totalMembers,
                'total_tasks' => $totalTasks,
            ],
        ]);
    }
}
