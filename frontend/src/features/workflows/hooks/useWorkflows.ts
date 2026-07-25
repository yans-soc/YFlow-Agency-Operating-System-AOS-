import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { workflowService } from '../services/workflow';
import type { CreateWorkflowRequest, UpdateWorkflowRequest } from '../types/workflow';

export function useWorkflows(workspaceId: number) {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['workflows', workspaceId],
    queryFn: () => workflowService.list(workspaceId),
    enabled: !!workspaceId,
  });

  const createMutation = useMutation({
    mutationFn: (data: CreateWorkflowRequest) => workflowService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['workflows', workspaceId] });
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateWorkflowRequest }) =>
      workflowService.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['workflows', workspaceId] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => workflowService.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['workflows', workspaceId] });
    },
  });

  return {
    workflows: data?.data || [],
    isLoading,
    error,
    createWorkflow: createMutation.mutateAsync,
    updateWorkflow: updateMutation.mutateAsync,
    deleteWorkflow: deleteMutation.mutateAsync,
    isCreating: createMutation.isPending,
    isUpdating: updateMutation.isPending,
    isDeleting: deleteMutation.isPending,
  };
}

export function useWorkflow(id: number) {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['workflow', id],
    queryFn: () => workflowService.get(id),
    enabled: !!id,
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateWorkflowRequest }) =>
      workflowService.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['workflow', id] });
      queryClient.invalidateQueries({ queryKey: ['workflows'] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (workflowId: number) => workflowService.delete(workflowId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['workflows'] });
    },
  });

  const addStageMutation = useMutation({
    mutationFn: ({ workflowId, stage }: { workflowId: number; stage: any }) =>
      workflowService.addStage(workflowId, stage),
    onSuccess: (_, { workflowId }) => {
      queryClient.invalidateQueries({ queryKey: ['workflow', workflowId] });
    },
  });

  const updateStageMutation = useMutation({
    mutationFn: ({ workflowId, stageId, data }: { workflowId: number; stageId: number; data: any }) =>
      workflowService.updateStage(workflowId, stageId, data),
    onSuccess: (_, { workflowId }) => {
      queryClient.invalidateQueries({ queryKey: ['workflow', workflowId] });
    },
  });

  const deleteStageMutation = useMutation({
    mutationFn: ({ workflowId, stageId }: { workflowId: number; stageId: number }) =>
      workflowService.deleteStage(workflowId, stageId),
    onSuccess: (_, { workflowId }) => {
      queryClient.invalidateQueries({ queryKey: ['workflow', workflowId] });
    },
  });

  return {
    workflow: data?.data,
    isLoading,
    error,
    updateWorkflow: updateMutation.mutateAsync,
    deleteWorkflow: deleteMutation.mutateAsync,
    addStage: addStageMutation.mutateAsync,
    updateStage: updateStageMutation.mutateAsync,
    deleteStage: deleteStageMutation.mutateAsync,
    isUpdating: updateMutation.isPending,
    isDeleting: deleteMutation.isPending,
    isAddingStage: addStageMutation.isPending,
    isUpdatingStage: updateStageMutation.isPending,
    isDeletingStage: deleteStageMutation.isPending,
  };
}