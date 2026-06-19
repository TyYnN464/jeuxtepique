#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/jeuxtepique"
SITE_NAME="jeuxtepique"
DB_NAME="jeuxtepique"
DB_USER="jeuxtepique_user"
BACKUP_DIR="/root/jeuxtepique-backups"

if [ "$(id -u)" -ne 0 ]; then
    echo "Lance ce script en root : su -"
    exit 1
fi

echo "Ce script va supprimer l'application JeuxTepique de cette machine."
echo "Dossier : $APP_DIR"
echo "Site Apache : $SITE_NAME"
echo "Base MariaDB : $DB_NAME"
echo
read -r -p "Tape SUPPRIMER pour confirmer : " CONFIRM

if [ "$CONFIRM" != "SUPPRIMER" ]; then
    echo "Annule."
    exit 0
fi

read -r -p "Faire une sauvegarde SQL avant suppression ? [o/N] " DO_BACKUP
if [ "$DO_BACKUP" = "o" ] || [ "$DO_BACKUP" = "O" ]; then
    mkdir -p "$BACKUP_DIR"
    BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_$(date +%Y%m%d_%H%M%S).sql"
    if mysql -e "USE \`$DB_NAME\`;" >/dev/null 2>&1; then
        mysqldump "$DB_NAME" > "$BACKUP_FILE"
        echo "Sauvegarde creee : $BACKUP_FILE"
    else
        echo "Base $DB_NAME introuvable, sauvegarde ignoree."
    fi
fi

if command -v a2dissite >/dev/null 2>&1; then
    a2dissite "${SITE_NAME}.conf" >/dev/null 2>&1 || true
fi

rm -f "/etc/apache2/sites-available/${SITE_NAME}.conf"
rm -f "/etc/apache2/sites-enabled/${SITE_NAME}.conf"

if mysql -e "USE \`$DB_NAME\`;" >/dev/null 2>&1; then
    mysql -e "DROP DATABASE \`$DB_NAME\`;"
    echo "Base supprimee : $DB_NAME"
fi

if mysql -e "SELECT User FROM mysql.user WHERE User = '$DB_USER';" | grep -q "$DB_USER"; then
    mysql -e "DROP USER IF EXISTS '$DB_USER'@'localhost'; FLUSH PRIVILEGES;"
    echo "Utilisateur SQL supprime : $DB_USER"
fi

rm -rf "$APP_DIR"

apache2ctl configtest
systemctl reload apache2

echo "Application JeuxTepique supprimee."
