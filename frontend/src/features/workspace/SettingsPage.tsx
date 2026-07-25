import { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  useWorkspace,
  useUpdateWorkspace,
  useWorkspaceMembers,
  useInviteMember,
  useRemoveMember,
  useUpdateMemberRole,
} from "@/hooks/useWorkspace";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { useAuthStore } from "@/stores/authStore";
import { useCurrentVersion } from "@/features/admin/hooks/useReleases";

export function SettingsPage() {
  const { user } = useAuthStore();
  const { data: workspace, isLoading } = useWorkspace();
  const { data: members } = useWorkspaceMembers();
  const { version } = useCurrentVersion();
  const updateWorkspace = useUpdateWorkspace();
  const inviteMember = useInviteMember();
  const removeMember = useRemoveMember();
  const updateMemberRole = useUpdateMemberRole();

  const [workspaceName, setWorkspaceName] = useState("");
  const [inviteEmail, setInviteEmail] = useState("");
  const [inviteRole, setInviteRole] = useState("member");

  if (isLoading) {
    return (
      <div className="flex-1 overflow-auto bg-neutral-50 p-6">
        <div className="animate-pulse space-y-4">
          <div className="h-8 w-48 rounded bg-neutral-200" />
          <div className="h-32 rounded bg-neutral-200" />
        </div>
      </div>
    );
  }

  const handleUpdateWorkspace = () => {
    updateWorkspace.mutate({ name: workspaceName });
  };

  const handleInvite = () => {
    if (!inviteEmail) return;
    inviteMember.mutate(
      { email: inviteEmail, role: inviteRole },
      {
        onSuccess: () => {
          setInviteEmail("");
        },
      },
    );
  };

  const handleRemoveMember = (memberId: string) => {
    if (confirm("Are you sure you want to remove this member?")) {
      removeMember.mutate(memberId);
    }
  };

  const handleUpdateRole = (memberId: string, role: string) => {
    updateMemberRole.mutate({ memberId, role });
  };

  return (
    <div className="flex-1 overflow-auto bg-neutral-50 p-6">
      <div className="mx-auto max-w-4xl space-y-6">
        <div className="flex items-center justify-between">
          <h1 className="text-h3 font-bold text-neutral-900">
            Workspace Settings
          </h1>
          {version && (
            <div className="text-sm text-neutral-500">
              Version: <span className="font-mono">{version.formatted_version}</span>
            </div>
          )}
        </div>

        {/* Workspace Info */}
        <Card>
          <CardHeader>
            <CardTitle>Workspace Information</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div>
              <label className="mb-1 block text-sm font-medium text-neutral-700">
                Workspace Name
              </label>
              <div className="flex gap-2">
                <Input
                  type="text"
                  value={workspaceName || workspace?.name || ""}
                  onChange={(e) => setWorkspaceName(e.target.value)}
                  placeholder="Enter workspace name"
                />
                <Button
                  onClick={handleUpdateWorkspace}
                  disabled={updateWorkspace.isPending}
                >
                  Save
                </Button>
              </div>
            </div>
            <div>
              <label className="mb-1 block text-sm font-medium text-neutral-700">
                Slug
              </label>
              <p className="text-sm text-neutral-500">{workspace?.slug}</p>
            </div>
          </CardContent>
        </Card>

        {/* Invite Members */}
        <Card>
          <CardHeader>
            <CardTitle>Invite Members</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex gap-2">
              <Input
                type="email"
                value={inviteEmail}
                onChange={(e) => setInviteEmail(e.target.value)}
                placeholder="Enter email address"
                className="flex-1"
              />
              <select
                value={inviteRole}
                onChange={(e) => setInviteRole(e.target.value)}
                className="rounded-lg border border-neutral-300 px-3 py-2 text-sm"
              >
                <option value="admin">Admin</option>
                <option value="member">Member</option>
                <option value="viewer">Viewer</option>
              </select>
              <Button
                onClick={handleInvite}
                disabled={inviteMember.isPending || !inviteEmail}
              >
                Invite
              </Button>
            </div>
          </CardContent>
        </Card>

        {/* Members List */}
        <Card>
          <CardHeader>
            <CardTitle>Team Members ({members?.length || 0})</CardTitle>
          </CardHeader>
          <CardContent>
            {members && members.length > 0 ? (
              <div className="divide-y divide-neutral-100">
                {members.map((member) => (
                  <div
                    key={member.id}
                    className="flex items-center justify-between py-3"
                  >
                    <div className="flex items-center gap-3">
                      <div className="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-primary-700">
                        {member.name?.charAt(0).toUpperCase()}
                      </div>
                      <div>
                        <p className="font-medium text-neutral-900">
                          {member.name}
                        </p>
                        <p className="text-sm text-neutral-500">
                          {member.email}
                        </p>
                      </div>
                    </div>
                    <div className="flex items-center gap-2">
                      <select
                        value={member.role}
                        onChange={(e) =>
                          handleUpdateRole(member.id, e.target.value)
                        }
                        className="rounded-lg border border-neutral-300 px-2 py-1 text-sm"
                        disabled={member.role === "owner"}
                      >
                        <option value="owner">Owner</option>
                        <option value="admin">Admin</option>
                        <option value="member">Member</option>
                        <option value="viewer">Viewer</option>
                      </select>
                      {member.role !== "owner" &&
                        member.id !== user?.id && (
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => handleRemoveMember(member.id)}
                            className="text-danger-600 hover:text-danger-700"
                          >
                            Remove
                          </Button>
                        )}
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <p className="text-center text-neutral-500">No members found</p>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
