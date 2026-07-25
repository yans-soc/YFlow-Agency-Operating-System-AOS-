import { useState } from "react";
import { useQuery, useMutation, useQueryClient } from "@tanstack/react-query";
import { Plus } from "lucide-react";
import apiClient from "../../lib/api";
import { ProjectCard } from "./ProjectCard";
import { ProjectModal } from "./ProjectModal";
import { Button } from "@/components/ui/button";

interface ProjectFormValues {
  name: string;
  description?: string;
  status: "planning" | "in_progress" | "completed" | "on_hold";
}

interface Project {
  id: string;
  name: string;
  description: string | null;
  status: "planning" | "in_progress" | "completed" | "on_hold";
  created_at: string;
}

export function ProjectListPage() {
  const queryClient = useQueryClient();
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingProject, setEditingProject] = useState<Project | null>(null);

  const { data, isLoading } = useQuery<{ data: { data: Project[] } }>({
    queryKey: ["projects"],
    queryFn: async () => {
      const response = await apiClient.get("/projects");
      return response.data;
    },
  });

  const createMutation = useMutation({
    mutationFn: (data: ProjectFormValues) => apiClient.post("/projects", data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["projects"] });
      setIsModalOpen(false);
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, data }: { id: string; data: ProjectFormValues }) =>
      apiClient.put(`/projects/${id}`, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["projects"] });
      setIsModalOpen(false);
      setEditingProject(null);
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: string) => apiClient.delete(`/projects/${id}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ["projects"] });
    },
  });

  const handleSubmit = (data: ProjectFormValues) => {
    if (editingProject) {
      updateMutation.mutate({ id: editingProject.id, data });
    } else {
      createMutation.mutate(data);
    }
  };

  const handleEdit = (project: Project) => {
    setEditingProject(project);
    setIsModalOpen(true);
  };

  const handleDelete = (id: string) => {
    if (confirm("Are you sure you want to delete this project?")) {
      deleteMutation.mutate(id);
    }
  };

  const projects = data?.data?.data || [];

  return (
    <div>
      <div className="mb-6 flex items-center justify-between">
        <h1 className="text-h2 font-bold text-neutral-900">Projects</h1>
        <Button
          onClick={() => {
            setEditingProject(null);
            setIsModalOpen(true);
          }}
          className="flex items-center gap-2"
        >
          <Plus className="h-4 w-4" />
          New Project
        </Button>
      </div>

      {isLoading ? (
        <div className="flex h-72 items-center justify-center">
          <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
        </div>
      ) : projects.length === 0 ? (
        <div className="flex h-72 flex-col items-center justify-center rounded-lg border-2 border-dashed border-neutral-200 bg-neutral-50">
          <p className="text-body text-neutral-500">No projects yet</p>
          <Button
            variant="link"
            onClick={() => setIsModalOpen(true)}
            className="mt-4 text-sm font-medium text-primary-600 hover:text-primary-700"
          >
            Create your first project
          </Button>
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
          {projects.map((project) => (
            <ProjectCard
              key={project.id}
              project={project}
              onEdit={handleEdit}
              onDelete={handleDelete}
            />
          ))}
        </div>
      )}

      <ProjectModal
        isOpen={isModalOpen}
        onClose={() => {
          setIsModalOpen(false);
          setEditingProject(null);
        }}
        onSubmit={handleSubmit}
        initialData={editingProject || undefined}
      />
    </div>
  );
}
