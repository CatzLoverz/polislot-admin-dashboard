<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Peringatan Keamanan: Akun Anda Terkunci</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; border-top: 5px solid #dc3545;">
        <h1 style="color: #dc3545;">Peringatan Keamanan</h1>
        <p>Halo {{ $user->name }},</p>
        <p>Sistem kami telah mengunci akun Anda sementara selama 10 menit karena mendeteksi beberapa kali percobaan login yang gagal.</p>
        <p>Berikut adalah rincian dari aktivitas tersebut:</p>
        <ul style="background-color: #f8f9fa; padding: 15px 30px; border-radius: 5px; list-style-type: none;">
            <li><strong>Waktu Kejadian:</strong> {{ $lockTime }}</li>
            <li><strong>Alamat IP:</strong> {{ $ipAddress }}</li>
            <li><strong>Lokasi:</strong> {{ $location }}</li>
            <li><strong>Perangkat/Browser:</strong> {{ $deviceInfo }}</li>
        </ul>
        <p>Jika ini adalah Anda yang lupa kata sandi, atau jika Anda mencurigai ada orang lain yang mencoba mengakses akun Anda, kami sangat menyarankan Anda untuk segera mereset kata sandi dengan menekan tombol di bawah ini.</p>
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('app.redirect', ['email' => $user->email, 'token' => $token]) }}" style="background-color: #007BFF; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Pulihkan Akun / Reset Password
            </a>
        </div>
        <p style="font-size: 12px; color: #777;">*Catatan: Tombol di atas akan membuka aplikasi secara otomatis dan Anda dapat membuat kata sandi baru. Link ini hanya dapat digunakan satu kali.</p>
        <p>Terima kasih atas perhatian Anda demi menjaga keamanan akun.</p>
    </div>
</body>
</html>
