#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/jeuxtepique"
DB_NAME="jeuxtepique"

if [ "$(id -u)" -ne 0 ]; then
    echo "Lance ce script en root : su -"
    exit 1
fi

cd "$APP_DIR"

git pull --ff-only

if [ -d "$APP_DIR/sql/migrations" ]; then
    for migration in "$APP_DIR"/sql/migrations/*.sql; do
        [ -e "$migration" ] || continue
        echo "Application migration : $(basename "$migration")"
        mysql "$DB_NAME" < "$migration"
    done
fi

chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 755 {} \;
find "$APP_DIR" -type f -exec chmod 644 {} \;
chmod +x "$APP_DIR/scripts/"*.sh

apache2ctl configtest
systemctl reload apache2

echo "Mise a jour terminee."
