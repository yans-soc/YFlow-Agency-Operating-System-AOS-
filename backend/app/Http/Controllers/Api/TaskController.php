<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\WorkflowStage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): JsonResponse
    {
        $query = Task::query()
            ->when($request->stage_id, fn ($q) => $q->where('stage_id', $request->stage_id))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->assignee_id, fn ($q) => $q->whereHas('assignees', fn ($q) => $q->where('people.id', $request->assignee_id)))
            ->with(['stage.workflow.project', 'creator', 'assignees'])
            ->orderBy('created_at', 'desc');

        $tasks = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $tasks,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stage_id' => 'required|string|exists:workflow_stages,id',
            'created_by' => 'required|string|exists:people,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = Task::create(array_merge($validator->validated(), [
            'priority' => $request->priority ?? 'medium',
        ]));

        if ($request->has('assignees')) {
            $task->assignees()->sync($request->assignees);
        }

        return response()->json([
            'success' => true,
            'data' => $task->load(['stage', 'creator', 'assignees']),
        ], 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $task->load(['stage.workflow.project', 'creator', 'assignees', 'checklists']),
        ]);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stage_id' => 'sometimes|string|exists:workflow_stages,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'completed_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $task->update($validator->validated());

        if ($request->has('assignees')) {
            $task->assignees()->sync($request->assignees);
        }

        return response()->json([
            'success' => true,
            'data' => $task->fresh(['stage', 'creator', 'assignees']),
        ]);
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }

    public function moveStage(Request $request, Task $task): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stage_id' => 'required|string|exists:workflow_stages,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $task->update(['stage_id' => $request->stage_id]);

        return response()->json([
            'success' => true,
            'data' => $task->load(['stage.workflow.project']),
        ]);
    }

    public function toggleComplete(Task $task): JsonResponse
    {
        $task->update([
            'completed_at' => $task->completed_at ? null : now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }
}