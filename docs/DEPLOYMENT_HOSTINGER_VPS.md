# Hostinger VPS KVM2 Deployment & Operational Runbook

**Target Platform:** Hostinger KVM 2 VPS (2 vCPU, 8 GB RAM, 100 GB NVMe, Ubuntu 24.04 LTS / Debian 12)  
**Target Domain:** `joygivers.org` (or approved event subdomain)  
**Database:** MySQL 8.0 InnoDB  
**Web Server:** Nginx with PHP 8.3-FPM  
**Process Supervisor:** Systemd / Supervisor for queue workers and scheduler  

---

## 1. Initial Server Provisioning

### System Update & Dependencies
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip ufw software-properties-common nginx mysql-server supervisor certbot python3-certbot-nginx

# Add PHP 8.3 repository (Ondřej Surý PPA)
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-curl php8.3-mbstring \
    php8.3-xml php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl php8.3-redis
```

### Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Firewall Hardening (UFW)
```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

---

## 2. MySQL 8 Database Configuration

### Secure Installation & Production Credentials
```bash
sudo mysql_secure_installation
```

### Create Database & Dedicated User
```sql
CREATE DATABASE sagar_kinare_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'sk_app'@'127.0.0.1' IDENTIFIED WITH caching_sha2_password BY 'STRONG_GENERATED_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON sagar_kinare_prod.* TO 'sk_app'@'127.0.0.1';
FLUSH PRIVILEGES;
```

---

## 3. Application Deployment

### Directory Setup & Permissions
```bash
sudo mkdir -p /var/www/sagar-kinare
sudo chown -R $USER:www-data /var/www/sagar-kinare
cd /var/www/sagar-kinare
git clone <repository_url> .

# Install dependencies (no dev packages)
composer install --no-dev --optimize-autoloader

# Environment Configuration
cp .env.example .env
nano .env # Set production DB, Razorpay live keys (when approved), and app key
php artisan key:generate

# File Permissions
sudo chown -R www-data:www-data /var/www/sagar-kinare/storage /var/www/sagar-kinare/bootstrap/cache
sudo chmod -R 775 /var/www/sagar-kinare/storage /var/www/sagar-kinare/bootstrap/cache

# Production Optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force --seed
```

---

## 4. Nginx Web Server Configuration

Create `/etc/nginx/sites-available/sagar-kinare`:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name event.joygivers.org;
    root /var/www/sagar-kinare/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site & install SSL certificate:
```bash
sudo ln -s /etc/nginx/sites-available/sagar-kinare /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
sudo certbot --nginx -d event.joygivers.org
```

---

## 5. Supervisor Worker Configuration (Background Jobs)

Create `/etc/supervisor/conf.d/sagar-kinare-worker.conf`:
```ini
[program:sagar-kinare-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sagar-kinare/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/sagar-kinare-worker.log
stopwaitsecs=3600
```

Start workers:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sagar-kinare-worker:*
```

---

## 6. System Scheduler (Hold Expiry Sweeper)

Add to www-data crontab (`sudo crontab -u www-data -e`):
```cron
* * * * * cd /var/www/sagar-kinare && php artisan schedule:run >> /dev/null 2>&1
```

---

## 7. Automated Daily Encrypted Database Backups

Create `/usr/local/bin/backup-sagar-kinare.sh`:
```bash
#!/bin/bash
set -e

BACKUP_DIR="/var/backups/sagar-kinare"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="${BACKUP_DIR}/db_backup_${TIMESTAMP}.sql.gz"
ENC_FILE="${BACKUP_FILE}.enc"
PASSPHRASE_FILE="/etc/sagar-kinare/backup_passphrase.key"

mkdir -p "$BACKUP_DIR"

# 1. Dump MySQL Database using InnoDB Single Transaction
mysqldump -u sk_app -p'PASSWORD_HERE' --single-transaction --quick --routines sagar_kinare_prod | gzip > "$BACKUP_FILE"

# 2. Encrypt with AES-256-CBC
openssl enc -aes-256-cbc -salt -in "$BACKUP_FILE" -out "$ENC_FILE" -pass file:"$PASSPHRASE_FILE"
rm "$BACKUP_FILE"

# 3. Retain last 14 days locally
find "$BACKUP_DIR" -type f -name "*.enc" -mtime +14 -delete

echo "Backup completed: ${ENC_FILE}"
```

Schedule daily at 02:00 AM:
```cron
0 2 * * * /usr/local/bin/backup-sagar-kinare.sh >> /var/log/sagar-kinare-backup.log 2>&1
```

---

## 8. Disaster Recovery & Restore Drill

To restore from an encrypted backup archive:
```bash
# 1. Decrypt backup
openssl enc -d -aes-256-cbc -in db_backup_YYYYMMDD_HHMMSS.sql.gz.enc -out restored.sql.gz -pass file:/etc/sagar-kinare/backup_passphrase.key

# 2. Decompress and restore into MySQL
gunzip < restored.sql.gz | mysql -u sk_app -p'PASSWORD_HERE' sagar_kinare_prod

# 3. Clear application cache
cd /var/www/sagar-kinare && php artisan cache:clear && php artisan config:clear
```
