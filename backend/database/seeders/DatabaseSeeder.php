<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\AiSession;
use App\Models\CalendarEvent;
use App\Models\Department;
use App\Models\File;
use App\Models\Note;
use App\Models\Notification;
use App\Models\Person;
use App\Models\Position;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Task;
use App\Models\Team;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoSeeder::class,
        ]);
    }
}