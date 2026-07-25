<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AiMessage;
use App\Models\AiSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AiSessionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\AiSession::class, 'ai_session');
    }

    public function index(Request $request): JsonResponse
    {
        $query = AiSession::query()
            ->when($request->workspace_id, fn ($q) => $q->where('workspace_id', $request->workspace_id))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->with(['workspace', 'user'])
            ->orderBy('created_at', 'desc');

        $sessions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'workspace_id' => 'required|string|exists:workspaces,id',
            'user_id' => 'required|string|exists:people,id',
            'title' => 'required|string|max:255',
            'context' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $session = AiSession::create($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $session->load(['workspace', 'user']),
        ], 201);
    }

    public function show(AiSession $aiSession): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $aiSession->load(['workspace', 'user', 'messages']),
        ]);
    }

    public function update(Request $request, AiSession $aiSession): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'context' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $aiSession->update($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $aiSession,
        ]);
    }

    public function destroy(AiSession $aiSession): JsonResponse
    {
        $aiSession->delete();

        return response()->json([
            'success' => true,
            'message' => 'AI session deleted successfully',
        ]);
    }

    public function sendMessage(Request $request, AiSession $aiSession): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role' => 'required|in:user,assistant,system',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $message = AiMessage::create([
            'ai_session_id' => $aiSession->id,
            'role' => $request->role,
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'data' => $message,
        ], 201);
    }

    public function getMessages(AiSession $aiSession): JsonResponse
    {
        $messages = $aiSession->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }
}