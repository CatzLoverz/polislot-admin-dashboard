<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Membuka Aplikasi...</title>
    <script>
        window.onload = function() {
            var email = "{{ $email ?? '' }}";
            var url = "polislot://forgot-password" + (email ? "?email=" + encodeURIComponent(email) : "");
            
            // Coba redirect otomatis ke aplikasi
            window.location.href = url;

            // Jika setelah 3 detik masih di halaman ini, beri pesan manual
            setTimeout(function() {
                document.getElementById('fallback-msg').style.display = 'block';
            }, 3000);
        };
    </script>
</head>
<body style="text-align: center; margin-top: 50px; font-family: Arial, sans-serif;">
    <p>Sedang mencoba membuka aplikasi PoliSlot...</p>
    
    <div id="fallback-msg" style="display: none; margin-top: 20px;">
        <p style="color: #666; font-size: 14px;">Jika aplikasi tidak terbuka otomatis, kemungkinan aplikasi belum terpasang atau perangkat Anda tidak mendukung Deep Link.</p>
        <p><a href="polislot://forgot-password?email={{ urlencode($email ?? '') }}" style="padding: 10px 20px; background-color: #007BFF; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">Klik di sini untuk mencoba lagi</a></p>
    </div>
</body>
</html>
