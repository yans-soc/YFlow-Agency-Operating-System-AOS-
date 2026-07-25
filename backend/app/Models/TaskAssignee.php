<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAssignee extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = [
        'task_id',
        'person_id',
        'assigned_at',
        'completed_at',
    ];

    protected $casts = [
        'task_id' => 'string',
        'person_id' => 'string',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}