#!/bin/sh
set -e

echo "Starting PoliSlot Admin Entrypoint..."

# --- 1. FIX PERMISSION .ENV (Aggressive Mode) ---
ENV_FILE="/var/www/html/.env"

if [ -f "$ENV_FILE" ]; then
    echo "Attempting to unlock .env file..."
    
    # FORCE CHANGE OWNERSHIP to www-data
    # This is critical for bind-mounted .env files on Linux/WSL
    chown www-data:www-data "$ENV_FILE" || echo "Note: Failed to chown .env (likely bind-mount permissions)"
    
    # Cara 1: Standard Chmod (Sering gagal di bind-mount)
    chmod 666 "$ENV_FILE" || echo "chmod failed on .env (expected on bind-mounts)"

    # Cara 2: ACL (Lebih kuat, mencoba memberi akses rw ke user www-data secara spesifik)
    # Ini berguna jika file dimiliki root tapi kita ingin www-data bisa nulis
    if command -v setfacl >/dev/null; then
        setfacl -m u:www-data:rw "$ENV_FILE" || echo "setfacl failed (host filesystem strictly locked)"
    fi
else
    echo ".env file not found! Skipping permission fix."
fi

# --- 2. FIX STORAGE PERMISSIONS ---
echo "Fixing storage permissions..."
# Gunakan ACL untuk storage agar www-data pasti bisa nulis
# setfacl -R (Recursive) -m (Modify) u:www-data:rwx (Read Write Execute)
if command -v setfacl >/dev/null; then
    setfacl -R -m u:www-data:rwx /var/www/html/storage
    setfacl -d -R -m u:www-data:rwx /var/www/html/storage
# Trigger auto-discovery untuk Laravel
    # php artisan package:discover --ansi
else
    # Fallback jika setfacl gagal
    chown -R www-data:www-data /var/www/html/storage
    chmod -R 775 /var/www/html/storage
fi

# --- 2b. FIX BOOTSTRAP/CACHE PERMISSIONS ---
# Cache folder perlu bisa ditulis oleh web server
CACHE_DIR="/var/www/html/bootstrap/cache"
if [ -d "$CACHE_DIR" ]; then
    echo "Fixing bootstrap/cache permissions..."
    chown -R www-data:www-data "$CACHE_DIR"
    chmod -R 775 "$CACHE_DIR"
fi

# --- 3. SETUP STRUKTUR BACKUP ---
BACKUP_DIR="/var/www/html/storage/app/backups"

echo "Setting up backup directories at $BACKUP_DIR..."
mkdir -p "$BACKUP_DIR/manual"
mkdir -p "$BACKUP_DIR/daily"
mkdir -p "$BACKUP_DIR/hourly"
mkdir -p "$BACKUP_DIR/every_3days"

# Pastikan permission backup folder terbuka untuk www-data
chown -R www-data:www-data "$BACKUP_DIR"
chmod -R 775 "$BACKUP_DIR"

# --- 4. SETUP PRIVATE KEYS ---
KEY_DIR="/var/www/html/storage/app/private/keys"
mkdir -p "$KEY_DIR"
chown -R www-data:www-data "$KEY_DIR"
chmod -R 775 "$KEY_DIR"

# --- 5. ENSURE STORAGE LINK ---
# Fixes "missing link" issue after restart, especially on WSL/Windows mounts
LINK_PATH="/var/www/html/public/storage"
echo "Checking storage symlink at $LINK_PATH..."

if [ -L "$LINK_PATH" ]; then
    # It is a symlink, check if valid
    if [ ! -e "$LINK_PATH" ]; then
        echo "Found broken symlink. Recreating..."
        rm "$LINK_PATH"
        php artisan storage:link
    else
        echo "Symlink exists and is valid."
    fi
elif [ -d "$LINK_PATH" ]; then
    # It is a directory (Not a symlink)
    echo "Directory found at $LINK_PATH (likely bind-mount from docker-compose). Skipping link creation."
else
    # Nothing exists
    echo "No link found. Creating..."
    php artisan storage:link
fi

# Ensure the symlink is owned by www-data (safety net)
if [ -L "$LINK_PATH" ]; then
    chown -h www-data:www-data "$LINK_PATH"
fi

echo "Environment Ready. Executing Command..."

exec "$@"