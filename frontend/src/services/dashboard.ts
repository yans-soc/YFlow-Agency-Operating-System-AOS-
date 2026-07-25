import apiClient from "@/lib/api";

export interface Task {
  id: string;
  title: string;
  description?: string;
  status: string;
  priority: string;
  due_date?: string;
  project_id: string;
  project_name?: string;
  workflow_stage_id?: string;
  workflow_stage_name?: string;
  assignees?: Array<{
    id: string;
    name: string;
    avatar?: string;
  }>;
}

export interface Project {
  id: string;
  name: string;
  description?: string;
  status: string;
  color?: string;
  start_date?: string;
  end_date?: string;
  member_count?: number;
}

export interface Activity {
  id: string;
  type: string;
  description: string;
  user_id: string;
  user_name: string;
  created_at: string;
}

export interface Notification {
  id: string;
  title: string;
  message: string;
  type: string;
  read: boolean;
  created_at: string;
}

export interface DashboardStats {
  total_projects: number;
  active_projects: number;
  total_tasks: number;
  completed_tasks: number;
  pending_tasks: number;
  overdue_tasks: number;
}

export interface DashboardData {
  stats: DashboardStats;
  today_tasks: Task[];
  upcoming_tasks: Task[];
  active_projects: Project[];
  recent_activities: Activity[];
  notifications: Notification[];
}

export const dashboardService = {
  async getDashboard(): Promise<DashboardData> {
    const response = await apiClient.get<{
      success: boolean;
      data: DashboardData;
    }>("/dashboard");
    return response.data.data;
  },

  async getStats(): Promise<DashboardStats> {
    const response = await apiClient.get<{
      success: boolean;
      data: DashboardStats;
    }>("/dashboard/stats");
    return response.data.data;
  },

  async getTodayTasks(): Promise<Task[]> {
    const response = await apiClient.get<{ success: boolean; data: Task[] }>(
      "/dashboard/today-tasks",
    );
    return response.data.data;
  },

  async getActiveProjects(): Promise<Project[]> {
    const response = await apiClient.get<{ success: boolean; data: Project[] }>(
      "/dashboard/active-projects",
    );
    return response.data.data;
  },

  async getRecentActivities(): Promise<Activity[]> {
    const response = await apiClient.get<{
      success: boolean;
      data: Activity[];
    }>("/dashboard/recent-activities");
    return response.data.data;
  },

  async getNotifications(): Promise<Notification[]> {
    const response = await apiClient.get<{
      success: boolean;
      data: Notification[];
    }>("/dashboard/notifications");
    return response.data.data;
  },

  async markNotificationRead(id: string): Promise<void> {
    await apiClient.put(`/dashboard/notifications/${id}/read`);
  },

  async markAllNotificationsRead(): Promise<void> {
    await apiClient.put("/dashboard/notifications/read-all");
  },
};
