<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserCredentials extends Mailable
{
    use Queueable, SerializesModels;

    public $fullName;
    public $emailAddress;
    public $temporaryPassword;
    public $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $fullName, string $emailAddress, string $temporaryPassword, string $resetUrl)
    {
        $this->fullName = $fullName;
        $this->emailAddress = $emailAddress;
        $this->temporaryPassword = $temporaryPassword;
        $this->resetUrl = $resetUrl;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        // Use the Markdown-style template and supply the variable names the template expects.
        return $this
            ->subject('Welcome — Your account credentials')
            ->markdown('emails.new-user-credentials-md') // <- points to new-user-credentials-md.blade.php
            ->with([
                'userName'     => $this->fullName,
                'userEmail'    => $this->emailAddress,
                'userPassword' => $this->temporaryPassword,
                'resetUrl'     => $this->resetUrl,
            ]);
    }
}