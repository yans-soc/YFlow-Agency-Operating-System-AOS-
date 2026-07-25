<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public function create(array $data): Notification
    {
        return Notification::create($data);
    }

    public function markAsRead(Notification $notification): Notification
    {
        $notification->markAsRead();
        return $notification;
    }

    public function markAllAsRead(string $recipientId): int
    {
        return Notification::query()
            ->where('recipient_id', $recipientId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function getUnreadCount(string $recipientId): int
    {
        return Notification::query()
            ->where('recipient_id', $recipientId)
            ->whereNull('read_at')
            ->count();
    }
}