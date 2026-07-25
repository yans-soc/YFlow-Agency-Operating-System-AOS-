import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { Bell, ChevronDown, LogOut, Settings, User } from "lucide-react";
import { useAuthStore } from "@/stores/authStore";
import { useLogout } from "@/hooks/useAuth";
import { VersionBadge } from "@/features/admin/components/VersionBadge";

export function Header() {
  const navigate = useNavigate();
  const { user } = useAuthStore();
  const logout = useLogout();
  const [isDropdownOpen, setIsDropdownOpen] = useState(false);

  const handleLogout = async () => {
    try {
      await logout.mutateAsync();
      navigate("/login");
    } catch {
      // Error handled by useLogout
    }
  };

  return (
    <header className="flex h-16 items-center justify-between border-b border-neutral-200 bg-white px-6">
      <div className="flex items-center gap-2">
        <h2 className="text-h5 font-semibold text-neutral-900">Workspace</h2>
        <VersionBadge />
      </div>

      <div className="flex items-center gap-2">
        <button className="relative rounded-lg p-2 text-neutral-600 hover:bg-neutral-50">
          <Bell className="h-5 w-5" />
          <span className="absolute right-1 top-1 flex h-2 w-2">
            <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-danger-400 opacity-75"></span>
            <span className="relative inline-flex h-2 w-2 rounded-full bg-danger-500"></span>
          </span>
        </button>

        <div className="relative">
          <button
            onClick={() => setIsDropdownOpen(!isDropdownOpen)}
            className="flex items-center gap-2 rounded-lg p-2 hover:bg-neutral-50"
          >
            <div className="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-sm font-medium text-primary-700">
              {user?.name?.charAt(0).toUpperCase() || "U"}
            </div>
            <span className="text-sm font-medium text-neutral-700">
              {user?.name || "User"}
            </span>
            <ChevronDown className="h-4 w-4 text-neutral-400" />
          </button>

          {isDropdownOpen && (
            <div className="absolute right-0 mt-2 w-48 origin-top-right rounded-lg border border-neutral-200 bg-white py-1 shadow-lg">
              <button
                onClick={() => {
                  setIsDropdownOpen(false);
                  navigate("/profile");
                }}
                className="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-50"
              >
                <User className="h-4 w-4" />
                Profile
              </button>
              <button
                onClick={() => {
                  setIsDropdownOpen(false);
                  navigate("/settings");
                }}
                className="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-neutral-700 hover:bg-neutral-50"
              >
                <Settings className="h-4 w-4" />
                Settings
              </button>
              <hr className="my-1" />
              <button
                onClick={handleLogout}
                disabled={logout.isPending}
                className="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-danger-600 hover:bg-neutral-50 disabled:opacity-50"
              >
                <LogOut className="h-4 w-4" />
                {logout.isPending ? "Signing out..." : "Sign out"}
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  );
}
