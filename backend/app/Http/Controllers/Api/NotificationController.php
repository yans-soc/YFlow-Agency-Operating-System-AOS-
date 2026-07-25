<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Notification::class, 'notification');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Notification::query()
            ->when($request->workspace_id, fn ($q) => $q->where('workspace_id', $request->workspace_id))
            ->when($request->recipient_id, fn ($q) => $q->where('recipient_id', $request->recipient_id))
            ->when($request->unread_only, fn ($q) => $q->whereNull('read_at'))
            ->with(['workspace', 'recipient', 'sender'])
            ->orderBy('created_at', 'desc');

        $notifications = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'workspace_id' => 'required|string|exists:workspaces,id',
            'recipient_id' => 'required|string|exists:people,id',
            'sender_id' => 'nullable|string|exists:people,id',
            'type' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $notification = Notification::create($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $notification->load(['workspace', 'recipient', 'sender']),
        ], 201);
    }

    public function show(Notification $notification): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $notification->load(['workspace', 'recipient', 'sender']),
        ]);
    }

    public function markAsRead(Notification $notification): JsonResponse
    {
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'data' => $notification,
        ]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        Notification::query()
            ->where('recipient_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    public function destroy(Notification $notification): JsonResponse
    {
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
        ]);
    }
}