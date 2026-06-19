# JeuxTepique

JeuxTepique est une plateforme de mini-jeux en PHP natif pour serveur LAMP. Le projet inclut authentification, profils avec avatars, tableau de bord, morpion solo contre une IA simple, multijoueur par invitation, historique, classements et administration.

## Stack

- Debian
- Apache
- MariaDB
- PHP natif avec PDO
- HTML, CSS, JavaScript
- Aucun framework lourd

## Arborescence

```text
/var/www/jeuxtepique
|-- apache/
|   `-- jeuxtepique.conf
|-- app/
|   |-- Controllers/
|   |-- Core/
|   |-- Models/
|   |-- Security/
|   `-- Views/
|-- assets/
|   |-- css/
|   |-- img/
|   `-- js/
|-- config/
|   `-- config.php
|-- games/
|   `-- tictactoe/
|-- public/
|-- scripts/
|-- sql/
|   `-- schema.sql
`-- README.md
```

## Installation LAMP sur Debian

Les commandes ci-dessous sont a executer avec un compte root, ou apres :

```bash
su -
```

```bash
apt update
apt install -y apache2 mariadb-server php php-cli php-mysql php-mbstring php-xml php-curl unzip
systemctl enable --now apache2 mariadb
```

Copier le projet :

```bash
mkdir -p /var/www/jeuxtepique
rsync -av --delete ./ /var/www/jeuxtepique/
chown -R www-data:www-data /var/www/jeuxtepique
find /var/www/jeuxtepique -type d -exec chmod 755 {} \;
find /var/www/jeuxtepique -type f -exec chmod 644 {} \;
```

## Base MariaDB

Créer la base et l'utilisateur SQL :

```bash
mysql
```

```sql
CREATE DATABASE jeuxtepique CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'jeuxtepique_user'@'localhost' IDENTIFIED BY 'ChangeMe_StrongPassword_2026!';
GRANT ALL PRIVILEGES ON jeuxtepique.* TO 'jeuxtepique_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Importer le schéma :

```bash
mysql jeuxtepique < /var/www/jeuxtepique/sql/schema.sql
```

Adapter ensuite le mot de passe dans `/var/www/jeuxtepique/config/config.php` et dans le VirtualHost si vous le changez.

## Apache VirtualHost

```bash
cp /var/www/jeuxtepique/apache/jeuxtepique.conf /etc/apache2/sites-available/jeuxtepique.conf
a2enmod headers
a2ensite jeuxtepique.conf
a2dissite 000-default.conf
apache2ctl configtest
systemctl reload apache2
```

Pour un test local, ajouter dans `/etc/hosts` :

```text
127.0.0.1 jeuxtepique.local
```

Le site sera disponible sur `http://jeuxtepique.local`.

## Compte admin

Le compte admin est cree en CLI pour beneficier de `password_hash()` et eviter un mot de passe par defaut dans le SQL.

```bash
cd /var/www/jeuxtepique
runuser -u www-data -- php scripts/create_admin.php
```

Le mot de passe admin doit contenir au moins 10 caracteres, une majuscule, une minuscule et un chiffre.

## Verification technique

Verifier la syntaxe PHP sur Debian :

```bash
cd /var/www/jeuxtepique
find app public games config scripts -name "*.php" -print0 | xargs -0 -n1 php -l
```

Verifier la connexion HTTP :

```bash
curl -I http://jeuxtepique.local/health.php
```

## Scripts VM utiles

Tous les scripts VM sont dans `scripts/` et se lancent en root avec `su -`.

### Supprimer l'application deja installee

Ce script desactive Apache, peut faire une sauvegarde SQL, supprime la base, l'utilisateur SQL et `/var/www/jeuxtepique`.

```bash
cd /var/www/jeuxtepique
chmod +x scripts/*.sh
./scripts/remove_app.sh
```

Il demande de taper `SUPPRIMER` avant de faire quoi que ce soit.

### Reinstaller depuis GitHub

```bash
DB_PASSWORD='ChangeMe_StrongPassword_2026!' APP_URL='http://IP_OU_DOMAINE' ./scripts/install_from_github.sh https://github.com/TON_COMPTE/jeuxtepique.git
```

Exemple avec une IP locale :

```bash
DB_PASSWORD='ChangeMe_StrongPassword_2026!' APP_URL='http://192.168.1.50' ./scripts/install_from_github.sh https://github.com/TON_COMPTE/jeuxtepique.git
```

