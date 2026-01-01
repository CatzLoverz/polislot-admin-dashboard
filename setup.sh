#!/bin/bash


PROJECT_ID=$(basename "$PWD")

LOGROTATE_CONF="/etc/logrotate.d/${PROJECT_ID}-mariadb"

# Path Konfigurasi Project
LOG_DIR="storage/logs/mariadb"
DB_USER_UID=999
DB_USER_GID=999

echo "Memulai Setup untuk Project: $PROJECT_ID"
echo "Konfigurasi Logrotate akan disimpan sebagai: $LOGROTATE_CONF"

# ----------------------------------------------------------
# TAHAP 1: BERSIH-BERSIH (LOKAL)
# ----------------------------------------------------------
echo "Membersihkan container & volume (Lokal)..."
# Hanya mematikan container yang ada di docker-compose.yaml folder ini
docker compose down -v 

# Hapus folder log lokal agar fresh
sudo rm -rf $LOG_DIR

# Hapus config logrotate lama MILIK PROJECT INI SAJA (Jika ada)
if [ -f "$LOGROTATE_CONF" ]; then
    echo "Menghapus config logrotate lama..."
    sudo rm "$LOGROTATE_CONF"
fi

# ----------------------------------------------------------
# TAHAP 2: PRE-CREATE FILE LOG (Anti-Permission Error)
# ----------------------------------------------------------
echo "Menyiapkan folder & file log..."
mkdir -p $LOG_DIR

# Buat file kosong agar kita bisa set permission 644 di awal
touch $LOG_DIR/general.log
touch $LOG_DIR/slow-queries.log
touch $LOG_DIR/server-audit.log

echo "Mengunci permission file log..."
# Set Folder milik 999 (agar MariaDB bisa masuk)
sudo chown -R $DB_USER_UID:$DB_USER_GID $LOG_DIR
sudo chmod 755 $LOG_DIR

# Set File bisa dibaca User Lain (VS Code) -> 644
sudo chmod 644 $LOG_DIR/*.log

# ----------------------------------------------------------
# TAHAP 3: SETUP LOGROTATE (DINAMIS)
# ----------------------------------------------------------
echo "Menghubungkan Logrotate..."

# Buat Symlink dengan nama file UNIK (sesuai nama folder)
sudo ln -sf $(pwd)/docker/logrotate $LOGROTATE_CONF

# Syarat wajib Logrotate: File config harus milik root
sudo chown root:root $LOGROTATE_CONF
sudo chmod 644 $LOGROTATE_CONF

# ----------------------------------------------------------
# TAHAP 4: START DOCKER
# ----------------------------------------------------------
echo "Menyalakan Docker..."
docker compose up -d --build

echo "SETUP SELESAI!"
echo "   - Project ID  : $PROJECT_ID"
echo "   - Logrotate   : $LOGROTATE_CONF (Terisolasi)"
echo "   - Log Path    : $(pwd)/$LOG_DIR"