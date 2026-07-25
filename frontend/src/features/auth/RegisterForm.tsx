import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useNavigate } from "react-router-dom";
import { useRegister } from "@/hooks/useAuth";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";

const registerSchema = z
  .object({
    name: z.string().min(2, "Name must be at least 2 characters"),
    email: z.string().email("Invalid email address"),
    password: z.string().min(8, "Password must be at least 8 characters"),
    password_confirmation: z.string(),
    workspace_name: z
      .string()
      .min(2, "Workspace name must be at least 2 characters"),
  })
  .refine((data) => data.password === data.password_confirmation, {
    message: "Passwords don't match",
    path: ["password_confirmation"],
  });

type RegisterFormValues = z.infer<typeof registerSchema>;

export function RegisterForm() {
  const navigate = useNavigate();
  const register = useRegister();

  const {
    register: registerField,
    handleSubmit,
    formState: { errors },
    setError,
  } = useForm<RegisterFormValues>({
    resolver: zodResolver(registerSchema),
  });

  const onSubmit = async (data: RegisterFormValues) => {
    try {
      await register.mutateAsync({
        name: data.name,
        email: data.email,
        password: data.password,
        password_confirmation: data.password_confirmation,
        workspace_name: data.workspace_name,
      });
      navigate("/dashboard/projects");
    } catch (error: unknown) {
      if (error && typeof error === "object" && "response" in error) {
        const axiosError = error as {
          response?: {
            data?: {
              message?: string;
              errors?: Record<string, string[]>;
            };
          };
        };
        if (axiosError.response?.data?.errors) {
          const backendErrors = axiosError.response.data.errors;
          Object.entries(backendErrors).forEach(([field, messages]) => {
            if (Array.isArray(messages) && messages.length > 0) {
              setError(field as keyof RegisterFormValues, {
                message: messages[0],
              });
            }
          });
        } else if (axiosError.response?.data?.message) {
          setError("root", { message: axiosError.response.data.message });
        }
      }
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
      {register.error && (
        <div className="rounded-md bg-danger-50 p-3">
          <p className="text-sm text-danger-600">
            {register.error instanceof Error
              ? register.error.message
              : "Registration failed. Please try again."}
          </p>
        </div>
      )}

      <div>
        <label
          htmlFor="name"
          className="mb-2 block text-sm font-medium text-neutral-700"
        >
          Full Name
        </label>
        <Input
          id="name"
          type="text"
          {...registerField("name")}
          placeholder="John Doe"
        />
        {errors.name && (
          <p className="mt-2 text-sm text-danger-600">{errors.name.message}</p>
        )}
      </div>

      <div>
        <label
          htmlFor="email"
          className="mb-2 block text-sm font-medium text-neutral-700"
        >
          Email
        </label>
        <Input
          id="email"
          type="email"
          {...registerField("email")}
          placeholder="you@example.com"
        />
        {errors.email && (
          <p className="mt-2 text-sm text-danger-600">{errors.email.message}</p>
        )}
      </div>

      <div>
        <label
          htmlFor="workspace_name"
          className="mb-2 block text-sm font-medium text-neutral-700"
        >
          Workspace Name
        </label>
        <Input
          id="workspace_name"
          type="text"
          {...registerField("workspace_name")}
          placeholder="My Agency"
        />
        {errors.workspace_name && (
          <p className="mt-2 text-sm text-danger-600">
            {errors.workspace_name.message}
          </p>
        )}
      </div>

      <div>
        <label
          htmlFor="password"
          className="mb-2 block text-sm font-medium text-neutral-700"
        >
          Password
        </label>
        <Input
          id="password"
          type="password"
          {...registerField("password")}
          placeholder="••••••••"
        />
        {errors.password && (
          <p className="mt-2 text-sm text-danger-600">
            {errors.password.message}
          </p>
        )}
      </div>

      <div>
        <label
          htmlFor="password_confirmation"
          className="mb-2 block text-sm font-medium text-neutral-700"
        >
          Confirm Password
        </label>
        <Input
          id="password_confirmation"
          type="password"
          {...registerField("password_confirmation")}
          placeholder="••••••••"
        />
        {errors.password_confirmation && (
          <p className="mt-2 text-sm text-danger-600">
            {errors.password_confirmation.message}
          </p>
        )}
      </div>

      <Button type="submit" disabled={register.isPending} className="w-full">
        {register.isPending ? "Creating account..." : "Sign Up"}
      </Button>
    </form>
  );
}
