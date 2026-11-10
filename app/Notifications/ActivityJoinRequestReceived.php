<?php

namespace App\Notifications;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ActivityJoinRequestReceived extends Notification
{
    use Queueable;

    public function __construct(public Activity $activity, public User $user) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'activity_join_request',
            'activity_public_id' => $this->activity->public_id,
            'user_public_id' => $this->user->public_id,
            'message' => $this->user->name.' requested to join '.$this->activity->title,
        ];
    }
}
