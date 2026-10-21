# YELENSCOL production host hardening

These files prepare the Linux host settings shown in the deployment checklist.
They do not change the developer machine's `.env` or run host-level commands
automatically. Apply them on the production server during a maintenance window.

## 1. UFW firewall and SSH brute-force protection

The script supports systemd-based Ubuntu/Debian hosts. It **resets existing UFW
rules** (briefly disabling UFW during the change), then permits only inbound TCP
80 and 443 plus TCP 22 from the supplied administrator CIDR. Review current
rules first, confirm the server really uses SSH port 22 and the administrator
address is stable, and run this in a maintenance window with provider console
access available. The script requires an explicit reset confirmation.

From an SSH session, pass the client's address explicitly (replace the example
with the real administrator IP; do not use the documentation-only address):

```bash
sudo env \
  CONFIRM_UFW_RESET=YES \
  SSH_ALLOWED_CIDR=203.0.113.10/32 \
  SSH_CLIENT_IP=203.0.113.10 \
  bash deploy/security/harden-ubuntu.sh
```

If connected through a VPN or bastion, use the address the server sees. For a
fixed office network, use its narrow CIDR rather than `0.0.0.0/0`. Check the
results with `sudo ufw status numbered` and `sudo fail2ban-client status sshd`.
Configure the cloud/provider firewall or security group with the same inbound
allow-list; host UFW does not control the provider network firewall.

## 2. MySQL loopback-only access

This setting is for a database running on the **same server** as Laravel. Do not
apply it when the application intentionally connects to a remote database.
Install the supplied option file in the server's MySQL/MariaDB configuration
include directory (commonly `/etc/mysql/mysql.conf.d/` for MySQL or
`/etc/mysql/mariadb.conf.d/` for MariaDB), then validate the configuration and
restart the matching database service during a maintenance window:

```bash
sudo install -m 0644 deploy/security/mysql/zz-yelenscol-bind-address.cnf \
  /etc/mysql/mysql.conf.d/zz-yelenscol-bind-address.cnf
sudo systemctl restart mysql
sudo ss -ltnp | grep ':3306'
```

The final command should show MySQL listening on `127.0.0.1:3306`, not
`0.0.0.0:3306` or a public address. Use the MariaDB include directory and
`mariadb` service name when applicable. Remove any conflicting later-loaded
`bind-address` setting. Create a dedicated database account restricted to
`localhost` with a randomly generated password; do not use MySQL `root` for
Laravel and do not open port 3306 in either firewall. Generate the password
with a trusted password manager or `openssl rand -base64 36`, then enter it in
the MySQL prompt and the server-side `.env` without putting it in shell history:

```sql
CREATE DATABASE yelenscol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'yelenscol'@'localhost' IDENTIFIED BY 'REPLACE_WITH_GENERATED_PASSWORD';
GRANT ALL PRIVILEGES ON yelenscol.* TO 'yelenscol'@'localhost';
```

## 3. Laravel production environment

Copy `deploy/security/.env.production.example` to the server's application
`.env` (outside version control), set the real HTTPS domain, database name and
dedicated DB account, and generate the key on that server:

```bash
cp deploy/security/.env.production.example .env
php artisan key:generate
```

Never commit the resulting `.env`, `APP_KEY`, or database password, paste them
into chat, or substitute a real secret into this example. Ensure `.env` is
owned by the deployment user and PHP-FPM group with mode `0640`. Then, as the
deployment user, run the project's production deployment steps:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Configure the web server's document root to Laravel's `public/` directory, enable
HTTPS with a valid certificate, and redirect HTTP to HTTPS. Do not expose `.env`,
`vendor/`, or `storage/` through the web server. Keep `APP_ENV=production` and
`APP_DEBUG=false` in the server environment.
