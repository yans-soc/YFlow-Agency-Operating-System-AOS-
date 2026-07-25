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
        $this->authorize('create', Release::class);

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
        $this->authorize('update', $release);

        $release->update($request->validated());

        return ReleaseResource::make($release->load('creator'))->response();
    }

    public function destroy(Release $release): JsonResponse
    {
        $this->authorize('delete', $release);

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
        $this->authorize('setCurrent', $release);

        $release->update(['is_current' => true]);

        return ReleaseResource::make($release->load('creator'))->response();
    }

    public function current(): JsonResponse
    {
        $release = Release::current()->firstOrFail();

        return CurrentVersionResource::make($release)->response();
    }
}