import { useEffect, useState } from "react";
import { useAuthStore, type User } from "../stores/authStore";

type AuthProviderProps = {
  children: React.ReactNode;
};

export function AuthProvider({ children }: AuthProviderProps) {
  const { login, logout } = useAuthStore();
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem("auth_token");
    const storedUser = localStorage.getItem("user");

    if (!token || !storedUser) {
      setIsLoading(false);
      return;
    }

    try {
      const user: User = JSON.parse(storedUser);
      login(user, token);
    } catch {
      logout();
    } finally {
      setIsLoading(false);
    }
  }, [login, logout]);

  if (isLoading) {
    return (
      <div className="flex h-screen items-center justify-center">
        <div className="h-8 w-8 animate-spin rounded-full border-4 border-primary border-t-transparent" />
      </div>
    );
  }

  return <>{children}</>;
}
