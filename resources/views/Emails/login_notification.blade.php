<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemberitahuan Login Akun Anda</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h1 style="color: #333;">Pemberitahuan Login Baru</h1>
        <p>Halo {{ $user->name }},</p>
        <p>Kami mendeteksi adanya login baru ke akun Anda dengan rincian sebagai berikut:</p>
        <ul style="background-color: #f8f9fa; padding: 15px 30px; border-radius: 5px; list-style-type: none;">
            <li><strong>Waktu:</strong> {{ $loginTime }}</li>
            <li><strong>Alamat IP:</strong> {{ $ipAddress }}</li>
            <li><strong>Perangkat/Browser:</strong> {{ $userAgent }}</li>
        </ul>
        <p>Jika ini adalah Anda, maka Anda dapat mengabaikan email ini.</p>
        <p>Jika Anda tidak merasa melakukan login ini, segera amankan akun Anda dengan mengubah kata sandi.</p>
        <div style="text-align: center; margin: 30px 0;">
            <a href="polislot://forgot-password?email={{ urlencode($user->email) }}" style="background-color: #007BFF; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Pulihkan Akun / Reset Password
            </a>
        </div>
        <p style="font-size: 12px; color: #777;">*Catatan: Tombol di atas akan membuka aplikasi jika Anda membukanya melalui perangkat seluler (memerlukan konfigurasi Deep Link).</p>
        <p>Terima kasih.</p>
    </div>
</body>
</html>
