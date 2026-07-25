import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { releaseService } from '../services/release';
import type { Release, ReleaseFormData } from '../types/release';

export function useReleases() {
  const queryClient = useQueryClient();

  const { data: releases, isLoading: loadingReleases } = useQuery({
    queryKey: ['releases'],
    queryFn: () => releaseService.list().then((res) => res.data),
  });

  const createMutation = useMutation({
    mutationFn: (data: ReleaseFormData) => releaseService.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['releases'] });
      queryClient.invalidateQueries({ queryKey: ['currentVersion'] });
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: number; data: Partial<ReleaseFormData> }) =>
      releaseService.update(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['releases'] });
      queryClient.invalidateQueries({ queryKey: ['currentVersion'] });
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => releaseService.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['releases'] });
    },
  });

  const setCurrentMutation = useMutation({
    mutationFn: (id: number) => releaseService.setCurrent(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['releases'] });
      queryClient.invalidateQueries({ queryKey: ['currentVersion'] });
    },
  });

  return {
    releases,
    loadingReleases,
    createRelease: createMutation.mutateAsync,
    updating: createMutation.isPending || updateMutation.isPending,
    deleting: deleteMutation.isPending,
    settingCurrent: setCurrentMutation.isPending,
    updateRelease: updateMutation.mutateAsync,
    deleteRelease: deleteMutation.mutateAsync,
    setCurrentRelease: setCurrentMutation.mutateAsync,
  };
}

export function useCurrentVersion() {
  const { data: version, isLoading } = useQuery({
    queryKey: ['currentVersion'],
    queryFn: () => releaseService.getCurrentVersion().then((res) => res.data),
    staleTime: 1000 * 60 * 5, // 5 minutes
  });

  return { version, loading: isLoading };
}