import { useParams, useNavigate } from "react-router-dom";
import { useQuery } from "@tanstack/react-query";
import { ArrowLeft, Users, Calendar, CheckSquare } from "lucide-react";
import apiClient from "../../lib/api";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";

interface Project {
  id: string;
  name: string;
  description: string | null;
  status: string;
  created_at: string;
}

export function ProjectDetailPage() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();

  const { data, isLoading } = useQuery<{ data: { data: Project } }>({
    queryKey: ["project", id],
    queryFn: async () => {
      const response = await apiClient.get(`/projects/${id}`);
      return response.data;
    },
    enabled: !!id,
  });

  const project = data?.data?.data;

  if (isLoading) {
    return (
      <div className="flex h-72 items-center justify-center">
        <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
      </div>
    );
  }

  if (!project) {
    return (
      <div className="flex h-72 flex-col items-center justify-center">
        <p className="text-body text-neutral-500">Project not found</p>
        <Button
          onClick={() => navigate("/dashboard/projects")}
          className="mt-4"
        >
          Back to Projects
        </Button>
      </div>
    );
  }

  return (
    <div>
      <Button
        onClick={() => navigate("/dashboard/projects")}
        variant="ghost"
        className="mb-6 flex items-center gap-2"
      >
        <ArrowLeft className="h-4 w-4" />
        Back to Projects
      </Button>

      <div className="mb-6">
        <h1 className="mb-2 text-3xl font-bold text-neutral-900">
          {project.name}
        </h1>
        <span className="inline-block rounded-full bg-primary-100 px-3 py-1 text-xs font-medium text-primary-700 capitalize">
          {project.status.replace("_", " ")}
        </span>
      </div>

      {project.description && (
        <Card className="mb-6">
          <CardContent className="pt-6">
            <h2 className="mb-4 text-lg font-semibold text-neutral-900">
              Description
            </h2>
            <p className="text-body text-neutral-600 whitespace-pre-wrap">
              {project.description}
            </p>
          </CardContent>
        </Card>
      )}

      <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
        <Card>
          <CardContent className="pt-6">
            <div className="mb-4 flex items-center gap-3">
              <Users className="h-5 w-5 text-neutral-400" />
              <h3 className="text-base font-medium text-neutral-900">
                Team Members
              </h3>
            </div>
            <p className="text-2xl font-bold text-neutral-900">0</p>
            <p className="text-sm text-neutral-500">Active members</p>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="mb-4 flex items-center gap-3">
              <CheckSquare className="h-5 w-5 text-neutral-400" />
              <h3 className="text-base font-medium text-neutral-900">Tasks</h3>
            </div>
            <p className="text-2xl font-bold text-neutral-900">0</p>
            <p className="text-sm text-neutral-500">Total tasks</p>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="pt-6">
            <div className="mb-4 flex items-center gap-3">
              <Calendar className="h-5 w-5 text-neutral-400" />
              <h3 className="text-base font-medium text-neutral-900">
                Created
              </h3>
            </div>
            <p className="text-2xl font-bold text-neutral-900">
              {new Date(project.created_at).toLocaleDateString()}
            </p>
            <p className="text-sm text-neutral-500">Start date</p>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
