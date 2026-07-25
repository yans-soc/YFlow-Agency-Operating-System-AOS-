import { MoreVertical } from "lucide-react";

interface Project {
  id: string;
  name: string;
  description: string | null;
  status: "planning" | "in_progress" | "completed" | "on_hold";
  created_at: string;
}

interface ProjectCardProps {
  project: Project;
  onEdit: (project: Project) => void;
  onDelete: (id: string) => void;
}

const statusColors = {
  planning: "bg-blue-100 text-blue-700",
  in_progress: "bg-green-100 text-green-700",
  completed: "bg-gray-100 text-gray-700",
  on_hold: "bg-yellow-100 text-yellow-700",
};

const statusLabels = {
  planning: "Planning",
  in_progress: "In Progress",
  completed: "Completed",
  on_hold: "On Hold",
};

export function ProjectCard({ project, onEdit, onDelete }: ProjectCardProps) {
  return (
    <div className="card group">
      <div className="mb-16 flex items-start justify-between">
        <span
          className={`rounded-full px-12 py-4 text-xs font-medium ${statusColors[project.status]}`}
        >
          {statusLabels[project.status]}
        </span>
        <button
          onClick={() => onEdit(project)}
          className="rounded-lg p-8 text-neutral-400 opacity-0 transition-opacity group-hover:opacity-100 hover:bg-neutral-50 hover:text-neutral-600"
        >
          <MoreVertical className="h-16 w-16" />
        </button>
      </div>

      <h3 className="mb-8 text-h5 font-semibold text-neutral-900">
        {project.name}
      </h3>

      {project.description && (
        <p className="mb-16 line-clamp-2 text-sm text-neutral-600">
          {project.description}
        </p>
      )}

      <div className="flex items-center justify-between pt-16 border-t border-neutral-100">
        <span className="text-xs text-neutral-500">
          Created {new Date(project.created_at).toLocaleDateString()}
        </span>
        <button
          onClick={() => onDelete(project.id)}
          className="text-xs font-medium text-danger-600 hover:text-danger-700"
        >
          Delete
        </button>
      </div>
    </div>
  );
}
