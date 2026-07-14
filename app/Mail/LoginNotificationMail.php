<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class LoginNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $loginTime;
    public $ipAddress;
    public $userAgent;
    public $token;

    /**
     * Konstruktor mail notifikasi login.
     *
     * @param User $user User yang melakukan login
     * @param string $loginTime Waktu login
     * @param string $ipAddress Alamat IP
     * @param string $userAgent User agent browser
     * @param string $token Token untuk verifikasi
     */
    public function __construct($user, $loginTime, $ipAddress, $userAgent, $token)
    {
        $this->user = $user;
        $this->loginTime = $loginTime;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->token = $token;
    }

    /**
     * Konfigurasi envelope email.
     *
     * @return Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pemberitahuan Login Akun Anda',
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
            view: 'Emails.login_notification',
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
