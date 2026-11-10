<?php

namespace App\Notifications;

use App\Models\ConnectionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ConnectionRequestReceived extends Notification
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
            'type' => 'connection_request',
            'request_id' => $this->connectionRequest->id,
            'sender_id' => $this->connectionRequest->sender_id,
            'intention' => $this->connectionRequest->intention,
            'message' => 'You have a new connection request.',
        ];
    }
}
