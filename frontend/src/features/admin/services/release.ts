import apiClient from '@/lib/api';
import type { Release, ReleaseFormData, CurrentVersion } from '../types/release';

export const releaseService = {
  list: (params?: { page?: number; per_page?: number }) =>
    apiClient.get<{ data: Release[]; meta: any }>('/releases', { params }),

  get: (id: number) =>
    apiClient.get<Release>(`/releases/${id}`),

  create: (data: ReleaseFormData) =>
    apiClient.post<Release>('/releases', data),

  update: (id: number, data: Partial<ReleaseFormData>) =>
    apiClient.put<Release>(`/releases/${id}`, data),

  delete: (id: number) =>
    apiClient.delete(`/releases/${id}`),

  setCurrent: (id: number) =>
    apiClient.post<Release>(`/releases/${id}/set-current`),

  getCurrentVersion: () =>
    apiClient.get<CurrentVersion>('/version/current'),
};
