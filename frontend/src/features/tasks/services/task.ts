import api, { unwrap } from "@/lib/api";
import type { Paginated } from "@/lib/api";
import type {
  Task,
  TaskListParams,
  CreateTaskRequest,
  UpdateTaskRequest,
} from "../types/task";

/**
 * Task API client.
 *
 * `api` already targets the `/api/v1` base URL, so paths here are relative
 * to that (do NOT re-prefix with `/api/v1`). All identifiers are UUID strings.
 */
export const taskService = {
  list: async (params: TaskListParams = {}): Promise<Paginated<Task>> => {
    const response = await api.get("/tasks", { params });
    return unwrap<Paginated<Task>>(response);
  },

  get: async (id: string): Promise<Task> => {
    const response = await api.get(`/tasks/${id}`);
    return unwrap<Task>(response);
  },

  create: async (data: CreateTaskRequest): Promise<Task> => {
    const response = await api.post("/tasks", data);
    return unwrap<Task>(response);
  },

  update: async (id: string, data: UpdateTaskRequest): Promise<Task> => {
    const response = await api.put(`/tasks/${id}`, data);
    return unwrap<Task>(response);
  },

  remove: async (id: string): Promise<void> => {
    await api.delete(`/tasks/${id}`);
  },

  /** Move a task to another workflow stage (Kanban drag/drop). */
  moveStage: async (id: string, stageId: string): Promise<Task> => {
    const response = await api.post(`/tasks/${id}/move-stage`, {
      stage_id: stageId,
    });
    return unwrap<Task>(response);
  },

  /** Toggle the task's completed_at timestamp. */
  toggleComplete: async (id: string): Promise<Task> => {
    const response = await api.post(`/tasks/${id}/toggle-complete`);
    return unwrap<Task>(response);
  },
};

export default taskService;
