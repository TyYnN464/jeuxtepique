#!/usr/bin/env bash
set -euo pipefail

REPO_URL="${1:-}"
APP_DIR="/var/www/jeuxtepique"
SITE_NAME="jeuxtepique"
DB_NAME="jeuxtepique"
DB_USER="jeuxtepique_user"
DB_PASSWORD="${DB_PASSWORD:-ChangeMe_StrongPassword_2026!}"
APP_URL="${APP_URL:-http://jeuxtepique.local}"

if [ "$(id -u)" -ne 0 ]; then
    echo "Lance ce script en root : su -"
    exit 1
fi

if [ -z "$REPO_URL" ]; then
    echo "Usage : ./scripts/install_from_github.sh https://github.com/TON_COMPTE/jeuxtepique.git"
    echo "Optionnel : DB_PASSWORD='motdepasse' APP_URL='http://IP_OU_DOMAINE' ./scripts/install_from_github.sh URL"
    exit 1
fi

apt update
apt install -y apache2 mariadb-server git php php-cli php-mysql php-mbstring php-xml php-curl rsync unzip
systemctl enable --now apache2 mariadb

if [ -d "$APP_DIR" ]; then
    echo "$APP_DIR existe deja. Supprime-le avec scripts/remove_app.sh ou choisis un dossier vide."
    exit 1
fi

git clone "$REPO_URL" "$APP_DIR"
cd "$APP_DIR"

mysql <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SQL

mysql "$DB_NAME" < "$APP_DIR/sql/schema.sql"

cp "$APP_DIR/apache/${SITE_NAME}.conf" "/etc/apache2/sites-available/${SITE_NAME}.conf"
sed -i "s#SetEnv APP_URL .*#SetEnv APP_URL ${APP_URL}#g" "/etc/apache2/sites-available/${SITE_NAME}.conf"
sed -i "s#SetEnv DB_PASSWORD .*#SetEnv DB_PASSWORD ${DB_PASSWORD}#g" "/etc/apache2/sites-available/${SITE_NAME}.conf"

a2enmod headers
a2ensite "${SITE_NAME}.conf"
a2dissite 000-default.conf >/dev/null 2>&1 || true

chown -R www-data:www-data "$APP_DIR"
find "$APP_DIR" -type d -exec chmod 755 {} \;
find "$APP_DIR" -type f -exec chmod 644 {} \;
chmod +x "$APP_DIR/scripts/"*.sh

apache2ctl configtest
systemctl reload apache2

echo
echo "Installation terminee."
echo "URL configuree : $APP_URL"
echo "Pour creer l'admin :"
echo "cd $APP_DIR && runuser -u www-data -- php scripts/create_admin.php"
