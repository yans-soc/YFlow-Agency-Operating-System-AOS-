<?php

namespace App\Jobs;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $userId,
        public string $action,
        public string $description,
        public ?string $subjectType = null,
        public ?string $subjectId = null,
        public ?array $metadata = null,
    ) {}

    public function handle(): void
    {
        Activity::create([
            'user_id' => $this->userId,
            'action' => $this->action,
            'description' => $this->description,
            'subject_type' => $this->subjectType,
            'subject_id' => $this->subjectId,
            'metadata' => $this->metadata,
        ]);
    }
}