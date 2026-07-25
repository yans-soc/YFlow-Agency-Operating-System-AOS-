import { useState } from 'react';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useTasks } from '../hooks/useTasks';
import { TaskCard } from '../components/TaskCard';
import type { Task, TaskStatus } from '../types/task';

export function TaskListPage() {
  const { tasks, isLoading, createTask, updateTask } = useTasks();
  const [showCreateForm, setShowCreateForm] = useState(false);
  const [newTaskTitle, setNewTaskTitle] = useState('');
  const [filterStatus, setFilterStatus] = useState<TaskStatus | 'all'>('all');

  const handleCreate = async () => {
    if (!newTaskTitle.trim()) return;
    await createTask({
      project_id: 1,
      workflow_stage_id: 1,
      title: newTaskTitle,
      priority: 'medium',
    });
    setNewTaskTitle('');
    setShowCreateForm(false);
  };

  const handleStatusChange = async (taskId: number, status: TaskStatus) => {
    await updateTask({ id: taskId, data: { status } });
  };

  const filteredTasks = filterStatus === 'all'
    ? tasks
    : tasks.filter((t: Task) => t.status === filterStatus);

  if (isLoading) {
    return <div className="p-6">Loading tasks...</div>;
  }

  return (
    <div className="p-6 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold">Tasks</h1>
        <Button onClick={() => setShowCreateForm(true)}>New Task</Button>
      </div>

      {showCreateForm && (
        <Card className="p-4">
          <div className="space-y-3">
            <Input
              placeholder="Task title..."
              value={newTaskTitle}
              onChange={(e) => setNewTaskTitle(e.target.value)}
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

      <div className="flex gap-2">
        <Button
          variant={filterStatus === 'all' ? 'default' : 'outline'}
          size="sm"
          onClick={() => setFilterStatus('all')}
        >
          All
        </Button>
        <Button
          variant={filterStatus === 'pending' ? 'default' : 'outline'}
          size="sm"
          onClick={() => setFilterStatus('pending')}
        >
          Pending
        </Button>
        <Button
          variant={filterStatus === 'in_progress' ? 'default' : 'outline'}
          size="sm"
          onClick={() => setFilterStatus('in_progress')}
        >
          In Progress
        </Button>
        <Button
          variant={filterStatus === 'done' ? 'default' : 'outline'}
          size="sm"
          onClick={() => setFilterStatus('done')}
        >
          Done
        </Button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {filteredTasks.map((task: Task) => (
          <TaskCard
            key={task.id}
            task={task}
            onStatusChange={(status) => handleStatusChange(task.id, status)}
          />
        ))}
      </div>

      {filteredTasks.length === 0 && (
        <div className="text-center py-12 text-muted-foreground">
          No tasks found
        </div>
      )}
    </div>
  );
}