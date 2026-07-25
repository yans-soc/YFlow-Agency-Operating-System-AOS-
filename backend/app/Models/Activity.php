<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $fillable = [
        'workspace_id',
        'actor_id',
        'action',
        'subject_type',
        'subject_id',
        'changes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'workspace_id' => 'string',
        'actor_id' => 'string',
        'subject_id' => 'string',
        'changes' => 'array',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'actor_id');
    }
}