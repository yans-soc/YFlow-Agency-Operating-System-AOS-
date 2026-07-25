import api from '@/lib/api';
import type { Workflow, WorkflowStage, CreateWorkflowRequest, UpdateWorkflowRequest } from '../types/workflow';

export const workflowService = {
  list: async (workspaceId: number) => {
    const response = await api.get(`/api/v1/workspaces/${workspaceId}/workflows`);
    return response.data;
  },

  get: async (id: number) => {
    const response = await api.get(`/api/v1/workflows/${id}`);
    return response.data;
  },

  create: async (data: CreateWorkflowRequest) => {
    const response = await api.post('/api/v1/workflows', data);
    return response.data;
  },

  update: async (id: number, data: UpdateWorkflowRequest) => {
    const response = await api.put(`/api/v1/workflows/${id}`, data);
    return response.data;
  },

  delete: async (id: number) => {
    const response = await api.delete(`/api/v1/workflows/${id}`);
    return response.data;
  },

  addStage: async (workflowId: number, stage: Omit<WorkflowStage, 'id' | 'workflow_id' | 'created_at' | 'updated_at'>) => {
    const response = await api.post(`/api/v1/workflows/${workflowId}/stages`, stage);
    return response.data;
  },

  updateStage: async (workflowId: number, stageId: number, data: Partial<WorkflowStage>) => {
    const response = await api.put(`/api/v1/workflows/${workflowId}/stages/${stageId}`, data);
    return response.data;
  },

  deleteStage: async (workflowId: number, stageId: number) => {
    const response = await api.delete(`/api/v1/workflows/${workflowId}/stages/${stageId}`);
    return response.data;
  },
};