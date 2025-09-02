<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;


use NotificationChannels\WebPush\WebPushMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    public function via($notifiable)
    {
        return ['webpush'];
    }

    public function toWebPush($notifiable, $notification)
    {
        return (new WebPushMessage)
            ->title('📢 New Task Alert!')
            ->icon('/icon.png')
            ->body('A new task has been assigned to you.')
            ->action('View Task', route('operator.index'))
            ->data(['url' => '/tasks']);
    }
}
