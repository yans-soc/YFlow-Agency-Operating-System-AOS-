<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoteController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Note::class, 'note');
    }

    public function index(Request $request): JsonResponse
    {
        $query = Note::query()
            ->when($request->workspace_id, fn ($q) => $q->where('workspace_id', $request->workspace_id))
            ->when($request->created_by, fn ($q) => $q->where('created_by', $request->created_by))
            ->with(['workspace', 'creator'])
            ->orderBy('created_at', 'desc');

        $notes = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'workspace_id' => 'required|string|exists:workspaces,id',
            'created_by' => 'required|string|exists:people,id',
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $note = Note::create($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $note->load(['workspace', 'creator']),
        ], 201);
    }

    public function show(Note $note): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $note->load(['workspace', 'creator']),
        ]);
    }

    public function update(Request $request, Note $note): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $note->update($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $note,
        ]);
    }

    public function destroy(Note $note): JsonResponse
    {
        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully',
        ]);
    }
}