<?php

namespace App\Channels;

use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;
use App\Models\Notification as NotificationModel;

class CustomDatabaseChannel extends DatabaseChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \App\Models\Notification|null
     */
    public function send($notifiable, Notification $notification)
    {
        // Get data from the notification's toDatabase method
        $data = [];
        if (method_exists($notification, 'toDatabase')) {
            $data = $notification->toDatabase($notifiable);
        } elseif (method_exists($notification, 'toArray')) {
            $data = $notification->toArray($notifiable);
        }
        
        // Only create notification if we have proper data
        if (empty($data) || empty($data['message'])) {
            \Log::warning('Skipping notification creation due to empty data', [
                'notification_class' => get_class($notification),
                'notifiable_id' => $notifiable->getKey(),
                'data' => $data
            ]);
            return null;
        }
        
        return NotificationModel::create([
            'user_id' => $notifiable->getKey(),
            'office' => $data['office'] ?? null,
            'action' => $data['action'] ?? 'notification',
            'message' => $data['message'] ?? '',
            'request_id' => $data['request_id'] ?? null,
            'is_read' => false,
            'created_at' => now()
        ]);
    }

    /**
     * Build an array payload for the DatabaseNotification Model.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array
     */
    protected function buildPayload($notifiable, Notification $notification)
    {
        $data = [];
        
        return [
            'id' => $notification->id,
            'type' => get_class($notification),
            'data' => $data,
            'read_at' => null,
        ];
    }
}
