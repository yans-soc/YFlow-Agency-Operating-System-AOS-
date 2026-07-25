import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  dashboardService,
  type DashboardData,
  type DashboardStats,
  type Task,
  type Project,
  type Activity,
  type Notification,
} from "@/services/dashboard";

export function useDashboard() {
  return useQuery<DashboardData>({
    queryKey: ["dashboard"],
    queryFn: () => dashboardService.getDashboard(),
    staleTime: 1000 * 60 * 5,
  });
}

export function useDashboardStats() {
  return useQuery<DashboardStats>({
    queryKey: ["dashboard", "stats"],
    queryFn: () => dashboardService.getStats(),
    staleTime: 1000 * 60 * 5,
  });
}

export function useTodayTasks() {
  return useQuery<Task[]>({
    queryKey: ["dashboard", "todayTasks"],
    queryFn: () => dashboardService.getTodayTasks(),
    staleTime: 1000 * 60 * 5,
  });
}

export function useActiveProjects() {
  return useQuery<Project[]>({
    queryKey: ["dashboard", "activeProjects"],
    queryFn: () => dashboardService.getActiveProjects(),
    staleTime: 1000 * 60 * 5,
  });
}

export function useRecentActivities() {
  return useQuery<Activity[]>({
    queryKey: ["dashboard", "recentActivities"],
    queryFn: () => dashboardService.getRecentActivities(),
    staleTime: 1000 * 60 * 5,
  });
}

export function useNotifications() {
  return useQuery<Notification[]>({
    queryKey: ["dashboard", "notifications"],
    queryFn: () => dashboardService.getNotifications(),
    staleTime: 1000 * 60 * 1,
  });
}

export function useMarkNotificationRead() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: string) => dashboardService.markNotificationRead(id),
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ["dashboard", "notifications"],
      });
    },
  });
}

export function useMarkAllNotificationsRead() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: () => dashboardService.markAllNotificationsRead(),
    onSuccess: () => {
      queryClient.invalidateQueries({
        queryKey: ["dashboard", "notifications"],
      });
    },
  });
}
