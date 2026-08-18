<?php

namespace App\Notifications;

use App\Models\IndigeneApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ApplicationDecisionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private IndigeneApplication $application,
        private string $decisionType,
        private string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $icon = match ($this->decisionType) {
            'approved' => 'task_alt',
            'rejected' => 'cancel',
            default => 'edit_note',
        };

        $iconClass = match ($this->decisionType) {
            'approved' => 'text-brand-green',
            'rejected' => 'text-danger',
            default => 'text-warning',
        };

        return [
            'message' => $this->message,
            'icon' => $icon,
            'icon_class' => $iconClass,
            'link' => route('applications.show', $this->application),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
