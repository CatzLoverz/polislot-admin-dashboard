<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otpCode;
    public $viewName;

    public function __construct($otpCode, $viewName)
    {
        $this->otpCode = $otpCode;
        $this->viewName = $viewName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi Akun Anda',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->viewName,
        );
    }

    public function attachments(): array
    {
        return [];
    }
}