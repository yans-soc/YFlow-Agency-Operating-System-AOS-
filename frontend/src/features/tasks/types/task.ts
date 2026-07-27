/**
 * Task domain types — aligned to the backend contract.
 *
 * Source of truth:
 * - tasks table: uuid id, stage_id, created_by, title, description,
 *   priority enum, status enum, start_date, due_date, completed_at
 * - TaskController exposes: index/show/store/update/destroy,
 *   moveStage (PATCH stage_id), toggleComplete (toggles completed_at)
 * - Assignees are synced via an `assignees` array of person UUIDs on
 *   store/update (there are no standalone assignee endpoints).
 */

export type TaskStatus =
  | "draft"
  | "todo"
  | "in_progress"
  | "review"
  | "approved"
  | "done"
  | "archived";

export type TaskPriority = "low" | "medium" | "high" | "urgent";

/** Nested project summary surfaced through stage.workflow.project. */
export interface TaskProjectRef {
  id: string;
  name: string;
}

export interface TaskStageRef {
  id: string;
  name?: string;
  workflow?: {
    id: string;
    name?: string;
    project?: TaskProjectRef;
  };
}

export interface TaskAssignee {
  id: string;
  task_id: string;
  person_id: string;
  role?: string;
  created_at?: string;
  person?: {
    id: string;
    name: string;
    avatar?: string | null;
  };
}

export interface TaskChecklist {
  id: string;
  task_id: string;
  title: string;
  is_completed: boolean;
  order?: number;
  created_at?: string;
  updated_at?: string;
}

export interface Task {
  id: string;
  stage_id: string;
  created_by: string;
  title: string;
  description?: string | null;
  status: TaskStatus;
  priority: TaskPriority;
  start_date?: string | null;
  due_date?: string | null;
  completed_at?: string | null;
  created_at: string;
  updated_at: string;
  stage?: TaskStageRef;
  creator?: { id: string; name: string; avatar?: string | null };
  assignees?: TaskAssignee[];
  checklists?: TaskChecklist[];
}

/** Query params accepted by GET /tasks. */
export interface TaskListParams {
  stage_id?: string;
  priority?: TaskPriority;
  assignee_id?: string;
  per_page?: number;
  page?: number;
}

export interface CreateTaskRequest {
  stage_id: string;
  created_by: string;
  title: string;
  description?: string;
  priority?: TaskPriority;
  start_date?: string;
  due_date?: string;
  /** Person UUIDs to sync as assignees. */
  assignees?: string[];
}

export interface UpdateTaskRequest {
  stage_id?: string;
  title?: string;
  description?: string;
  priority?: TaskPriority;
  status?: TaskStatus;
  start_date?: string;
  due_date?: string;
  completed_at?: string | null;
  assignees?: string[];
}
