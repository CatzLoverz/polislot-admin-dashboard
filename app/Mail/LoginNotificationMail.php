<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $loginTime;
    public $ipAddress;
    public $userAgent;
    public $token;

    public function __construct($user, $loginTime, $ipAddress, $userAgent, $token)
    {
        $this->user = $user;
        $this->loginTime = $loginTime;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->token = $token;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Login Akun Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'Emails.login_notification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
