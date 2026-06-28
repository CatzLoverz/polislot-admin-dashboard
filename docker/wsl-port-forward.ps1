# ============================================
# Script Auto Port Forwarding WSL untuk Polislot
# ============================================

# Daftar port yang akan di-forward
# 8080: Web App (Laravel)
# 1883: MQTT TCP
# 9001: MQTT WebSockets
$ports = @(8080, 1883, 9001)

# Dapatkan IP WSL terbaru
$wslIp = (wsl hostname -I).Trim().Split()[0]

if (-not $wslIp) {
    Write-Error "Gagal mendapatkan IP WSL. Pastikan WSL sudah berjalan."
    exit 1
}

Write-Host "IP WSL terdeteksi: $wslIp"

try {
    foreach ($port in $ports) {
        # Hapus rule lama jika ada
        $existing = netsh interface portproxy show v4tov4 | Select-String $port
        if ($existing) {
            netsh interface portproxy delete v4tov4 listenport=$port listenaddress=0.0.0.0 | Out-Null
        }

        # Buat rule baru
        netsh interface portproxy add v4tov4 listenport=$port listenaddress=0.0.0.0 connectport=$port connectaddress=$wslIp | Out-Null
        
        # Buka firewall (jika belum ada)
        $ruleName = "WSL Polislot Port $port"
        $firewallRule = Get-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue
        if (-not $firewallRule) {
            New-NetFirewallRule -DisplayName $ruleName -Direction Inbound -LocalPort $port -Protocol TCP -Action Allow | Out-Null
            Write-Host "Firewall rule untuk port $port dibuat"
        }
        
        Write-Host "Port forwarding aktif: 0.0.0.0:$port -> $($wslIp):$port"
    }

    # Verifikasi
    Write-Host "`nSemua Rule aktif saat ini:"
    netsh interface portproxy show v4tov4

    Write-Host "`n======================================================="
    Write-Host "Port Forwarding sedang berjalan..."
    Write-Host "Tekan CTRL+C di sini untuk menghentikan dan menghapus rule."
    Write-Host "======================================================="

    # Tahan script agar tidak langsung keluar
    while ($true) {
        Start-Sleep -Seconds 1
    }

} finally {
    Write-Host "`n[Membersihkan...] Menghapus rule port forwarding & firewall..."
    foreach ($port in $ports) {
        # Hapus portproxy
        netsh interface portproxy delete v4tov4 listenport=$port listenaddress=0.0.0.0 | Out-Null
        
        # Hapus firewall rule
        $ruleName = "WSL Polislot Port $port"
        Remove-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue | Out-Null

        Write-Host "Rule port forwarding dan firewall untuk port $port telah dihapus."
    }
    Write-Host "Selesai. Script dihentikan."
}