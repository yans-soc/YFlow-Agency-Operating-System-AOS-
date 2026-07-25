<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Activity::class, 'activity');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Activity::query()
            ->when($request->workspace_id, fn ($q) => $q->where('workspace_id', $request->workspace_id))
            ->when($request->actor_id, fn ($q) => $q->where('actor_id', $request->actor_id))
            ->when($request->subject_type, fn ($q) => $q->where('subject_type', $request->subject_type))
            ->when($request->subject_id, fn ($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->action, fn ($q) => $q->where('action', $request->action))
            ->with(['workspace', 'actor'])
            ->orderBy('created_at', 'desc');

        $activities = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'workspace_id' => 'required|string|exists:workspaces,id',
            'actor_id' => 'required|string|exists:people,id',
            'action' => 'required|string|max:255',
            'subject_type' => 'required|string|max:255',
            'subject_id' => 'required|string',
            'changes' => 'nullable|array',
            'ip_address' => 'nullable|string|max:45',
            'user_agent' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $activity = Activity::create(array_merge($validator->validated(), [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]));

        return response()->json([
            'success' => true,
            'data' => $activity->load(['workspace', 'actor']),
        ], 201);
    }

    public function show(Activity $activity): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $activity->load(['workspace', 'actor']),
        ]);
    }

    public function destroy(Activity $activity): JsonResponse
    {
        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activity log deleted successfully',
        ]);
    }
}