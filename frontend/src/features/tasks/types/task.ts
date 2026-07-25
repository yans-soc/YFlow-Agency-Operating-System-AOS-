export type TaskStatus = 'pending' | 'in_progress' | 'review' | 'done';
export type TaskPriority = 'low' | 'medium' | 'high' | 'urgent';

export interface Task {
  id: number;
  project_id: number;
  workflow_stage_id: number;
  title: string;
  description?: string;
  status: TaskStatus;
  priority: TaskPriority;
  due_date?: string;
  created_by: number;
  created_at: string;
  updated_at: string;
  assignees?: TaskAssignee[];
  checklists?: TaskChecklist[];
  notes?: any[];
  files?: any[];
}

export interface TaskAssignee {
  id: number;
  task_id: number;
  person_id: number;
  role: string;
  created_at: string;
  person?: any;
}

export interface TaskChecklist {
  id: number;
  task_id: number;
  title: string;
  is_completed: boolean;
  order: number;
  created_at: string;
  updated_at: string;
}

export interface CreateTaskRequest {
  project_id: number;
  workflow_stage_id: number;
  title: string;
  description?: string;
  priority?: TaskPriority;
  due_date?: string;
  assignee_ids?: number[];
  checklist_items?: string[];
}

export interface UpdateTaskRequest {
  title?: string;
  description?: string;
  status?: TaskStatus;
  priority?: TaskPriority;
  due_date?: string;
}