import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import type { Task, TaskStatus, TaskPriority } from '../types/task';

interface TaskCardProps {
  task: Task;
  onStatusChange?: (status: TaskStatus) => void;
  onClick?: () => void;
}

const statusColors: Record<TaskStatus, string> = {
  pending: 'bg-gray-100 text-gray-700',
  in_progress: 'bg-blue-100 text-blue-700',
  review: 'bg-yellow-100 text-yellow-700',
  done: 'bg-green-100 text-green-700',
};

const priorityColors: Record<TaskPriority, string> = {
  low: 'bg-slate-100 text-slate-700',
  medium: 'bg-orange-100 text-orange-700',
  high: 'bg-red-100 text-red-700',
  urgent: 'bg-purple-100 text-purple-700',
};

export function TaskCard({ task, onStatusChange, onClick }: TaskCardProps) {
  const completedChecklists = task.checklists?.filter((c) => c.is_completed).length || 0;
  const totalChecklists = task.checklists?.length || 0;

  return (
    <Card className="p-4 cursor-pointer hover:shadow-lg transition" onClick={onClick}>
      <div className="flex items-start justify-between mb-2">
        <h4 className="font-semibold flex-1">{task.title}</h4>
        <span className={`text-xs px-2 py-1 rounded ${priorityColors[task.priority]}`}>
          {task.priority}
        </span>
      </div>

      {task.description && (
        <p className="text-sm text-muted-foreground mb-2 line-clamp-2">{task.description}</p>
      )}

      <div className="flex items-center gap-2 mb-3">
        <span className={`text-xs px-2 py-1 rounded ${statusColors[task.status]}`}>
          {task.status.replace('_', ' ')}
        </span>
        {task.due_date && (
          <span className="text-xs text-muted-foreground">
            Due: {new Date(task.due_date).toLocaleDateString()}
          </span>
        )}
      </div>

      {totalChecklists > 0 && (
        <div className="text-xs text-muted-foreground mb-2">
          {completedChecklists}/{totalChecklists} checklists
        </div>
      )}

      {task.assignees && task.assignees.length > 0 && (
        <div className="flex -space-x-2">
          {task.assignees.slice(0, 3).map((assignee) => (
            <div
              key={assignee.id}
              className="w-6 h-6 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xs border-2 border-white"
              title={assignee.person?.name}
            >
              {assignee.person?.name?.charAt(0) || '?'}
            </div>
          ))}
          {task.assignees.length > 3 && (
            <div className="w-6 h-6 rounded-full bg-muted flex items-center justify-center text-xs border-2 border-white">
              +{task.assignees.length - 3}
            </div>
          )}
        </div>
      )}

      {onStatusChange && (
        <div className="flex gap-1 mt-3 pt-3 border-t">
          <Button
            variant="outline"
            size="sm"
            onClick={(e) => {
              e.stopPropagation();
              onStatusChange('pending');
            }}
          >
            Pending
          </Button>
          <Button
            variant="outline"
            size="sm"
            onClick={(e) => {
              e.stopPropagation();
              onStatusChange('in_progress');
            }}
          >
            In Progress
          </Button>
          <Button
            variant="outline"
            size="sm"
            onClick={(e) => {
              e.stopPropagation();
              onStatusChange('done');
            }}
          >
            Done
          </Button>
        </div>
      )}
    </Card>
  );
}