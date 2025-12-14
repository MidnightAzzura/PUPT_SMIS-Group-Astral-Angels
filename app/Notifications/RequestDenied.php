<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Requests;

class RequestDenied extends Notification
{
    use Queueable;

    private $request;
    private $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(Requests $request, $reason = null)
    {
        $this->request = $request;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', \App\Channels\CustomDatabaseChannel::class]; // Use both email and database channels
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $user = $this->request->user;
        $office = $user ? $user->Office : 'Unknown Office';
        
        return (new MailMessage)
                    ->subject('Request Denied - SMIS')
                    ->greeting('Hello ' . ($user ? $user->First_Name . ' ' . $user->Last_Name : 'User') . '!')
                    ->line('Unfortunately, your supply request has been denied.')
                    ->line('**Request Details:**')
                    ->line('Stock Number: ' . $this->request->StockNumber)
                    ->line('Supply: ' . $this->request->supply_name)
                    ->line('Quantity: ' . $this->request->supply_quantity)
                    ->line('Office: ' . $office)
                    ->line('Purpose: ' . ($this->request->purpose ?: $this->request->Purpose))
                    ->when($this->reason, function ($message) {
                        return $message->line('Reason: ' . $this->reason);
                    })
                    ->line('Please contact the administrator if you need clarification.')
                    ->line('Thank you for using SMIS!');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $user = $this->request->user;
        return [
            'office' => $user ? $user->Office : null,
            'action' => 'request_denied',
            'message' => 'Your request for ' . $this->request->supply_name . ' has been denied.' . ($this->reason ? ' Reason: ' . $this->reason : ''),
            'request_id' => $this->request->id,
            'stock_number' => $this->request->StockNumber
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
