<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowStage;

class WorkflowService
{
    public function create(array $data): Workflow
    {
        $workflow = Workflow::create([
            'project_id' => $data['project_id'],
            'name' => $data['name'],
        ]);

        if (!empty($data['stages'])) {
            foreach ($data['stages'] as $index => $stage) {
                WorkflowStage::create([
                    'workflow_id' => $workflow->id,
                    'name' => $stage['name'],
                    'order' => $index + 1,
                ]);
            }
        }

        return $workflow->fresh(['stages']);
    }

    public function update(Workflow $workflow, array $data): Workflow
    {
        $workflow->update([
            'name' => $data['name'] ?? $workflow->name,
        ]);

        return $workflow->fresh(['stages']);
    }

    public function delete(Workflow $workflow): bool
    {
        return $workflow->delete();
    }

    public function addStage(Workflow $workflow, array $data): WorkflowStage
    {
        $maxOrder = $workflow->stages()->max('order') ?? 0;
        
        return WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'name' => $data['name'],
            'order' => $maxOrder + 1,
        ]);
    }

    public function updateStageOrder(Workflow $workflow, string $stageId, int $newOrder): bool
    {
        $stage = $workflow->stages()->find($stageId);
        if (!$stage) {
            return false;
        }

        $stage->update(['order' => $newOrder]);
        return true;
    }
}