<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Requests;

class BatchRequestApproved extends Notification
{
    use Queueable;

    private $batchRequest;
    private $batchRequests;
    private $batchId;

    /**
     * Create a new notification instance.
     */
    public function __construct($batchRequest, $batchRequests = null, $batchId = null)
    {
        $this->batchRequest = $batchRequest;
        $this->batchRequests = $batchRequests ?? [];
        $this->batchId = $batchId;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // Only email for batch notifications
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $user = $this->batchRequest->user;
        $office = $user ? $user->Office : 'Unknown Office';
        $batchCount = $this->batchRequest->batch_count ?? count($this->batchRequests);
        
        // Get all request details for the email
        $requestDetails = [];
        if (!empty($this->batchRequests)) {
            foreach ($this->batchRequests as $request) {
                $requestDetails[] = [
                    'stock_number' => $request->StockNumber,
                    'supply_name' => $request->supply_name,
                    'quantity' => $request->supply_quantity,
                    'issue_quantity' => $request->issue_quantity ?? '1',
                    'remarks' => $request->remarks ?? ''
                ];
            }
        } else {
            // Fallback to single request if batch requests not provided
            $requestDetails[] = [
                'stock_number' => $this->batchRequest->StockNumber,
                'supply_name' => $this->batchRequest->supply_name,
                'quantity' => $this->batchRequest->supply_quantity,
                'issue_quantity' => $this->batchRequest->issue_quantity ?? '1',
                'remarks' => $this->batchRequest->remarks ?? ''
            ];
        }

        $mail = (new MailMessage)
                    ->subject('Batch Request Approved - SMIS')
                    ->greeting('Hello ' . ($user ? $user->First_Name . ' ' . $user->Last_Name : 'User') . '!')
                    ->line('Your batch supply request has been approved.');
        
        // Add batch summary
        if ($batchCount > 1) {
            $mail->line("**Batch Summary:** {$batchCount} items approved");
        }
        
        $mail->line('**Request Details:**');
        
        // Add each request details
        foreach ($requestDetails as $index => $detail) {
            $itemNumber = $index + 1;
            $mail->line("**Item {$itemNumber}:**")
                 ->line('• Stock Number: ' . $detail['stock_number'])
                 ->line('• Supply: ' . $detail['supply_name'])
                 ->line('• Requested Quantity: ' . $detail['quantity'])
                 ->line('• Issue Quantity: ' . $detail['issue_quantity']);
            
            if (!empty($detail['remarks'])) {
                $mail->line('• Remarks: ' . $detail['remarks']);
            }
            
            // Add spacing between items (except for last item)
            if ($itemNumber < count($requestDetails)) {
                $mail->line('');
            }
        }
        
        $mail->line('')
             ->line('**Office:** ' . $office)
             ->line('**Purpose:** ' . ($this->batchRequest->purpose ?: $this->batchRequest->Purpose))
             ->action('View Request', route('requests.approved'))
             ->line('Please coordinate with the Property Custodian for item pickup.')
             ->line('Thank you for using SMIS!')
             ->line('')
             ->line('**Regards,**')
             ->line('**PUPT-SMIS**');

        return $mail;
    }
}
