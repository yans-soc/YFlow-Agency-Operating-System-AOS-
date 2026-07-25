<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $fillable = [
        'workspace_id',
        'recipient_id',
        'sender_id',
        'type',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'workspace_id' => 'string',
        'recipient_id' => 'string',
        'sender_id' => 'string',
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'recipient_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'sender_id');
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }
}