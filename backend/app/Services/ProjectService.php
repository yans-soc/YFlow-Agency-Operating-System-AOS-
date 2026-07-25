<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Workflow;
use App\Models\WorkflowStage;

class ProjectService
{
    public function create(array $data): Project
    {
        $project = Project::create([
            'workspace_id' => $data['workspace_id'],
            'owner_id' => $data['owner_id'] ?? null,
            'code' => $data['code'] ?? $this->generateCode($data['name']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'planning',
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ]);

        $this->createDefaultWorkflow($project);

        return $project->load(['workflow.stages']);
    }

    private function generateCode(string $name): string
    {
        return 'PRJ-' . strtoupper(substr(md5(uniqid()), 0, 6));
    }

    private function createDefaultWorkflow(Project $project): void
    {
        $workflow = Workflow::create([
            'project_id' => $project->id,
            'name' => $project->name . ' Workflow',
        ]);

        $defaultStages = ['Backlog', 'To Do', 'In Progress', 'Review', 'Done'];

        foreach ($defaultStages as $index => $stageName) {
            WorkflowStage::create([
                'workflow_id' => $workflow->id,
                'name' => $stageName,
                'sort_order' => $index + 1,
            ]);
        }
    }

    public function update(Project $project, array $data): Project
    {
        $project->update([
            'name' => $data['name'] ?? $project->name,
            'description' => $data['description'] ?? $project->description,
            'status' => $data['status'] ?? $project->status,
            'start_date' => $data['start_date'] ?? $project->start_date,
            'end_date' => $data['end_date'] ?? $project->end_date,
        ]);

        return $project;
    }

    public function archive(Project $project): Project
    {
        $project->update(['status' => 'archived']);
        return $project;
    }
}