<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CalendarEventController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\CalendarEvent::class, 'calendar_event');
    }

    public function index(Request $request): JsonResponse
    {
        $query = CalendarEvent::query()
            ->when($request->workspace_id, fn ($q) => $q->where('workspace_id', $request->workspace_id))
            ->when($request->created_by, fn ($q) => $q->where('created_by', $request->created_by))
            ->when($request->start_date, fn ($q) => $q->whereDate('start_time', '>=', $request->start_date))
            ->when($request->end_date, fn ($q) => $q->whereDate('end_time', '<=', $request->end_date))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->with(['workspace', 'creator'])
            ->orderBy('start_time', 'asc');

        $events = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'workspace_id' => 'required|string|exists:workspaces,id',
            'created_by' => 'required|string|exists:people,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'type' => 'sometimes|in:meeting,deadline,holiday,reminder,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $event = CalendarEvent::create(array_merge($validator->validated(), [
            'type' => $request->type ?? 'other',
        ]));

        return response()->json([
            'success' => true,
            'data' => $event->load(['workspace', 'creator']),
        ], 201);
    }

    public function show(CalendarEvent $calendarEvent): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $calendarEvent->load(['workspace', 'creator']),
        ]);
    }

    public function update(Request $request, CalendarEvent $calendarEvent): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'type' => 'sometimes|in:meeting,deadline,holiday,reminder,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $calendarEvent->update($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $calendarEvent,
        ]);
    }

    public function destroy(CalendarEvent $calendarEvent): JsonResponse
    {
        $calendarEvent->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully',
        ]);
    }
}