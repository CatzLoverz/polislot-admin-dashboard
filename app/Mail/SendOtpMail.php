<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otpCode;

    public $viewName;

    public $subject;

    /**
     * Konstruktor mail.
     *
     * @param string $otpCode Kode OTP yang akan dikirim
     * @param string $viewName Nama view email yang digunakan
     * @param string $subject Subjek email
     */
    public function __construct($otpCode, $viewName, $subject)
    {
        $this->otpCode = $otpCode;
        $this->viewName = $viewName;
        $this->subject = $subject;
    }

    /**
     * Konfigurasi envelope email.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Konfigurasi konten email.
     *
     * @return Content
     */
    public function content(): Content
    {
        return new Content(
            view: $this->viewName,
        );
    }

    /**
     * Lampiran email.
     *
     * @return array
     */
    public function attachments(): array
    {
        return [];
    }
}
