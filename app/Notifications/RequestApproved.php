<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Requests;

class RequestApproved extends Notification
{
    use Queueable;

    private $request;
    private $requestDetails;

    /**
     * Create a new notification instance.
     */
    public function __construct(Requests $request, $requestDetails = [])
    {
        $this->request = $request;
        $this->requestDetails = $requestDetails;
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
        
        $mail = (new MailMessage)
                    ->subject('Request Approved - SMIS')
                    ->greeting('Hello ' . ($user ? $user->First_Name . ' ' . $user->Last_Name : 'User') . '!')
                    ->line('Your supply request has been approved.')
                    ->line('**Request Details:**')
                    ->line('Stock Number: ' . $this->request->StockNumber)
                    ->line('Supply: ' . $this->request->supply_name)
                    ->line('Requested Quantity: ' . $this->request->supply_quantity)
                    ->line('Issue Quantity: ' . ($this->request->issue_quantity ?? '0')) // Show issue quantity (even if 0)
                    ->line('Office: ' . $office)
                    ->line('Purpose: ' . ($this->request->purpose ?: $this->request->Purpose));
        
        // Add remarks if they exist
        if (isset($this->request->remarks) && !empty($this->request->remarks)) {
            $mail->line('**Remarks:** ' . $this->request->remarks);
        }
        
        return $mail->action('View Request', route('requests.approved'))
                    ->line('Please coordinate with the Property Custodian for item pickup.')
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
            'action' => 'request_approved',
            'message' => 'Your request for ' . $this->request->supply_name . ' has been approved.',
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
