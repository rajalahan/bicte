# Production Deployment

This guide assumes Ubuntu 22.04 with Nginx + PHP-FPM + MySQL on a single VM inside the municipality LAN. Adapt freely for Windows Server / IIS.

## 1. System packages

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-intl \
  php8.2-bcmath unzip git
sudo systemctl enable --now nginx mysql php8.2-fpm
```

Install Composer:
```bash
php -r "copy('https://getcomposer.org/installer','composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm composer-setup.php
```

## 2. Database

```bash
sudo mysql_secure_installation
sudo mysql -e "CREATE DATABASE bicte CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
              CREATE USER 'bicte'@'localhost' IDENTIFIED BY 'CHANGE_ME';
              GRANT ALL ON bicte.* TO 'bicte'@'localhost';
              FLUSH PRIVILEGES;"
```

## 3. Application

```bash
sudo mkdir -p /var/www/bicte
sudo chown -R $USER:$USER /var/www/bicte
git clone https://github.com/rajalahan/bicte.git /var/www/bicte
cd /var/www/bicte/backend
cp .env.example .env
# Edit .env: APP_ENV=production, APP_DEBUG=false, APP_URL, DB_*
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force --seed
php artisan storage:link
php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
```

## 4. Nginx vhosts

`/etc/nginx/sites-available/bicte`:

```nginx
# 1. Kiosk (static frontend)
server {
    listen 80;
    server_name kiosk.lahanmun.local;
    root /var/www/bicte/frontend;
    index index.html;

    location / { try_files $uri $uri/ /index.html; }

    # cache static assets aggressively
    location ~* \.(?:css|js|svg|png|jpg|jpeg|gif|woff2?)$ {
        expires 30d;
        access_log off;
        add_header Cache-Control "public, immutable";
    }
}

# 2. API + Admin (Laravel)
server {
    listen 80;
    server_name api.lahanmun.local admin.lahanmun.local;
    root /var/www/bicte/backend/public;
    index index.php;
    client_max_body_size 32M;

    location / { try_files $uri $uri/ /index.php?$query_string; }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
    }

    location ~ /\. { deny all; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/bicte /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

Tell the frontend where the API lives (one-time): edit `frontend/index.html` (and other pages) to add inside `<head>`:

```html
<meta name="api-base" content="http://api.lahanmun.local/api">
```

## 5. TLS (Let's Encrypt – only if reachable from internet)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d api.lahanmun.gov.np -d admin.lahanmun.gov.np -d kiosk.lahanmun.gov.np
```

For LAN-only deployments, generate a self-signed cert with the office CA or just stay on HTTP within the LAN.

## 6. Queue worker (for SMS/email notifications)

`/etc/systemd/system/bicte-queue.service`:

```ini
[Unit]
Description=Bicte Laravel queue worker
After=network.target mysql.service

[Service]
User=www-data
Group=www-data
Restart=always
WorkingDirectory=/var/www/bicte/backend
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now bicte-queue
```

## 7. Scheduler (cron)

```bash
sudo crontab -e -u www-data
# Add:
* * * * * cd /var/www/bicte/backend && php artisan schedule:run >> /dev/null 2>&1
```

## 8. Backups (daily at 02:00)

`/etc/cron.daily/bicte-backup`:

```bash
#!/bin/bash
set -e
DAY=$(date +%F)
DIR=/var/backups/bicte
mkdir -p "$DIR"
mysqldump --single-transaction bicte | gzip > "$DIR/db-$DAY.sql.gz"
tar -czf "$DIR/storage-$DAY.tar.gz" -C /var/www/bicte/backend storage/app/public
find "$DIR" -mtime +30 -delete
```

```bash
sudo chmod +x /etc/cron.daily/bicte-backup
```

## 9. Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

## 10. Health monitoring

- `GET /api/health` returns `{ "status": "ok" }`. Hook this into your uptime monitor (Uptime Kuma / cron + curl).
- Logs: `backend/storage/logs/laravel.log`, `journalctl -u bicte-queue`.
- Optional Sentry/Bugsnag — drop the SDK package and DSN into `.env`.

---

For the touchscreen kiosk PC setup (Chrome kiosk + autostart) see [`KIOSK_SETUP.md`](KIOSK_SETUP.md).
