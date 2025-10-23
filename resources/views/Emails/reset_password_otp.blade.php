<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode OTP Reset Password Anda</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
        <h1 style="color: #333;">Kode Reset Password Akun Anda</h1>
        <p>Halo,</p>
        <p>Gunakan kode berikut untuk mengatur ulang password akun Anda. Kode ini hanya berlaku selama 10 menit.</p>
        <p style="text-align: center; font-size: 24px; font-weight: bold; color: #007BFF; letter-spacing: 5px; margin: 20px 0; padding: 10px; border: 1px dashed #007BFF; background-color: #f0f8ff;">
            {{ $otpCode }}
        </p>
        <p>Jika Anda tidak merasa meminta ini, mohon abaikan email ini.</p>
        <p>Terima kasih.</p>
    </div>
</body>
</html>