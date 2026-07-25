<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\AiMessage;
use App\Models\AiSession;
use App\Models\CalendarEvent;
use App\Models\File;
use App\Models\Note;
use App\Models\Notification;
use App\Models\Person;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\TaskAssignee;
use App\Models\TaskChecklist;
use App\Models\Workflow;
use App\Models\WorkflowStage;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $workspace = Workspace::firstOrCreate(
            ['slug' => 'demo-agency'],
            [
                'name' => 'Demo Creative Agency',
                'status' => 'active',
                'settings' => ['timezone' => 'UTC', 'currency' => 'USD'],
            ]
        );

        $departments = [
            ['name' => 'Design', 'description' => 'Creative design team'],
            ['name' => 'Development', 'description' => 'Engineering and development'],
            ['name' => 'Marketing', 'description' => 'Marketing and growth'],
        ];

        $departmentModels = [];
        foreach ($departments as $dept) {
            $departmentModels[] = \App\Models\Department::create([
                'workspace_id' => $workspace->id,
                'name' => $dept['name'],
                'description' => $dept['description'],
            ]);
        }

        $positions = [
            ['title' => 'Lead Designer', 'level' => 'senior'],
            ['title' => 'UI/UX Designer', 'level' => 'mid'],
            ['title' => 'Senior Developer', 'level' => 'senior'],
            ['title' => 'Full Stack Developer', 'level' => 'mid'],
            ['title' => 'Marketing Manager', 'level' => 'senior'],
        ];

        $positionModels = [];
        foreach ($positions as $pos) {
            $positionModels[] = \App\Models\Position::create([
                'workspace_id' => $workspace->id,
                'title' => $pos['title'],
                'level' => $pos['level'],
            ]);
        }

        $skills = [
            'Figma', 'Adobe XD', 'Sketch', 'Photoshop', 'Illustrator',
            'React', 'Vue', 'Laravel', 'Node.js', 'TypeScript',
            'SEO', 'Content Marketing', 'Analytics', 'Social Media',
        ];

        $skillModels = [];
        foreach ($skills as $skill) {
            $skillModels[] = \App\Models\Skill::create([
                'workspace_id' => $workspace->id,
                'name' => $skill,
                'category' => in_array($skill, ['Figma', 'Adobe XD', 'Sketch', 'Photoshop', 'Illustrator']) ? 'design' : 
                              (in_array($skill, ['React', 'Vue', 'Laravel', 'Node.js', 'TypeScript']) ? 'development' : 'marketing'),
            ]);
        }

        $people = [];
        
        $admin = \App\Models\Person::create([
            'workspace_id' => $workspace->id,
            'department_id' => $departmentModels[0]->id,
            'position_id' => $positionModels[0]->id,
            'name' => 'Alex Johnson',
            'email' => 'alex@demo.yflow',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
            'avatar' => null,
            'bio' => 'Creative director with 10+ years experience',
        ]);
        $people[] = $admin;

        $teamMembers = [
            ['Sarah Chen', 'sarah@demo.yflow', $departmentModels[0]->id, $positionModels[1]->id, 'Designer passionate about user experience'],
            ['Mike Ross', 'mike@demo.yflow', $departmentModels[1]->id, $positionModels[2]->id, 'Full stack developer specializing in Laravel'],
            ['Emily Davis', 'emily@demo.yflow', $departmentModels[1]->id, $positionModels[3]->id, 'Frontend expert with React expertise'],
            ['James Wilson', 'james@demo.yflow', $departmentModels[2]->id, $positionModels[4]->id, 'Growth marketer focused on analytics'],
        ];

        foreach ($teamMembers as $member) {
            $people[] = \App\Models\Person::create([
                'workspace_id' => $workspace->id,
                'department_id' => $member[2],
                'position_id' => $member[3],
                'name' => $member[0],
                'email' => $member[1],
                'password' => bcrypt('password'),
                'role' => 'member',
                'status' => 'active',
                'avatar' => null,
                'bio' => $member[4],
            ]);
        }

        foreach ($people as $person) {
            $person->skills()->attach(
                collect($skillModels)->random(rand(2, 4))->pluck('id')
            );
        }

        $teams = [];
        $designTeam = \App\Models\Team::create([
            'department_id' => $departmentModels[0]->id,
            'lead_id' => $admin->id,
            'name' => 'Design Team',
            'description' => 'Core design team',
        ]);
        $teams[] = $designTeam;

        $devTeam = \App\Models\Team::create([
            'department_id' => $departmentModels[1]->id,
            'lead_id' => $people[2]->id,
            'name' => 'Development Team',
            'description' => 'Engineering squad',
        ]);
        $teams[] = $devTeam;

        $projects = [
            [
                'name' => 'E-Commerce Platform Redesign',
                'description' => 'Complete redesign of the client e-commerce platform with focus on mobile experience and conversion optimization.',
                'status' => 'in_progress',
            ],
            [
                'name' => 'Mobile App Development',
                'description' => 'Native iOS and Android app for fitness tracking with social features and gamification elements.',
                'status' => 'planning',
            ],
            [
                'name' => 'Brand Identity Package',
                'description' => 'Complete brand identity including logo, style guide, and marketing materials for startup client.',
                'status' => 'completed',
            ],
            [
                'name' => 'Marketing Campaign Q4',
                'description' => 'Multi-channel marketing campaign for holiday season including social media, email, and paid ads.',
                'status' => 'in_progress',
            ],
        ];

        $projectModels = [];
        foreach ($projects as $proj) {
            $project = \App\Models\Project::create([
                'workspace_id' => $workspace->id,
                'owner_id' => $admin->id,
                'name' => $proj['name'],
                'description' => $proj['description'],
                'status' => $proj['status'],
                'start_date' => now()->subDays(rand(0, 60)),
                'due_date' => now()->addDays(rand(30, 90)),
            ]);
            $projectModels[] = $project;

            foreach ($people as $person) {
                if (rand(0, 1)) {
                    ProjectMember::create([
                        'project_id' => $project->id,
                        'person_id' => $person->id,
                        'role' => rand(0, 1) ? 'developer' : 'designer',
                    ]);
                }
            }

            $workflow = Workflow::create([
                'project_id' => $project->id,
                'name' => 'Default Workflow',
            ]);

            $stageNames = ['Backlog', 'To Do', 'In Progress', 'Review', 'Done'];
            $stages = [];
            foreach ($stageNames as $index => $name) {
                $stages[] = WorkflowStage::create([
                    'workflow_id' => $workflow->id,
                    'name' => $name,
                    'order' => $index + 1,
                ]);
            }

            $taskCount = rand(5, 15);
            for ($i = 0; $i < $taskCount; $i++) {
                $stage = $stages[array_rand($stages)];
                $creator = $people[array_rand($people)];
                
                $task = Task::create([
                    'stage_id' => $stage->id,
                    'project_id' => $project->id,
                    'title' => "Task #" . ($i + 1) . " for " . $project->name,
                    'description' => 'This is a sample task description with details about what needs to be done.',
                    'priority' => ['low', 'medium', 'high', 'urgent'][array_rand(['low', 'medium', 'high', 'urgent'])],
                    'status' => match($stage->name) {
                        'Done' => 'completed',
                        'In Progress', 'Review' => 'in_progress',
                        default => 'pending',
                    },
                    'created_by' => $creator->id,
                    'estimated_hours' => rand(2, 40),
                    'due_date' => now()->addDays(rand(1, 30)),
                ]);

                $assignees = collect($people)->random(rand(1, 3));
                foreach ($assignees as $assignee) {
                    TaskAssignee::create([
                        'task_id' => $task->id,
                        'person_id' => $assignee->id,
                        'role' => 'contributor',
                    ]);
                }

                if (rand(0, 1)) {
                    $checklistItems = ['Research', 'Design', 'Implementation', 'Testing', 'Documentation'];
                    foreach (collect($checklistItems)->random(rand(2, 4)) as $item) {
                        TaskChecklist::create([
                            'task_id' => $task->id,
                            'title' => $item,
                            'is_completed' => rand(0, 1) === 1,
                        ]);
                    }
                }
            }
        }

        $notes = [
            ['title' => 'Project Kickoff Notes', 'content' => 'Key points from the initial client meeting:\n\n- Target audience: millennials and Gen Z\n- Primary goal: increase conversion by 30%\n- Timeline: 3 months\n- Budget: flexible based on scope'],
            ['title' => 'Design System Guidelines', 'content' => 'Core principles for our design system:\n\n1. Accessibility first\n2. Consistent spacing (8px grid)\n3. Limited color palette\n4. Clear typography hierarchy'],
            ['title' => 'Sprint Planning Template', 'content' => 'Weekly sprint planning agenda:\n\n1. Review previous sprint\n2. Discuss blockers\n3. Estimate new tasks\n4. Assign responsibilities\n5. Set sprint goals'],
        ];

        foreach ($notes as $note) {
            Note::create([
                'workspace_id' => $workspace->id,
                'project_id' => $projectModels[array_rand($projectModels)]->id,
                'created_by' => $admin->id,
                'title' => $note['title'],
                'content' => $note['content'],
                'is_pinned' => rand(0, 1) === 1,
            ]);
        }

        $events = [
            ['title' => 'Client Presentation', 'type' => 'meeting', 'description' => 'Present final designs to client'],
            ['title' => 'Sprint Review', 'type' => 'meeting', 'description' => 'Bi-weekly sprint review with team'],
            ['title' => 'Design Workshop', 'type' => 'workshop', 'description' => 'Internal design skills workshop'],
            ['title' => 'Product Launch', 'type' => 'milestone', 'description' => 'Official product launch date'],
        ];

        foreach ($events as $event) {
            CalendarEvent::create([
                'workspace_id' => $workspace->id,
                'project_id' => $projectModels[array_rand($projectModels)]->id,
                'created_by' => $admin->id,
                'title' => $event['title'],
                'description' => $event['description'],
                'type' => $event['type'],
                'start_time' => now()->addDays(rand(1, 30))->setHour(10)->setMinute(0),
                'end_time' => now()->addDays(rand(1, 30))->setHour(11)->setMinute(0),
                'location' => 'Conference Room A / Virtual',
            ]);
        }

        $notifications = [
            ['type' => 'task_assigned', 'message' => 'You have been assigned to a new task'],
            ['type' => 'comment_mention', 'message' => 'Sarah mentioned you in a comment'],
            ['type' => 'deadline_reminder', 'message' => 'Task deadline approaching in 2 days'],
            ['type' => 'project_update', 'message' => 'New files uploaded to E-Commerce project'],
        ];

        foreach ($notifications as $notif) {
            Notification::create([
                'workspace_id' => $workspace->id,
                'recipient_id' => $people[array_rand($people)]->id,
                'type' => $notif['type'],
                'message' => $notif['message'],
                'is_read' => rand(0, 1) === 1,
            ]);
        }

        Activity::create([
            'workspace_id' => $workspace->id,
            'user_id' => $admin->id,
            'subject_type' => Workspace::class,
            'subject_id' => $workspace->id,
            'action' => 'created',
            'description' => 'Demo workspace initialized',
        ]);

        $aiSession = AiSession::create([
            'workspace_id' => $workspace->id,
            'project_id' => $projectModels[0]->id,
            'created_by' => $admin->id,
            'title' => 'Project Planning Assistant',
            'model' => 'gpt-4',
        ]);

        AiMessage::create([
            'session_id' => $aiSession->id,
            'role' => 'user',
            'content' => 'Help me create a project timeline for the e-commerce redesign.',
        ]);

        AiMessage::create([
            'session_id' => $aiSession->id,
            'role' => 'assistant',
            'content' => "I'll help you create a comprehensive timeline. Based on typical e-commerce projects, I recommend:\n\n**Phase 1: Discovery (Week 1-2)**\n- Stakeholder interviews\n- User research\n- Competitor analysis\n\n**Phase 2: Design (Week 3-6)**\n- Wireframes\n- Visual design\n- Prototyping\n\n**Phase 3: Development (Week 7-12)**\n- Frontend implementation\n- Backend integration\n- Testing\n\n**Phase 4: Launch (Week 13-14)**\n- Final QA\n- Deployment\n- Training",
        ]);

        $this->command->info('Demo data seeded successfully!');
        $this->command->info('Login: alex@demo.yflow / password');
    }
}