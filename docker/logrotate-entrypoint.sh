#!/bin/sh
set -e

# 0. INSTALL LOGROTATE (Missing in Alpine base image)
apk add --no-cache logrotate

# 1. FIX PERMISSIONS (CRITICAL)
# Jika folder log kosong/baru (misal habis dihapus), permissionnya mungkin root:root.
# Kita harus ubah jadi 999:999 agar MariaDB bisa nulis log lagi.
mkdir -p /var/log/mysql
chown -R 999:999 /var/log/mysql
chmod 775 /var/log/mysql

# 2. DETECT OWNER OF UID 999
USR_999=$(awk -F: '$3 == 999 {print $1}' /etc/passwd)

if [ -n "$USR_999" ]; then
    echo "Found existing user for UID 999: $USR_999"
else
    echo "UID 999 is free. Checking Group ID 999..."
    
    # Check if GROUP 999 exists
    GRP_999=$(awk -F: '$3 == 999 {print $1}' /etc/group)
    
    if [ -n "$GRP_999" ]; then
        echo "Group 999 exists ($GRP_999). Creating user 'mysql' in existing group..."
        # Create user mysql with UID 999, but use EXISTING group (don't create new group)
        adduser -u 999 -D -H -G "$GRP_999" mysql
    else
        echo "Group 999 is free. Creating user 'mysql' and group..."
        adduser -u 999 -D -H mysql
    fi
    USR_999=mysql
fi

# 3. DETECT EFFECTIVE GROUP NAME
# Kita tidak boleh berasumsi nama group sama dengan nama user (misal user mysql tapi group ping).
# 'id -gn' akan memberitahu nama group primary yang valid buat user tersebut.
GRP_999=$(id -gn $USR_999)
echo "Configuring logrotate to run as User: $USR_999, Group: $GRP_999"

# 4. GENERATE CONFIG
cat <<EOF > /etc/logrotate.d/mariadb
/var/log/mysql/*.log {
    su $USR_999 $GRP_999
    daily
    rotate 7
    size 10M
    missingok
    notifempty
    compress
    delaycompress
    copytruncate
    create 644 $USR_999 $GRP_999
    sharedscripts
    postrotate
        find /var/log/mysql -type f -exec chmod 644 {} +
    endscript
}
EOF

# Pastikan permission config aman (harus root)
chmod 644 /etc/logrotate.d/mariadb
chown root:root /etc/logrotate.d/mariadb

# 3. RUN CRON
echo "Starting crond..."
exec /usr/sbin/crond -f -L 8
