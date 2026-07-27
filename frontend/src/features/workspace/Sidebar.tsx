import { NavLink } from "react-router-dom";
import {
  LayoutDashboard,
  LayoutGrid,
  CheckSquare,
  Users,
  Calendar,
  FileText,
  Folder,
  Bell,
  Settings,
  GitCommit,
  BookOpen,
} from "lucide-react";
import { useAuthStore } from "@/stores/authStore";


const navigation = [
  { name: "Dashboard", to: "/dashboard", icon: LayoutDashboard, exact: true },
  { name: "Projects", to: "/dashboard/projects", icon: LayoutGrid },
  { name: "My Tasks", to: "/dashboard/tasks", icon: CheckSquare },
  { name: "People", to: "/dashboard/people", icon: Users },
  { name: "Calendar", to: "/dashboard/calendar", icon: Calendar },
  { name: "Notes", to: "/dashboard/notes", icon: FileText },
  { name: "Files", to: "/dashboard/files", icon: Folder },
  { name: "Notifications", to: "/dashboard/notifications", icon: Bell },
  { name: "Changelog", to: "/changelog", icon: BookOpen },
  { name: "Settings", to: "/dashboard/settings", icon: Settings },
];

const adminNavigation = [
  { name: "Release Management", to: "/admin/releases", icon: GitCommit },
];

const linkClass = ({ isActive }: { isActive: boolean }) =>
  `flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
    isActive
      ? "bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300"
      : "text-neutral-700 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:bg-neutral-800"
  }`;

export function Sidebar() {
  const user = useAuthStore((state) => state.user);
  const isAdmin = user?.role === "workspace_admin";

  return (
    <aside className="flex h-screen w-64 flex-col border-r border-neutral-200 bg-white dark:border-neutral-800 dark:bg-neutral-900">
      <div className="flex h-16 items-center gap-2 border-b border-neutral-200 px-5 dark:border-neutral-800">
        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600 text-sm font-bold text-white">
          Y
        </div>
        <span className="text-lg font-bold text-neutral-900 dark:text-neutral-50">
          YFlow
        </span>
      </div>

      <nav className="flex-1 overflow-y-auto p-3">
        <ul className="space-y-1">
          {navigation.map((item) => (
            <li key={item.name}>
              <NavLink to={item.to} end={item.exact} className={linkClass}>
                <item.icon className="h-5 w-5 shrink-0" />
                {item.name}
              </NavLink>
            </li>
          ))}
        </ul>

        {isAdmin && (
          <>
            <div className="my-3 border-t border-neutral-200 dark:border-neutral-800" />
            <p className="px-3 pb-2 pt-1 text-xs font-semibold uppercase tracking-wide text-neutral-400">
              Admin
            </p>
            <ul className="space-y-1">
              {adminNavigation.map((item) => (
                <li key={item.name}>
                  <NavLink to={item.to} className={linkClass}>
                    <item.icon className="h-5 w-5 shrink-0" />
                    {item.name}
                  </NavLink>
                </li>
              ))}
            </ul>
          </>
        )}
      </nav>
    </aside>
  );
}

