<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Person;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Notification $notification,
        public Person $recipient,
    ) {}

    public function handle(): void
    {
        if (!$this->recipient->email) {
            return;
        }

        // Mail::to($this->recipient->email)->send(new NotificationMail($this->notification));
        
        \Log::info('Notification email queued', [
            'recipient' => $this->recipient->email,
            'notification_id' => $this->notification->id,
            'type' => $this->notification->type,
        ]);
    }
}