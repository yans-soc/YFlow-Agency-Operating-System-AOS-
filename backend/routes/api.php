<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AiSessionController;
use App\Http\Controllers\Api\CalendarEventController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PeopleController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ReleaseController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\WorkflowController;
use App\Http\Controllers\Api\WorkspaceController;
use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check endpoint (public)
Route::get('/health', function () {
    $databaseConnected = false;
    $redisConnected = false;
    
    try {
        DB::connection()->getPdo();
        $databaseConnected = true;
    } catch (\Exception $e) {
        // Database connection failed
    }
    
    try {
        Redis::ping();
        $redisConnected = true;
    } catch (\Exception $e) {
        // Redis connection failed
    }
    
    $status = $databaseConnected && $redisConnected ? 'healthy' : 'unhealthy';
    $httpCode = $databaseConnected && $redisConnected ? 200 : 503;
    
    return response()->json([
        'status' => $status,
        'timestamp' => now()->toIso8601String(),
        'services' => [
            'database' => $databaseConnected ? 'connected' : 'disconnected',
            'redis' => $redisConnected ? 'connected' : 'disconnected',
        ],
        'version' => config('app.version', '1.0.0'),
        'environment' => config('app.env'),
    ], $httpCode);
});

Route::prefix('v1')->group(function () {
    // Auth routes
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('auth/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::get('dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('dashboard/today-tasks', [DashboardController::class, 'todayTasks']);
        Route::get('dashboard/active-projects', [DashboardController::class, 'activeProjects']);
        Route::get('dashboard/recent-activities', [DashboardController::class, 'recentActivities']);
        Route::get('dashboard/notifications', [DashboardController::class, 'notifications']);
        Route::put('dashboard/notifications/{id}/read', [DashboardController::class, 'markNotificationRead']);
        Route::put('dashboard/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead']);

        // Workspaces
        Route::get('workspaces/current', [WorkspaceController::class, 'current']);
        Route::apiResource('workspaces', WorkspaceController::class);
        Route::get('workspaces/{workspace}/members', [WorkspaceController::class, 'members']);
        Route::post('workspaces/{workspace}/members', [WorkspaceController::class, 'inviteMember']);
        Route::put('workspaces/{workspace}/members/{memberId}', [WorkspaceController::class, 'updateMember']);
        Route::delete('workspaces/{workspace}/members/{memberId}', [WorkspaceController::class, 'removeMember']);
        Route::get('workspaces/{workspace}/stats', [WorkspaceController::class, 'stats']);

        // Departments
        Route::apiResource('departments', DepartmentController::class);

        // People
        Route::apiResource('people', PeopleController::class);

        // Projects
        Route::apiResource('projects', ProjectController::class);

        // Workflows
        Route::apiResource('workflows', WorkflowController::class);
        Route::post('workflows/{id}/stages', [WorkflowController::class, 'addStage']);
        Route::patch('workflows/{id}/stages/{stageId}/order', [WorkflowController::class, 'updateStageOrder']);

        // Tasks
        Route::apiResource('tasks', TaskController::class);
        Route::post('tasks/{task}/move-stage', [TaskController::class, 'moveStage']);
        Route::post('tasks/{task}/toggle-complete', [TaskController::class, 'toggleComplete']);

        // Notes
        Route::apiResource('notes', NoteController::class);

        // Files
        Route::apiResource('files', FileController::class);

        // Calendar Events
        Route::apiResource('calendar-events', CalendarEventController::class);

        // Notifications
        Route::apiResource('notifications', NotificationController::class);
        Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        // Activities
        Route::apiResource('activities', ActivityController::class);

        // AI Sessions
        Route::apiResource('ai-sessions', AiSessionController::class);
        Route::post('ai-sessions/{id}/messages', [AiSessionController::class, 'sendMessage']);

        // Releases (Version Management)
        Route::prefix('releases')->group(function () {
            Route::get('/', [ReleaseController::class, 'index']);
            Route::post('/', [ReleaseController::class, 'store']);
            Route::get('/{release}', [ReleaseController::class, 'show']);
            Route::put('/{release}', [ReleaseController::class, 'update']);
            Route::delete('/{release}', [ReleaseController::class, 'destroy']);
            Route::post('/{release}/set-current', [ReleaseController::class, 'setCurrent']);
        });
    });

    // Public version endpoint
    Route::get('/version/current', [ReleaseController::class, 'current']);
});
