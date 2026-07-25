import api from '@/lib/api';
import type { Task, CreateTaskRequest, UpdateTaskRequest } from '../types/task';

export const taskService = {
  list: async (projectId?: number) => {
    const params = projectId ? `?project_id=${projectId}` : '';
    const response = await api.get(`/api/v1/tasks${params}`);
    return response.data;
  },

  get: async (id: number) => {
    const response = await api.get(`/api/v1/tasks/${id}`);
    return response.data;
  },

  create: async (data: CreateTaskRequest) => {
    const response = await api.post('/api/v1/tasks', data);
    return response.data;
  },

  update: async (id: number, data: UpdateTaskRequest) => {
    const response = await api.put(`/api/v1/tasks/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await api.delete(`/api/v1/tasks/${id}`);
    return response.data;
  },

  addAssignee: async (taskId: number, personId: number) => {
    const response = await api.post(`/api/v1/tasks/${taskId}/assignees`, { person_id: personId });
    return response.data;
  },

  removeAssignee: async (taskId: number, assigneeId: number) => {
    const response = await api.delete(`/api/v1/tasks/${taskId}/assignees/${assigneeId}`);
    return response.data;
  },

  addChecklist: async (taskId: number, title: string) => {
    const response = await api.post(`/api/v1/tasks/${taskId}/checklists`, { title });
    return response.data;
  },

  toggleChecklist: async (taskId: number, checklistId: number) => {
    const response = await api.patch(`/api/v1/tasks/${taskId}/checklists/${checklistId}/toggle`);
    return response.data;
  },

  deleteChecklist: async (taskId: number, checklistId: number) => {
    const response = await api.delete(`/api/v1/tasks/${taskId}/checklists/${checklistId}`);
    return response.data;
  },
};