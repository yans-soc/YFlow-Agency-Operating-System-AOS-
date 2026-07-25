<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use App\Models\Activity;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Get dashboard data.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $stats = $this->getStats($user);
        $todayTasks = $this->getTodayTasks($user);
        $upcomingTasks = $this->getUpcomingTasks($user);
        $activeProjects = $this->getActiveProjects($user);
        $recentActivities = $this->getRecentActivities($user);
        $notifications = $this->getNotifications($user);

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'today_tasks' => $todayTasks,
                'upcoming_tasks' => $upcomingTasks,
                'active_projects' => $activeProjects,
                'recent_activities' => $recentActivities,
                'notifications' => $notifications,
            ]
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    public function stats(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => $this->getStats($user)
        ]);
    }

    /**
     * Get today's tasks.
     */
    public function todayTasks(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => $this->getTodayTasks($user)
        ]);
    }

    /**
     * Get active projects.
     */
    public function activeProjects(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => $this->getActiveProjects($user)
        ]);
    }

    /**
     * Get recent activities.
     */
    public function recentActivities(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => $this->getRecentActivities($user)
        ]);
    }

    /**
     * Get notifications.
     */
    public function notifications(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'success' => true,
            'data' => $this->getNotifications($user)
        ]);
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationRead(Request $request, string $id)
    {
        $notification = Notification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllNotificationsRead(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('read', false)
            ->update(['read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    /**
     * Get dashboard statistics.
     */
    private function getStats($user)
    {
        $workspaceId = $user->person->workspace_id ?? null;
        
        if (!$workspaceId) {
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
        
        $totalTasks = Task::whereHas('project', function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->count();
        
        $completedTasks = Task::whereHas('project', function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->where('status', 'completed')->count();
        
        $pendingTasks = Task::whereHas('project', function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->whereIn('status', ['todo', 'in_progress'])->count();
        
        $overdueTasks = Task::whereHas('project', function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })->where('due_date', '<', now())
            ->whereNotIn('status', ['completed'])
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
     * Get today's tasks.
     */
    private function getTodayTasks($user)
    {
        $workspaceId = $user->person->workspace_id ?? null;
        
        if (!$workspaceId) {
            return [];
        }

        return Task::whereHas('project', function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })
        ->where(function ($query) {
            $query->whereDate('due_date', today())
                ->orWhereDate('due_date', '<', today())
                ->whereNotIn('status', ['completed']);
        })
        ->with('project:id,name')
        ->orderBy('due_date')
        ->limit(10)
        ->get()
        ->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date?->toISOString(),
                'project_id' => $task->project_id,
                'project_name' => $task->project?->name,
            ];
        });
    }

    /**
     * Get upcoming tasks.
     */
    private function getUpcomingTasks($user)
    {
        $workspaceId = $user->person->workspace_id ?? null;
        
        if (!$workspaceId) {
            return [];
        }

        return Task::whereHas('project', function ($query) use ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        })
        ->whereDate('due_date', '>', today())
        ->whereNotIn('status', ['completed'])
        ->with('project:id,name')
        ->orderBy('due_date')
        ->limit(10)
        ->get()
        ->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'due_date' => $task->due_date?->toISOString(),
                'project_id' => $task->project_id,
                'project_name' => $task->project?->name,
            ];
        });
    }

    /**
     * Get active projects.
     */
    private function getActiveProjects($user)
    {
        $workspaceId = $user->person->workspace_id ?? null;
        
        if (!$workspaceId) {
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
                    'color' => $project->color,
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
        $workspaceId = $user->person->workspace_id ?? null;
        
        if (!$workspaceId) {
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
     * Get notifications.
     */
    private function getNotifications($user)
    {
        return Notification::where('user_id', $user->person->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'read' => (bool) $notification->read,
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });
    }
}
