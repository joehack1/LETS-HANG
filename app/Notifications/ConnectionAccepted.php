<?php

namespace App\Notifications;

use App\Models\ConnectionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConnectionAccepted extends Notification
{
    use Queueable;

    public function __construct(public ConnectionRequest $connectionRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'connection_accepted',
            'request_id' => $this->connectionRequest->id,
            'receiver_id' => $this->connectionRequest->receiver_id,
            'intention' => $this->connectionRequest->intention,
            'message' => 'Your connection request was accepted.',
        ];
    }
}
