<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kode Verifikasi Akun Anda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f6f6f6;
            padding: 20px;
            color: #333;
        }
        .container {
            background: #ffffff;
            border-radius: 8px;
            padding: 25px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .otp {
            font-size: 28px;
            letter-spacing: 6px;
            font-weight: bold;
            color: #2a7ae2;
            text-align: center;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Kode Verifikasi Akun Anda</h2>
        <p>Halo,</p>
        <p>Terima kasih telah mendaftar. Berikut adalah kode OTP Anda:</p>

        <div class="otp">{{ $otpCode }}</div>

        <p>Kode ini berlaku selama <strong>10 menit</strong>.</p>
        <p>Jika Anda tidak merasa melakukan pendaftaran, abaikan email ini.</p>

        <div class="footer">
            <p>© {{ date('Y') }} Aplikasi Anda — Semua Hak Dilindungi</p>
        </div>
    </div>
</body>
</html>
