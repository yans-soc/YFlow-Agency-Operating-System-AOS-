import {
  useDashboardStats,
  useTodayTasks,
  useActiveProjects,
  useRecentActivities,
} from "@/hooks/useDashboard";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { useAuthStore } from "@/stores/authStore";
import { format } from "date-fns";
import {
  FolderKanban,
  CheckCircle2,
  Clock,
  AlertCircle,
  Plus,
  ArrowRight,
} from "lucide-react";
import { Link } from "react-router-dom";

function StatCard({
  title,
  value,
  icon: Icon,
  colorClass,
}: {
  title: string;
  value: number | string;
  icon: React.ElementType;
  colorClass: string;
}) {
  return (
    <Card>
      <CardContent className="flex items-center gap-4 p-6">
        <div
          className={`flex h-12 w-12 items-center justify-center rounded-lg ${colorClass}`}
        >
          <Icon className="h-6 w-6" />
        </div>
        <div>
          <p className="text-sm text-neutral-500">{title}</p>
          <p className="text-2xl font-semibold">{value}</p>
        </div>
      </CardContent>
    </Card>
  );
}

function TodayTaskItem({
  task,
}: {
  task: {
    id: string;
    title: string;
    project_name?: string;
    priority: string;
    due_date?: string;
  };
}) {
  const priorityColors: Record<string, string> = {
    high: "bg-danger-100 text-danger-700",
    medium: "bg-warning-100 text-warning-700",
    low: "bg-success-100 text-success-700",
  };

  return (
    <div className="flex items-center gap-3 border-b border-neutral-100 py-3 last:border-0">
      <input
        type="checkbox"
        className="h-4 w-4 rounded border-neutral-300 text-primary-600"
      />
      <div className="flex-1">
        <p className="text-sm font-medium text-neutral-900">{task.title}</p>
        <p className="text-xs text-neutral-500">{task.project_name}</p>
      </div>
      <span
        className={`rounded-full px-2 py-1 text-xs font-medium ${priorityColors[task.priority] || "bg-neutral-100 text-neutral-700"}`}
      >
        {task.priority}
      </span>
    </div>
  );
}

function ProjectItem({
  project,
}: {
  project: {
    id: string;
    name: string;
    status: string;
    color?: string;
    member_count?: number;
  };
}) {
  const statusColors: Record<string, string> = {
    active: "bg-success-100 text-success-700",
    completed: "bg-neutral-100 text-neutral-700",
    on_hold: "bg-warning-100 text-warning-700",
  };

  return (
    <Link
      to={`/dashboard/projects/${project.id}`}
      className="flex items-center gap-3 border-b border-neutral-100 py-3 last:border-0 hover:bg-neutral-50"
    >
      <div
        className="h-3 w-3 rounded-full"
        style={{ backgroundColor: project.color || "#6366f1" }}
      />
      <div className="flex-1">
        <p className="text-sm font-medium text-neutral-900">{project.name}</p>
        <p className="text-xs text-neutral-500">
          {project.member_count || 0} members
        </p>
      </div>
      <span
        className={`rounded-full px-2 py-1 text-xs font-medium ${statusColors[project.status] || "bg-neutral-100"}`}
      >
        {project.status.replace("_", " ")}
      </span>
    </Link>
  );
}

function ActivityItem({
  activity,
}: {
  activity: {
    id: string;
    type: string;
    description: string;
    user_name: string;
    created_at: string;
  };
}) {
  const formatActivityTime = (dateString: string) => {
    try {
      return format(new Date(dateString), "MMM d, h:mm a");
    } catch {
      return dateString;
    }
  };

  return (
    <div className="border-b border-neutral-100 py-3 last:border-0">
      <p className="text-sm text-neutral-900">{activity.description}</p>
      <p className="text-xs text-neutral-500">
        {activity.user_name} • {formatActivityTime(activity.created_at)}
      </p>
    </div>
  );
}

