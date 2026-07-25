import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { X } from "lucide-react";

const projectSchema = z.object({
  name: z.string().min(2, "Name must be at least 2 characters"),
  description: z.string().optional(),
  status: z.enum(["planning", "in_progress", "completed", "on_hold"]),
});

type ProjectFormValues = z.infer<typeof projectSchema>;

interface ProjectModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSubmit: (data: ProjectFormValues) => void;
  initialData?: ProjectFormValues & { id?: string };
}

export function ProjectModal({
  isOpen,
  onClose,
  onSubmit,
  initialData,
}: ProjectModalProps) {
  const {
    register,
    handleSubmit,
    formState: { errors, isSubmitting },
  } = useForm<ProjectFormValues>({
    resolver: zodResolver(projectSchema),
    defaultValues: initialData || {
      name: "",
      description: "",
      status: "planning",
    },
  });

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div className="w-full max-w-lg rounded-lg bg-white p-24 shadow-xl">
        <div className="mb-24 flex items-center justify-between">
          <h2 className="text-h4 font-semibold text-neutral-900">
            {initialData ? "Edit Project" : "New Project"}
          </h2>
          <button
            onClick={onClose}
            className="rounded-lg p-8 hover:bg-neutral-100"
          >
            <X className="h-20 w-20 text-neutral-500" />
          </button>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-16">
          <div>
            <label
              htmlFor="name"
              className="block mb-4 text-sm font-medium text-neutral-700"
            >
              Project Name
            </label>
            <input
              id="name"
              type="text"
              {...register("name")}
              className="input"
              placeholder="Enter project name"
            />
            {errors.name && (
              <p className="mt-4 text-sm text-danger-600">
                {errors.name.message}
              </p>
            )}
          </div>

          <div>
            <label
              htmlFor="description"
              className="block mb-4 text-sm font-medium text-neutral-700"
            >
              Description
            </label>
            <textarea
              id="description"
              {...register("description")}
              className="input min-h-[100px]"
              placeholder="Enter project description"
            />
            {errors.description && (
              <p className="mt-4 text-sm text-danger-600">
                {errors.description.message}
              </p>
            )}
          </div>

          <div>
            <label
              htmlFor="status"
              className="block mb-4 text-sm font-medium text-neutral-700"
            >
              Status
            </label>
            <select id="status" {...register("status")} className="input">
              <option value="planning">Planning</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="on_hold">On Hold</option>
            </select>
            {errors.status && (
              <p className="mt-4 text-sm text-danger-600">
                {errors.status.message}
              </p>
            )}
          </div>

          <div className="flex gap-8 pt-8">
            <button
              type="button"
              onClick={onClose}
              className="btn btn-secondary flex-1"
            >
              Cancel
            </button>
            <button
              type="submit"
              disabled={isSubmitting}
              className="btn btn-primary flex-1"
            >
              {isSubmitting ? "Saving..." : initialData ? "Update" : "Create"}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
