import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { useState } from 'react';

interface Task {
  id: number;
  title: string;
  due_date?: string;
  priority: string;
}

const mockTasks: Task[] = [
  { id: 1, title: 'Complete project proposal', due_date: '2026-07-25', priority: 'high' },
  { id: 2, title: 'Review team submissions', due_date: '2026-07-25', priority: 'medium' },
  { id: 3, title: 'Update documentation', due_date: '2026-07-26', priority: 'low' },
];

export function FocusPage() {
  const [completed, setCompleted] = useState<Set<number>>(new Set());

  const toggleComplete = (id: number) => {
    const newCompleted = new Set(completed);
    if (newCompleted.has(id)) newCompleted.delete(id);
    else newCompleted.add(id);
    setCompleted(newCompleted);
  };

  const today = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
  const progress = Math.round((completed.size / mockTasks.length) * 100);

  return (
    <div className="p-6 space-y-6">
      <div>
        <h1 className="text-2xl font-bold">Focus View</h1>
        <p className="text-muted-foreground">{today}</p>
      </div>

      <Card className="p-4">
        <div className="flex items-center justify-between mb-2">
          <span className="font-semibold">Today's Progress</span>
          <span className="text-sm text-muted-foreground">{progress}%</span>
        </div>
        <div className="w-full bg-secondary rounded-full h-2">
          <div className="bg-primary h-2 rounded-full transition-all" style={{ width: `${progress}%` }} />
        </div>
      </Card>

      <div className="space-y-3">
        <h2 className="text-lg font-semibold">Today's Tasks</h2>
        {mockTasks.map(task => (
          <Card key={task.id} className="p-4">
            <div className="flex items-start gap-3">
              <button
                onClick={() => toggleComplete(task.id)}
                className={`w-5 h-5 rounded border-2 flex items-center justify-center ${
                  completed.has(task.id) ? 'bg-primary border-primary' : 'border-gray-300'
                }`}
              >
                {completed.has(task.id) && (
                  <svg className="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                  </svg>
                )}
              </button>
              <div className="flex-1">
                <div className={`font-medium ${completed.has(task.id) ? 'line-through text-muted-foreground' : ''}`}>
                  {task.title}
                </div>
                <div className="flex gap-2 mt-1 text-xs text-muted-foreground">
                  {task.due_date && <span>Due: {new Date(task.due_date).toLocaleDateString()}</span>}
                  <span className={`px-2 py-0.5 rounded ${
                    task.priority === 'high' ? 'bg-red-100 text-red-700' :
                    task.priority === 'medium' ? 'bg-orange-100 text-orange-700' :
                    'bg-slate-100 text-slate-700'
                  }`}>{task.priority}</span>
                </div>
              </div>
            </div>
          </Card>
        ))}
      </div>

      <Card className="p-4">
        <h2 className="text-lg font-semibold mb-3">Upcoming Deadlines</h2>
        <div className="space-y-2">
          <div className="flex items-center justify-between p-2 bg-yellow-50 rounded">
            <span>Project Deadline</span>
            <span className="text-sm text-muted-foreground">Tomorrow</span>
          </div>
        </div>
      </Card>
    </div>
  );
}