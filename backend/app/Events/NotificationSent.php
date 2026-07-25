<?php

namespace App\Events;

use App\Models\Notification;
use App\Models\Person;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationSent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Notification $notification,
        public Person $recipient,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('person.' . $this->recipient->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'notification.received';
    }

    public function broadcastWith(): array
    {
        return [
            'notification_id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
        ];
    }
}