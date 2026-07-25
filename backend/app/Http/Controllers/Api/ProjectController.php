<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Project::class);

        $query = Project::query()
            ->when($request->workspace_id, fn ($q) => $q->where('workspace_id', $request->workspace_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->with(['owner', 'workflow.stages'])
            ->orderBy('created_at', 'desc');

        $projects = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $validator = Validator::make($request->all(), [
            'workspace_id' => 'required|string|exists:workspaces,id',
            'owner_id' => 'required|string|exists:people,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:planning,active,on_hold,completed,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $project = Project::create(array_merge($validator->validated(), [
            'status' => $request->status ?? 'planning',
        ]));

        $workflow = Workflow::create([
            'project_id' => $project->id,
            'name' => $project->name . ' Workflow',
        ]);

        $defaultStages = ['Backlog', 'To Do', 'In Progress', 'Review', 'Done'];
        foreach ($defaultStages as $index => $stageName) {
            WorkflowStage::create([
                'workflow_id' => $workflow->id,
                'name' => $stageName,
                'sort_order' => $index + 1,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $project->load(['workflow.stages']),
        ], 201);
    }

    public function show(Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json([
            'success' => true,
            'data' => $project->load(['owner', 'members.person', 'workflow.stages.tasks']),
        ]);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:planning,active,on_hold,completed,archived',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $project->update($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $project,
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
    }
}
