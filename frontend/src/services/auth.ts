import apiClient from "@/lib/api";

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterData {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  workspace_id?: string;
  workspace_name?: string;
}

export interface AuthResponse {
  person: {
    id: string;
    name: string;
    email: string;
    role: string;
    status: string;
    workspace_id?: string;
  };
  token: string;
}

export const authService = {
  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response = await apiClient.post<{
      success: boolean;
      data: AuthResponse;
    }>("/auth/login", credentials);
    return response.data.data;
  },

  async register(data: RegisterData): Promise<AuthResponse> {
    const response = await apiClient.post<{
      success: boolean;
      data: AuthResponse;
    }>("/auth/register", data);
    return response.data.data;
  },

  async logout(): Promise<void> {
    await apiClient.post("/auth/logout");
  },

  async getCurrentUser(): Promise<AuthResponse["person"]> {
    const response = await apiClient.get<{
      success: boolean;
      data: AuthResponse["person"];
    }>("/auth/me");
    return response.data.data;
  },
};
