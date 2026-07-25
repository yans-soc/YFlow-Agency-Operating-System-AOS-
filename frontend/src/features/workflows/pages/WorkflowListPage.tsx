import { useState } from 'react';
import { useWorkspace } from '@/hooks/useWorkspace';
import { useWorkflows } from '../hooks/useWorkflows';
import { WorkflowBuilder } from '../components/WorkflowBuilder';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Workflow } from '../types/workflow';

export function WorkflowListPage() {
  const { data: workspaceData } = useWorkspace();
  const workspaceId = Number(workspaceData?.id) || 0;
  const { workflows, isLoading, createWorkflow, deleteWorkflow } = useWorkflows(workspaceId);
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [newWorkflowName, setNewWorkflowName] = useState('');
  const [selectedWorkflow, setSelectedWorkflow] = useState<number | null>(null);

  const handleCreate = async () => {
    if (!newWorkflowName.trim()) return;
    await createWorkflow({ name: newWorkflowName, is_template: false });
    setNewWorkflowName('');
    setShowCreateForm(false);
  };

  if (isLoading) {
    return <div className="p-6">Loading workflows...</div>;
  }

  return (
    <div className="p-6 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Workflows</h1>
        <Button onClick={() => setShowCreateForm(true)}>New Workflow</Button>
      </div>

      {showCreateForm && (
        <Card className="p-4">
          <div className="space-y-3">
            <Input
              placeholder="Workflow name..."
              value={newWorkflowName}
              onChange={(e) => setNewWorkflowName(e.target.value)}
            />
            <div className="flex gap-2">
              <Button onClick={handleCreate}>Create</Button>
              <Button variant="outline" onClick={() => setShowCreateForm(false)}>
                Cancel
              </Button>
            </div>
          </div>
        </Card>
      )}

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {workflows.map((workflow: any) => (
          <Card
            key={workflow.id}
            className={`p-4 cursor-pointer hover:shadow-lg transition ${
              selectedWorkflow === workflow.id ? 'ring-2 ring-primary' : ''
            }`}
            onClick={() => setSelectedWorkflow(workflow.id)}
          >
            <div className="flex items-start justify-between">
              <div>
                <h3 className="font-semibold">{workflow.name}</h3>
                <p className="text-sm text-muted-foreground">
                  {workflow.stages?.length || 0} stages
                </p>
              </div>
              <Button
                variant="ghost"
                size="sm"
                onClick={(e) => {
                  e.stopPropagation();
                  deleteWorkflow(workflow.id);
                }}
              >
                
              </Button>
            </div>
          </Card>
        ))}
      </div>

      {selectedWorkflow && (
        <Card className="p-6">
          <h2 className="text-xl font-bold mb-4">
            {workflows.find((w: Workflow) => w.id === selectedWorkflow)?.name}
          </h2>
          <WorkflowBuilder
            workflow={workflows.find((w: Workflow) => w.id === selectedWorkflow)!}
            onSaveStage={async () => {}}
            onUpdateStage={async () => {}}
            onDeleteStage={async () => {}}
          />
        </Card>
      )}
    </div>
  );
}