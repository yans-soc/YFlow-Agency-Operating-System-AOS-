import { NavLink } from "react-router-dom";
import {
  LayoutDashboard,
  LayoutGrid,
  CheckSquare,
  Users,
  Calendar,
  FileText,
  Folder,
  MessageSquare,
  Settings,
  GitCommit,
  BookOpen,
} from "lucide-react";

const navigation = [
  { name: "Dashboard", to: "/dashboard", icon: LayoutDashboard, exact: true },
  { name: "Projects", to: "/dashboard/projects", icon: LayoutGrid },
  { name: "Tasks", to: "/dashboard/tasks", icon: CheckSquare },
  { name: "People", to: "/dashboard/people", icon: Users },
  { name: "Calendar", to: "/dashboard/calendar", icon: Calendar },
  { name: "Notes", to: "/dashboard/notes", icon: FileText },
  { name: "Files", to: "/dashboard/files", icon: Folder },
  { name: "AI Assistant", to: "/dashboard/ai", icon: MessageSquare },
  { name: "Changelog", to: "/changelog", icon: BookOpen },
  { name: "Settings", to: "/dashboard/settings", icon: Settings },
];

const adminNavigation = [
  { name: "Release Management", to: "/admin/releases", icon: GitCommit },
];

export function Sidebar() {
  const isAdmin = true; // TODO: Replace with actual admin check from auth store

  return (
    <aside className="flex h-screen w-64 flex-col border-r border-neutral-200 bg-white">
      <div className="flex h-16 items-center border-b border-neutral-200 px-4">
        <span className="text-h4 font-bold text-neutral-900">YFlow</span>
      </div>

      <nav className="flex-1 overflow-y-auto p-2">
        <ul className="space-y-1">
          {navigation.map((item) => (
            <li key={item.name}>
              <NavLink
                to={item.to}
                end={item.exact}
                className={({ isActive }) =>
                  `flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
                    isActive
                      ? "bg-primary-50 text-primary-700"
                      : "text-neutral-700 hover:bg-neutral-50"
                  }`
                }
              >
                <item.icon className="h-5 w-5" />
                {item.name}
              </NavLink>
            </li>
          ))}
        </ul>

        {isAdmin && (
          <>
            <div className="my-2 border-t border-neutral-200" />
            <p className="px-3 pb-2 pt-3 text-xs font-semibold uppercase text-neutral-500">
              Admin
            </p>
            <ul className="space-y-1">
              {adminNavigation.map((item) => (
                <li key={item.name}>
                  <NavLink
                    to={item.to}
                    className={({ isActive }) =>
                      `flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors ${
                        isActive
                          ? "bg-primary-50 text-primary-700"
                          : "text-neutral-700 hover:bg-neutral-50"
                      }`
                    }
                  >
                    <item.icon className="h-5 w-5" />
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
