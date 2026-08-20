<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AccountLockedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;
    public $lockTime;
    public $ipAddress;
    public $deviceInfo;
    public $token;
    public $location;

    /**
     * Konstruktor mail notifikasi akun dikunci.
     *
     * @param User $user User yang akunnya terkunci
     * @param string $lockTime Waktu kejadian
     * @param string $ipAddress Alamat IP
     * @param string $deviceInfo Info perangkat (atau fallback User-Agent)
     * @param string $token Token untuk reset password
     */
    public function __construct($user, $lockTime, $ipAddress, $deviceInfo, $token)
    {
        $this->user = $user;
        $this->lockTime = $lockTime;
        $this->ipAddress = $ipAddress;
        $this->deviceInfo = $deviceInfo;
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
            subject: 'Peringatan Keamanan: Akun Anda Terkunci',
        );
    }

    /**
     * Konfigurasi konten email.
     *
     * @return Content
     */
    public function content(): Content
    {
        $this->location = 'Tidak diketahui';
        if ($this->ipAddress && $this->ipAddress !== '127.0.0.1' && $this->ipAddress !== '::1') {
            try {
                $response = Http::timeout(5)->get("http://ip-api.com/json/{$this->ipAddress}");
                if ($response->successful() && $response->json('status') === 'success') {
                    $this->location = $response->json('city') . ', ' . $response->json('country');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal melacak lokasi IP (Lockout): " . $e->getMessage());
            }
        }

        return new Content(
            view: 'Emails.account_locked_notification',
            with: [
                'location' => $this->location,
            ]
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
