import { useState } from 'react';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { Workflow, WorkflowStage } from '../types/workflow';

interface WorkflowBuilderProps {
  workflow: Workflow;
  onSaveStage: (stage: Omit<WorkflowStage, 'id' | 'workflow_id' | 'created_at' | 'updated_at'>) => Promise<void>;
  onUpdateStage: (stageId: number, data: Partial<WorkflowStage>) => Promise<void>;
  onDeleteStage: (stageId: number) => Promise<void>;
}

export function WorkflowBuilder({ workflow, onSaveStage, onUpdateStage, onDeleteStage }: WorkflowBuilderProps) {
  const [newStageName, setNewStageName] = useState('');
  const [newStageColor, setNewStageColor] = useState('#3b82f6');

  const handleAddStage = async () => {
    if (!newStageName.trim()) return;
    
    const order = (workflow.stages?.length || 0) + 1;
    await onSaveStage({
      name: newStageName,
      order,
      color: newStageColor,
    });
    setNewStageName('');
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center gap-2">
        <Input
          placeholder="Stage name..."
          value={newStageName}
          onChange={(e) => setNewStageName(e.target.value)}
          className="max-w-xs"
        />
        <Input
          type="color"
          value={newStageColor}
          onChange={(e) => setNewStageColor(e.target.value)}
          className="w-12 h-10 p-1"
        />
        <Button onClick={handleAddStage} disabled={!newStageName.trim()}>
          Add Stage
        </Button>
      </div>

      <div className="flex gap-4 overflow-x-auto pb-4">
        {workflow.stages?.map((stage) => (
          <Card key={stage.id} className="p-4 min-w-[200px] flex-shrink-0">
            <div className="flex items-center justify-between mb-2">
              <div className="flex items-center gap-2">
                <div
                  className="w-3 h-3 rounded-full"
                  style={{ backgroundColor: stage.color || '#3b82f6' }}
                />
                <span className="font-semibold">{stage.name}</span>
              </div>
              <div className="flex gap-1">
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => onUpdateStage(stage.id, { name: prompt('Stage name:', stage.name) || stage.name })}
                >
                  ✏️
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => onDeleteStage(stage.id)}
                >
                  ️
                </Button>
              </div>
            </div>
            <div className="text-sm text-muted-foreground">
              {stage.tasks?.length || 0} tasks
            </div>
          </Card>
        ))}
      </div>
    </div>
  );
}