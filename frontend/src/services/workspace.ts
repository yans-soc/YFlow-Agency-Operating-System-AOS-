import apiClient from "@/lib/api";

export interface Workspace {
  id: string;
  name: string;
  slug: string;
  description?: string;
  logo?: string;
  owner_id: string;
  created_at: string;
  updated_at: string;
}

export interface WorkspaceMember {
  id: string;
  user_id: string;
  name: string;
  email: string;
  role: string;
  avatar?: string;
  department?: string;
  position?: string;
  joined_at: string;
}

export interface WorkspaceStats {
  total_projects: number;
  active_projects: number;
  total_members: number;
  total_tasks: number;
}

export const workspaceService = {
  async getCurrentWorkspace(): Promise<Workspace> {
    const response = await apiClient.get<{ success: boolean; data: Workspace }>(
      "/workspaces/current",
    );
    return response.data.data;
  },

  async getWorkspace(): Promise<Workspace> {
    const response = await apiClient.get<{ success: boolean; data: Workspace }>(
      "/workspaces",
    );
    return response.data.data;
  },

  async updateWorkspace(data: Partial<Workspace>): Promise<Workspace> {
    const response = await apiClient.put<{ success: boolean; data: Workspace }>(
      "/workspaces",
      data,
    );
    return response.data.data;
  },

  async getMembers(): Promise<WorkspaceMember[]> {
    const response = await apiClient.get<{
      success: boolean;
      data: WorkspaceMember[];
    }>("/workspaces/members");
    return response.data.data;
  },

  async inviteMember(email: string, role: string): Promise<WorkspaceMember> {
    const response = await apiClient.post<{
      success: boolean;
      data: WorkspaceMember;
    }>("/workspaces/members", { email, role });
    return response.data.data;
  },

  async removeMember(memberId: string): Promise<void> {
    await apiClient.delete(`/workspaces/members/${memberId}`);
  },

  async updateMemberRole(
    memberId: string,
    role: string,
  ): Promise<WorkspaceMember> {
    const response = await apiClient.put<{
      success: boolean;
      data: WorkspaceMember;
    }>(`/workspaces/members/${memberId}`, { role });
    return response.data.data;
  },

  async getStats(): Promise<WorkspaceStats> {
    const response = await apiClient.get<{
      success: boolean;
      data: WorkspaceStats;
    }>("/workspaces/stats");
    return response.data.data;
  },
};
