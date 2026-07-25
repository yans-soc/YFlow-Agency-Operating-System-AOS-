<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workflow\StoreWorkflowRequest;
use App\Http\Requests\Workflow\UpdateWorkflowRequest;
use App\Http\Resources\WorkflowResource;
use App\Services\WorkflowService;
use Illuminate\Http\Request;

class WorkflowController extends Controller
{
    public function __construct(
        private WorkflowService $service,
    ) {
        $this->authorizeResource(\App\Models\Workflow::class, 'workflow');
    }

    public function index(Request $request)
    {
        $query = $request->input('project_id');
        
        $workflows = \App\Models\Workflow::query()
            ->when($query, fn($q) => $q->where('project_id', $query))
            ->with('stages')
            ->paginate(15);

        return WorkflowResource::collection($workflows);
    }

    public function show(string $id)
    {
        $workflow = \App\Models\Workflow::with(['stages.tasks'])->find($id);

        if (!$workflow) {
            return response()->json(['message' => 'Workflow not found'], 404);
        }

        return new WorkflowResource($workflow);
    }

    public function store(StoreWorkflowRequest $request)
    {
        $workflow = $this->service->create($request->validated());

        return response()
            ->json(new WorkflowResource($workflow), 201)
            ->header('Location', route('api.workflows.show', $workflow));
    }

    public function update(UpdateWorkflowRequest $request, string $id)
    {
        $workflow = \App\Models\Workflow::find($id);

        if (!$workflow) {
            return response()->json(['message' => 'Workflow not found'], 404);
        }

        $workflow = $this->service->update($workflow, $request->validated());

        return new WorkflowResource($workflow);
    }

    public function destroy(string $id)
    {
        $workflow = \App\Models\Workflow::find($id);

        if (!$workflow) {
            return response()->json(['message' => 'Workflow not found'], 404);
        }

        $this->service->delete($workflow);

        return response()->json(null, 204);
    }

    public function addStage(Request $request, string $id)
    {
        $workflow = \App\Models\Workflow::find($id);

        if (!$workflow) {
            return response()->json(['message' => 'Workflow not found'], 404);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $stage = $this->service->addStage($workflow, $request->only('name'));

        return response()
            ->json(new \App\Http\Resources\WorkflowStageResource($stage), 201);
    }

    public function updateStageOrder(Request $request, string $id, string $stageId)
    {
        $workflow = \App\Models\Workflow::find($id);

        if (!$workflow) {
            return response()->json(['message' => 'Workflow not found'], 404);
        }

        $request->validate([
            'order' => ['required', 'integer', 'min:1'],
        ]);

        $this->service->updateStageOrder($workflow, $stageId, $request->input('order'));

        return response()->json(null, 204);
    }
}