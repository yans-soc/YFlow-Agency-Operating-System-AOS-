<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\File::class, 'file');
    }

    public function index(Request $request): JsonResponse
    {
        $query = File::query()
            ->when($request->workspace_id, fn ($q) => $q->where('workspace_id', $request->workspace_id))
            ->when($request->uploaded_by, fn ($q) => $q->where('uploaded_by', $request->uploaded_by))
            ->with(['workspace', 'uploader'])
            ->orderBy('created_at', 'desc');

        $files = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $files,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'workspace_id' => 'required|string|exists:workspaces,id',
            'uploaded_by' => 'required|string|exists:people,id',
            'file' => 'required|file|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');
        $path = $file->store('uploads', 'public');

        $fileRecord = File::create([
            'workspace_id' => $request->workspace_id,
            'uploaded_by' => $request->uploaded_by,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $fileRecord->load(['workspace', 'uploader']),
        ], 201);
    }

    public function show(File $file): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $file->load(['workspace', 'uploader']),
        ]);
    }

    public function download(File $file): JsonResponse
    {
        if (!Storage::disk('public')->exists($file->path)) {
            return response()->json([
                'success' => false,
                'message' => 'File not found',
            ], 404);
        }

        return response()->download(storage_path('app/public/' . $file->path));
    }

    public function destroy(File $file): JsonResponse
    {
        if (Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }

        $file->delete();

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }
}