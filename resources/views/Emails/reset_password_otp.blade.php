<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kode OTP Reset Password</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>Halo!</h2>
    <p>Kami menerima permintaan untuk mereset password akun Anda.</p>
    <p>Kode OTP Anda adalah:</p>

    <h1 style="background-color:#000;color:#fff;display:inline-block;padding:10px 20px;border-radius:8px;">
        {{ $otpCode }}
    </h1>

    <p>Kode ini akan kedaluwarsa dalam <strong>10 menit</strong>.</p>
    <p>Jika Anda tidak meminta reset password, abaikan email ini.</p>

    <p>Salam,<br>Tim PoliSlot</p>
</body>
</html>