Puis cree le compte admin :

```bash
cd /var/www/jeuxtepique
runuser -u www-data -- php scripts/create_admin.php
```

### Mettre a jour depuis GitHub

Quand tu modifies le projet sur ton PC et que tu as pousse sur GitHub :

```bash
cd /var/www/jeuxtepique
./scripts/update_from_github.sh
```

Le script fait `git pull`, applique les migrations SQL, remet les droits, teste Apache et recharge Apache.

## GitHub pour debutant

### 1. Creer le depot sur GitHub

1. Va sur `https://github.com`.
2. Connecte-toi.
3. Clique sur `New repository`.
4. Nom du depot : `jeuxtepique`.
5. Choisis `Public` ou `Private`.
6. Ne coche pas `Add a README`.
7. Clique sur `Create repository`.

GitHub affichera une URL comme :

```text
https://github.com/TON_COMPTE/jeuxtepique.git
```

### 2. Envoyer le projet depuis ton PC

Dans PowerShell, dans le dossier du projet :

```powershell
cd "C:\Users\arthu\Documents\LAMP JeuTePique"
git init
git add .
git commit -m "Initial commit JeuxTepique"
git branch -M main
git remote add origin https://github.com/TON_COMPTE/jeuxtepique.git
git push -u origin main
```

Remplace `TON_COMPTE` par ton pseudo GitHub.

### 3. Modifier plus tard

Sur ton PC :

```powershell
cd "C:\Users\arthu\Documents\LAMP JeuTePique"
git add .
git commit -m "Ma modification"
git push
```

Sur la VM :

```bash
cd /var/www/jeuxtepique
./scripts/update_from_github.sh
```

## Fonctionnement

- `public/` est le seul dossier expose par Apache.
- `assets/` est servi via un alias Apache.
- `app/bootstrap.php` charge la configuration, l'autoload, les sessions et les helpers.
- Les controleurs gerent les pages et deleguent la base aux modeles.
- Les modeles utilisent PDO avec requetes preparees.
- Les vues echappent les sorties avec `e()`.
- Les formulaires sensibles utilisent un jeton CSRF.
- Les mots de passe sont hashes avec `password_hash()`.
- Le morpion persiste le plateau, les joueurs, les coups et le resultat en base.
- Les liens d'invitation utilisent un token aleatoire de 32 caracteres.

## Procedure de test fonctionnel

1. Creer un compte `alice`.
2. Creer un compte `bob` dans un autre navigateur ou une session privee.
3. Avec `alice`, ouvrir le dashboard puis cliquer sur `Inviter un ami`.
4. Copier le lien `/join.php?token=...`.
5. Ouvrir le lien avec `bob`.
6. Si `bob` n'est pas connecte, se connecter ou s'inscrire, puis verifier la redirection automatique vers la partie.
7. Jouer un morpion complet, chacun son tour.
8. Verifier que la partie passe en `finished`.
9. Ouvrir `Classements` et verifier les victoires, defaites ou egalites.
10. Se connecter avec l'admin, ouvrir `Admin`, verifier la liste utilisateurs et parties, puis tester la suppression d'une partie de test.

## Securite incluse

- Sessions PHP avec cookies `HttpOnly`, `SameSite=Lax` et regeneration periodique d'ID.
- Hash des mots de passe avec `password_hash()`.
- Requetes preparees PDO.
- Echappement HTML systematique dans les vues.
- CSRF sur inscription, connexion, profil, creation de partie, coups et actions admin.
- Validation des pseudos, emails, mots de passe, avatars et positions de jeu.
- DocumentRoot limite a `public/`.
- Acces refuse aux dossiers `app`, `config` et `sql`.

## Evolutions possibles

- Puissance 4 avec grille 7x6 et detection de lignes.
- Quiz duel avec categories, minuterie et questions aleatoires.
- Chat de partie stocke en base.
- Notifications d'invitation et de tour joueur.
- WebSocket plus tard pour le temps reel.
- API REST pour les jeux, profils et classements.
- Plus d'avatars et personnalisation de bordure.
- Autres mini-jeux : bataille navale, memory, pendu, mastermind, 2048 duel, reversi, dames simplifiees, snake duel, mots meles, pierre-feuille-ciseaux.