export function DashboardPage() {
  const { user } = useAuthStore();
  const { data: stats, isLoading: statsLoading } = useDashboardStats();
  void statsLoading; // Used for future loading state
  const { data: todayTasks, isLoading: tasksLoading } = useTodayTasks();
  const { data: activeProjects, isLoading: projectsLoading } =
    useActiveProjects();
  const { data: recentActivities, isLoading: activitiesLoading } =
    useRecentActivities();

  const getGreeting = () => {
    const hour = new Date().getHours();
    if (hour < 12) return "Good morning";
    if (hour < 18) return "Good afternoon";
    return "Good evening";
  };

  return (
    <div className="flex-1 overflow-auto bg-neutral-50 p-6">
      {/* Header */}
      <div className="mb-6">
        <h1 className="text-h3 font-bold text-neutral-900">
          {getGreeting()}, {user?.name?.split(" ")[0] || "there"}!
        </h1>
        <p className="text-neutral-600">Here's what's happening today.</p>
      </div>

      {/* Stats Grid */}
      <div className="mb-6 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <StatCard
          title="Total Projects"
          value={stats?.total_projects || 0}
          icon={FolderKanban}
          colorClass="bg-primary-100 text-primary-600"
        />
        <StatCard
          title="Active Projects"
          value={stats?.active_projects || 0}
          icon={Clock}
          colorClass="bg-warning-100 text-warning-600"
        />
        <StatCard
          title="Completed Tasks"
          value={stats?.completed_tasks || 0}
          icon={CheckCircle2}
          colorClass="bg-success-100 text-success-600"
        />
        <StatCard
          title="Overdue Tasks"
          value={stats?.overdue_tasks || 0}
          icon={AlertCircle}
          colorClass="bg-danger-100 text-danger-600"
        />
      </div>

      {/* Main Content Grid */}
      <div className="grid gap-6 lg:grid-cols-3">
        {/* Today's Tasks */}
        <Card className="lg:col-span-2">
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-base font-semibold">
              Today's Tasks
            </CardTitle>
            <Link
              to="/dashboard/tasks"
              className="flex items-center gap-1 text-sm text-primary-600 hover:text-primary-700"
            >
              View all <ArrowRight className="h-4 w-4" />
            </Link>
          </CardHeader>
          <CardContent>
            {tasksLoading ? (
              <div className="space-y-3">
                {[1, 2, 3].map((i) => (
                  <div
                    key={i}
                    className="h-12 animate-pulse rounded bg-neutral-100"
                  />
                ))}
              </div>
            ) : todayTasks && todayTasks.length > 0 ? (
              <div>
                {todayTasks.slice(0, 5).map((task) => (
                  <TodayTaskItem key={task.id} task={task} />
                ))}
                {todayTasks.length > 5 && (
                  <Link
                    to="/dashboard/tasks"
                    className="mt-3 flex items-center justify-center gap-1 text-sm text-primary-600 hover:text-primary-700"
                  >
                    +{todayTasks.length - 5} more tasks
                  </Link>
                )}
              </div>
            ) : (
              <div className="py-8 text-center">
                <CheckCircle2 className="mx-auto mb-2 h-8 w-8 text-neutral-300" />
                <p className="text-sm text-neutral-500">No tasks for today</p>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Quick Actions */}
        <Card>
          <CardHeader>
            <CardTitle className="text-base font-semibold">
              Quick Actions
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-2">
            <Link
              to="/dashboard/projects/new"
              className="flex w-full items-center gap-3 rounded-lg border border-neutral-200 p-3 text-left hover:bg-neutral-50"
            >
              <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 text-primary-600">
                <Plus className="h-5 w-5" />
              </div>
              <div>
                <p className="text-sm font-medium">New Project</p>
                <p className="text-xs text-neutral-500">Create a new project</p>
              </div>
            </Link>
            <Link
              to="/dashboard/tasks/new"
              className="flex w-full items-center gap-3 rounded-lg border border-neutral-200 p-3 text-left hover:bg-neutral-50"
            >
              <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-success-100 text-success-600">
                <CheckCircle2 className="h-5 w-5" />
              </div>
              <div>
                <p className="text-sm font-medium">New Task</p>
                <p className="text-xs text-neutral-500">Add a new task</p>
              </div>
            </Link>
          </CardContent>
        </Card>

        {/* Active Projects */}
        <Card>
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-base font-semibold">
              Active Projects
            </CardTitle>
            <Link
              to="/dashboard/projects"
              className="flex items-center gap-1 text-sm text-primary-600 hover:text-primary-700"
            >
              View all <ArrowRight className="h-4 w-4" />
            </Link>
          </CardHeader>
          <CardContent>
            {projectsLoading ? (
              <div className="space-y-3">
                {[1, 2, 3].map((i) => (
                  <div
                    key={i}
                    className="h-12 animate-pulse rounded bg-neutral-100"
                  />
                ))}
              </div>
            ) : activeProjects && activeProjects.length > 0 ? (
              <div>
                {activeProjects.slice(0, 5).map((project) => (
                  <ProjectItem key={project.id} project={project} />
                ))}
              </div>
            ) : (
              <div className="py-8 text-center">
                <FolderKanban className="mx-auto mb-2 h-8 w-8 text-neutral-300" />
                <p className="text-sm text-neutral-500">No active projects</p>
              </div>
            )}
          </CardContent>
        </Card>

        {/* Recent Activity */}
        <Card className="lg:col-span-2">
          <CardHeader className="flex flex-row items-center justify-between pb-2">
            <CardTitle className="text-base font-semibold">
              Recent Activity
            </CardTitle>
          </CardHeader>
          <CardContent>
            {activitiesLoading ? (
              <div className="space-y-3">
                {[1, 2, 3, 4].map((i) => (
                  <div
                    key={i}
                    className="h-12 animate-pulse rounded bg-neutral-100"
                  />
                ))}
              </div>
            ) : recentActivities && recentActivities.length > 0 ? (
              <div>
                {recentActivities.slice(0, 5).map((activity) => (
                  <ActivityItem key={activity.id} activity={activity} />
                ))}
              </div>
            ) : (
              <div className="py-8 text-center">
                <Clock className="mx-auto mb-2 h-8 w-8 text-neutral-300" />
                <p className="text-sm text-neutral-500">No recent activity</p>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
