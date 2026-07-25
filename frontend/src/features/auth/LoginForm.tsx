import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { z } from "zod";
import { useNavigate } from "react-router-dom";
import { useLogin } from "@/hooks/useAuth";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";

const loginSchema = z.object({
  email: z.string().email("Invalid email address"),
  password: z.string().min(1, "Password is required"),
});

type LoginFormValues = z.infer<typeof loginSchema>;

export function LoginForm() {
  const navigate = useNavigate();
  const login = useLogin();

  const {
    register,
    handleSubmit,
    formState: { errors },
    setError,
  } = useForm<LoginFormValues>({
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = async (data: LoginFormValues) => {
    try {
      await login.mutateAsync(data);
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
        if (axiosError.response?.data?.message) {
          setError("root", { message: axiosError.response.data.message });
        }
      }
    }
  };

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
      {login.error && (
        <div className="rounded-md bg-danger-50 p-3">
          <p className="text-sm text-danger-600">
            {login.error instanceof Error
              ? login.error.message
              : "Login failed. Please try again."}
          </p>
        </div>
      )}

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
          {...register("email")}
          placeholder="you@example.com"
        />
        {errors.email && (
          <p className="mt-2 text-sm text-danger-600">{errors.email.message}</p>
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
          {...register("password")}
          placeholder="••••••••"
        />
        {errors.password && (
          <p className="mt-2 text-sm text-danger-600">
            {errors.password.message}
          </p>
        )}
      </div>

      <Button type="submit" disabled={login.isPending} className="w-full">
        {login.isPending ? "Signing in..." : "Sign In"}
      </Button>
    </form>
  );
}
