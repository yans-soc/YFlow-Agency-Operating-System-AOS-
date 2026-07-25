<?php

namespace Database\Seeders;

use App\Models\Release;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReleaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@yflow.local')->first() ?? User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@yflow.local',
            'password' => bcrypt('password'),
        ]);

        Release::create([
            'version' => '1.0.0',
            'release_notes' => <<<MD
# YFlow v1.0.0 - Initial Release

## Features
- Workspace & Project management
- Task boards with Kanban workflow
- Team collaboration
- AI-assisted planning
MD,
            'released_at' => now()->subMonths(2),
            'is_current' => false,
            'created_by' => $admin->id,
        ]);

        Release::create([
            'version' => '1.0.1',
            'release_notes' => <<<MD
# YFlow v1.0.1 - Stability & Bug Fixes

## Fixed
- Task drag-and-drop on mobile
- Notification email delivery
- Calendar event timezone handling

## Improved
- Dashboard load time (-40%)
MD,
            'released_at' => now()->subWeek(),
            'is_current' => true,
            'created_by' => $admin->id,
        ]);
    }
}