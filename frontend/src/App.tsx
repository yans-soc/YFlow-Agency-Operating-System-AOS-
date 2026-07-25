import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import { AuthProvider } from "./providers/AuthProvider";
import { QueryProvider } from "./providers/QueryProvider";
import { ProtectedRoute } from "./components/ProtectedRoute";
import { useAuthStore } from "./stores/authStore";
import { LoginPage } from "./features/auth/LoginPage";
import { RegisterPage } from "./features/auth/RegisterPage";
import { WorkspaceLayout } from "./features/workspace/WorkspaceLayout";
import { DashboardPage } from "./features/dashboard/DashboardPage";
import { SettingsPage } from "./features/workspace/SettingsPage";
import { ProjectListPage } from "./features/projects/ProjectListPage";
import { ProjectDetailPage } from "./features/projects/ProjectDetailPage";
import { ReleaseManagementPage } from "./features/admin/pages/ReleaseManagementPage";
import { ChangelogPage } from "./features/admin/pages/ChangelogPage";

function App() {
  const { isAuthenticated } = useAuthStore();

  return (
    <QueryProvider>
      <BrowserRouter>
        <AuthProvider>
          <Routes>
            <Route
              path="/login"
              element={
                isAuthenticated ? (
                  <Navigate to="/dashboard" replace />
                ) : (
                  <LoginPage />
                )
              }
            />
            <Route
              path="/register"
              element={
                isAuthenticated ? (
                  <Navigate to="/dashboard" replace />
                ) : (
                  <RegisterPage />
                )
              }
            />
            <Route
              path="/dashboard"
              element={
                <ProtectedRoute>
                  <WorkspaceLayout />
                </ProtectedRoute>
              }
            >
              <Route index element={<DashboardPage />} />
              <Route path="projects" element={<ProjectListPage />} />
              <Route path="projects/:id" element={<ProjectDetailPage />} />
              <Route path="tasks" element={<div>Tasks Page</div>} />
              <Route path="people" element={<div>People Page</div>} />
              <Route path="calendar" element={<div>Calendar Page</div>} />
              <Route path="notes" element={<div>Notes Page</div>} />
              <Route path="files" element={<div>Files Page</div>} />
              <Route path="ai" element={<div>AI Assistant Page</div>} />
              <Route path="settings" element={<SettingsPage />} />
            </Route>
            <Route
              path="/admin/releases"
              element={
                <ProtectedRoute>
                  <ReleaseManagementPage />
                </ProtectedRoute>
              }
            />
            <Route path="/changelog" element={<ChangelogPage />} />
            <Route path="/" element={<Navigate to="/dashboard" replace />} />
          </Routes>
        </AuthProvider>
      </BrowserRouter>
    </QueryProvider>
  );
}

export default App;
