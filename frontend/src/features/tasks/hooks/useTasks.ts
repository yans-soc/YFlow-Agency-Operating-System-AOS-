import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { taskService } from '../services/task';
import type { CreateTaskRequest, UpdateTaskRequest } from '../types/task';

export function useTasks(projectId?: number) {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['tasks', projectId].filter(Boolean),
    queryFn: () => taskService.list(projectId),
  });

  const createMutation = useMutation({
    mutationFn: (data: CreateTaskRequest) => taskService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['tasks'] });
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateTaskRequest }) =>
      taskService.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['tasks'] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => taskService.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['tasks'] });
    },
  });

  return {
    tasks: data?.data || [],
    isLoading,
    error,
    createTask: createMutation.mutateAsync,
    updateTask: updateMutation.mutateAsync,
    deleteTask: deleteMutation.mutateAsync,
    isCreating: createMutation.isPending,
    isUpdating: updateMutation.isPending,
    isDeleting: deleteMutation.isPending,
  };
}

export function useTask(id: number) {
  const queryClient = useQueryClient();

  const { data, isLoading, error } = useQuery({
    queryKey: ['task', id],
    queryFn: () => taskService.get(id),
    enabled: !!id,
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateTaskRequest }) =>
      taskService.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['task', id] });
      queryClient.invalidateQueries({ queryKey: ['tasks'] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (taskId: number) => taskService.delete(taskId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['tasks'] });
    },
  });

  const addAssigneeMutation = useMutation({
    mutationFn: ({ taskId, personId }: { taskId: number; personId: number }) =>
      taskService.addAssignee(taskId, personId),
    onSuccess: (_, { taskId }) => {
      queryClient.invalidateQueries({ queryKey: ['task', taskId] });
    },
  });

  const removeAssigneeMutation = useMutation({
    mutationFn: ({ taskId, assigneeId }: { taskId: number; assigneeId: number }) =>
      taskService.removeAssignee(taskId, assigneeId),
    onSuccess: (_, { taskId }) => {
      queryClient.invalidateQueries({ queryKey: ['task', taskId] });
    },
  });

  const addChecklistMutation = useMutation({
    mutationFn: ({ taskId, title }: { taskId: number; title: string }) =>
      taskService.addChecklist(taskId, title),
    onSuccess: (_, { taskId }) => {
      queryClient.invalidateQueries({ queryKey: ['task', taskId] });
    },
  });

  const toggleChecklistMutation = useMutation({
    mutationFn: ({ taskId, checklistId }: { taskId: number; checklistId: number }) =>
      taskService.toggleChecklist(taskId, checklistId),
    onSuccess: (_, { taskId }) => {
      queryClient.invalidateQueries({ queryKey: ['task', taskId] });
    },
  });

  const deleteChecklistMutation = useMutation({
    mutationFn: ({ taskId, checklistId }: { taskId: number; checklistId: number }) =>
      taskService.deleteChecklist(taskId, checklistId),
    onSuccess: (_, { taskId }) => {
      queryClient.invalidateQueries({ queryKey: ['task', taskId] });
    },
  });

  return {
    task: data?.data,
    isLoading,
    error,
    updateTask: updateMutation.mutateAsync,
    deleteTask: deleteMutation.mutateAsync,
    addAssignee: addAssigneeMutation.mutateAsync,
    removeAssignee: removeAssigneeMutation.mutateAsync,
    addChecklist: addChecklistMutation.mutateAsync,
    toggleChecklist: toggleChecklistMutation.mutateAsync,
    deleteChecklist: deleteChecklistMutation.mutateAsync,
    isUpdating: updateMutation.isPending,
    isDeleting: deleteMutation.isPending,
  };
}