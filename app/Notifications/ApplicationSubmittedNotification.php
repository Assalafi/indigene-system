<?php

namespace App\Notifications;

use App\Models\IndigeneApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApplicationSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private IndigeneApplication $application,
        private string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'icon' => 'description',
            'icon_class' => 'text-warning',
            'link' => route('applications.show', $this->application),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
