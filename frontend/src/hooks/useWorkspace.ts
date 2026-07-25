import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import {
  workspaceService,
  type Workspace,
  type WorkspaceMember,
  type WorkspaceStats,
} from "@/services/workspace";

export function useWorkspace() {
  return useQuery<Workspace>({
    queryKey: ["workspace"],
    queryFn: () => workspaceService.getWorkspace(),
  });
}

export function useWorkspaceStats() {
  return useQuery<WorkspaceStats>({
    queryKey: ["workspace", "stats"],
    queryFn: () => workspaceService.getStats(),
  });
}

export function useWorkspaceMembers() {
  return useQuery<WorkspaceMember[]>({
    queryKey: ["workspace", "members"],
    queryFn: () => workspaceService.getMembers(),
  });
}

export function useUpdateWorkspace() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: Partial<Workspace>) =>
      workspaceService.updateWorkspace(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["workspace"] });
    },
  });
}

export function useInviteMember() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ email, role }: { email: string; role: string }) =>
      workspaceService.inviteMember(email, role),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["workspace", "members"] });
    },
  });
}

export function useRemoveMember() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (memberId: string) => workspaceService.removeMember(memberId),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["workspace", "members"] });
    },
  });
}

export function useUpdateMemberRole() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ memberId, role }: { memberId: string; role: string }) =>
      workspaceService.updateMemberRole(memberId, role),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["workspace", "members"] });
    },
  });
}
