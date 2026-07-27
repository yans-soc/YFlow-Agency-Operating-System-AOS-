import axios, { AxiosError } from "axios";
import type { AxiosResponse, InternalAxiosRequestConfig } from "axios";
import { toast } from "sonner";

const API_BASE_URL =
  import.meta.env.VITE_API_BASE_URL || "http://localhost:8000/api/v1";

export const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  withCredentials: true,
});

// Request interceptor - attach auth token
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem("auth_token");
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error: AxiosError) => Promise.reject(error),
);

// Response interceptor - handle errors globally
apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError<ApiError>) => {
    const status = error.response?.status;
    const message = error.response?.data?.message;

    if (status === 401) {
      // Only redirect if we actually had a session; avoids loop on login page.
      const hadToken = Boolean(localStorage.getItem("auth_token"));
      localStorage.removeItem("auth_token");
      localStorage.removeItem("user");
      if (hadToken && !window.location.pathname.startsWith("/login")) {
        toast.error("Session expired. Please sign in again.");
        window.location.href = "/login";
      }
    } else if (status === 403) {
      toast.error(message || "You do not have permission to do that.");
    } else if (status === 422) {
      // Validation errors are surfaced by forms; keep a soft fallback.
      toast.error(message || "Please check the form and try again.");
    } else if (status && status >= 500) {
      toast.error("Something went wrong on our end. Please try again.");
    } else if (!error.response) {
      toast.error("Network error. Check your connection and try again.");
    }

    return Promise.reject(error);
  },
);

/**
 * Standard success envelope used across the YFlow API:
 *   { success: true, data: T, message?: string }
 */
export type ApiEnvelope<T = unknown> = {
  success?: boolean;
  data: T;
  message?: string;
};

export type ApiResponse<T = unknown> = ApiEnvelope<T>;

export type ApiError = {
  success?: boolean;
  message: string;
  errors?: Record<string, string[]>;
};

/**
 * Laravel length-aware paginator shape (what index endpoints return
 * inside `data`).
 */
export interface Paginated<T> {
  data: T[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number | null;
  to: number | null;
}

/**
 * Unwrap the `{ success, data }` envelope so callers work directly
 * with the payload. Falls back to the raw body if the envelope is absent.
 */
export function unwrap<T>(response: AxiosResponse<ApiEnvelope<T>>): T {
  const body = response.data;
  if (body && typeof body === "object" && "data" in body) {
    return body.data;
  }
  return body as unknown as T;
}

/**
 * Extract a flat map of the first validation message per field.
 * Handy for React Hook Form `setError` integration.
 */
export function getValidationErrors(
  error: unknown,
): Record<string, string> | null {
  if (error instanceof AxiosError) {
    const errors = error.response?.data?.errors as
      | Record<string, string[]>
      | undefined;
    if (errors) {
      return Object.fromEntries(
        Object.entries(errors).map(([key, msgs]) => [key, msgs[0]]),
      );
    }
  }
  return null;
}

export default apiClient;
