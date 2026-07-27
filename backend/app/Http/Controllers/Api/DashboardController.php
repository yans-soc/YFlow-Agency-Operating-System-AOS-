<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use App\Models\Activity;
use App\Models\Notification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get dashboard data.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $this->getStats($user),
                'today_tasks' => $this->getTodayTasks($user),
                'upcoming_tasks' => $this->getUpcomingTasks($user),
                'active_projects' => $this->getActiveProjects($user),
                'recent_activities' => $this->getRecentActivities($user),
                'notifications' => $this->getNotifications($user),
            ],
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    public function stats(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getStats($request->user()),
        ]);
    }

    /**
     * Get today's tasks.
     */
    public function todayTasks(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getTodayTasks($request->user()),
        ]);
    }

    /**
     * Get active projects.
     */
    public function activeProjects(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getActiveProjects($request->user()),
        ]);
    }

    /**
     * Get recent activities.
     */
    public function recentActivities(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getRecentActivities($request->user()),
        ]);
    }

    /**
     * Get notifications.
     */
    public function notifications(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->getNotifications($request->user()),
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead(Request $request, string $id)
    {
        $notification = Notification::where('id', $id)
            ->where('recipient_id', $request->user()->id)
            ->first();

        if (! $notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found',
            ], 404);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllNotificationsRead(Request $request)
    {
        Notification::where('recipient_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Base query for tasks belonging to the user's workspace.
     */
    private function workspaceTasks(string $workspaceId)
    {
        return Task::whereHas('stage.workflow.project', function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        });
    }

    /**
     * Get dashboard statistics.
     */
    private function getStats($user)
    {
        $workspaceId = $user->workspace_id ?? null;

        if (! $workspaceId) {
            return [
                'total_projects' => 0,
                'active_projects' => 0,
                'total_tasks' => 0,
                'completed_tasks' => 0,
                'pending_tasks' => 0,
                'overdue_tasks' => 0,
            ];
        }

        $totalProjects = Project::where('workspace_id', $workspaceId)->count();
        $activeProjects = Project::where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->count();

        $totalTasks = $this->workspaceTasks($workspaceId)->count();

        $completedTasks = $this->workspaceTasks($workspaceId)
            ->whereNotNull('completed_at')
            ->count();

        $pendingTasks = $this->workspaceTasks($workspaceId)
            ->whereIn('status', ['todo', 'in_progress', 'review'])
            ->count();

        $overdueTasks = $this->workspaceTasks($workspaceId)
            ->whereDate('due_date', '<', now())
            ->whereNull('completed_at')
            ->count();

        return [
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'overdue_tasks' => $overdueTasks,
        ];
    }

    /**
     * Get today's tasks (due today or overdue, not completed).
     */
    private function getTodayTasks($user)
    {
        $workspaceId = $user->workspace_id ?? null;

        if (! $workspaceId) {
            return [];
        }

        return $this->workspaceTasks($workspaceId)
            ->whereNull('completed_at')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', today())
            ->with('stage.workflow.project:id,name')
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(fn ($task) => $this->transformTask($task));
    }

    /**
     * Get upcoming tasks (due after today, not completed).
     */
    private function getUpcomingTasks($user)
    {
        $workspaceId = $user->workspace_id ?? null;

        if (! $workspaceId) {
            return [];
        }

        return $this->workspaceTasks($workspaceId)
            ->whereNull('completed_at')
            ->whereDate('due_date', '>', today())
            ->with('stage.workflow.project:id,name')
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(fn ($task) => $this->transformTask($task));
    }

    /**
     * Shape a task for the dashboard payload.
     */
    private function transformTask($task): array
    {
        $project = $task->stage?->workflow?->project;

        return [
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $task->status,
            'priority' => $task->priority,
            'due_date' => $task->due_date?->toISOString(),
            'completed_at' => $task->completed_at?->toISOString(),
            'project_id' => $project?->id,
            'project_name' => $project?->name,
        ];
    }

    /**
     * Get active projects.
     */
    private function getActiveProjects($user)
    {
        $workspaceId = $user->workspace_id ?? null;

        if (! $workspaceId) {
            return [];
        }

        return Project::where('workspace_id', $workspaceId)
            ->where('status', 'active')
            ->withCount('members')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'status' => $project->status,
                    'start_date' => $project->start_date?->toISOString(),
                    'end_date' => $project->end_date?->toISOString(),
                    'member_count' => $project->members_count,
                ];
            });
    }

    /**
     * Get recent activities.
     */
    private function getRecentActivities($user)
    {
        $workspaceId = $user->workspace_id ?? null;

        if (! $workspaceId) {
            return [];
        }

        return Activity::where('workspace_id', $workspaceId)
            ->with('person:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'description' => $activity->description,
                    'user_id' => $activity->person_id,
                    'user_name' => $activity->person?->name ?? 'Unknown',
                    'created_at' => $activity->created_at->toISOString(),
                ];
            });
    }

    /**
     * Get notifications for the authenticated person.
     */
    private function getNotifications($user)
    {
        return Notification::where('recipient_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'read' => $notification->read_at !== null,
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });
    }
}
