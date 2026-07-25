<?php

$controllers = [
    'WorkspaceController' => ['Workspace', 'workspace'],
    'DepartmentController' => ['Department', 'department'],
    'PeopleController' => ['Person', 'person'],
    'ProjectController' => ['Project', 'project'],
    'WorkflowController' => ['Workflow', 'workflow'],
    'TaskController' => ['Task', 'task'],
    'NoteController' => ['Note', 'note'],
    'FileController' => ['File', 'file'],
    'CalendarEventController' => ['CalendarEvent', 'calendar_event'],
    'NotificationController' => ['Notification', 'notification'],
    'ActivityController' => ['Activity', 'activity'],
    'AiSessionController' => ['AiSession', 'ai_session'],
];

foreach ($controllers as $controller => $info) {
    $model = $info[0];
    $param = $info[1];
    $path = __DIR__ . "/app/Http/Controllers/Api/{$controller}.php";
    
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // check if constructor already exists
        if (strpos($content, 'public function __construct()') === false) {
            // Find class start
            $search = "class {$controller} extends Controller\n{";
            if (strpos($content, $search) !== false) {
                $replace = "class {$controller} extends Controller\n{\n    public function __construct()\n    {\n        \$this->authorizeResource(\App\Models\\{$model}::class, '{$param}');\n    }\n";
                $content = str_replace($search, $replace, $content);
                file_put_contents($path, $content);
                echo "Added authorizeResource to $controller\n";
            } else {
                $search2 = "class {$controller} extends Controller\n{\n";
                $replace2 = "class {$controller} extends Controller\n{\n    public function __construct()\n    {\n        \$this->authorizeResource(\App\Models\\{$model}::class, '{$param}');\n    }\n\n";
                $content = str_replace($search2, $replace2, $content);
                file_put_contents($path, $content);
                echo "Added authorizeResource to $controller (variant 2)\n";
            }
        } else {
            echo "Constructor already exists in $controller\n";
        }
    } else {
        echo "File not found: $path\n";
    }
}