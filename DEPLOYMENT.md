# Deployment Guide — Hospital Management System

## System Requirements

| Requirement | Version |
|-------------|---------|
| PHP | 8.3+ (extensions: BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML) |
| Composer | 2.x |
| Node.js | 20+ |
| npm | 10+ |
| MySQL | 8.0+ (or MariaDB 10.6+) |
| Web server | Nginx (recommended) or Apache |

---

## Installation

### 1. Clone and install dependencies

```bash
git clone <repository-url> hms
cd hms

composer install --no-dev --optimize-autoloader
npm ci
```

### 2. Environment configuration

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your values:

```env
APP_NAME="Hospital Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hms
DB_USERNAME=hms_user
DB_PASSWORD=your_secure_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=database

SESSION_DRIVER=file
CACHE_STORE=file
```

### 3. Database setup

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 4. Build frontend assets

```bash
npm run build
```

### 5. Storage and permissions

```bash
php artisan storage:link

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## Default Credentials

After seeding, log in with:

| Field | Value |
|-------|-------|
| Email | `admin@hms.com` |
| Password | `Admin@123` |

**Change this password immediately after first login.**

---

## Queue Worker

Database notifications (appointment reminders, IPD admissions, lab results, low stock alerts, salary generation) are sent via queued jobs. Run the worker as a persistent process:

### Using Supervisor (recommended)

Create `/etc/supervisor/conf.d/hms-worker.conf`:

```ini
[program:hms-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hms/artisan queue:work database --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hms/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start hms-worker:*
```

### Manual (development only)

```bash
php artisan queue:work database --tries=3
```

---

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate     /etc/ssl/certs/your-domain.crt;
    ssl_certificate_key /etc/ssl/private/your-domain.key;

    root /var/www/hms/public;
    index index.php;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Apache Configuration

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/hms/public

    <Directory /var/www/hms/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hms-error.log
    CustomLog ${APACHE_LOG_DIR}/hms-access.log combined
</VirtualHost>
```

Ensure `mod_rewrite` is enabled: `a2enmod rewrite`.

---

## Caching (Production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

To clear caches after a deployment:

```bash
php artisan optimize:clear
```

---

## Deployment Checklist

- [ ] `.env` configured with production values (`APP_DEBUG=false`, `APP_ENV=production`)
- [ ] Database created and credentials set
- [ ] `php artisan migrate --force` run
- [ ] `php artisan db:seed --force` run (first deployment only)
- [ ] `npm run build` completed (assets in `public/build/`)
- [ ] `php artisan storage:link` run
- [ ] Storage and cache directories writable by web server
- [ ] Queue worker running via Supervisor
- [ ] Caches warmed (`php artisan optimize`)
- [ ] Admin password changed from default
- [ ] SSL certificate installed and HTTPS enforced

---

## Updating an Existing Deployment

```bash
git pull

composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan optimize:clear
php artisan migrate --force
php artisan optimize

supervisorctl restart hms-worker:*
```

---

## REST API

The application exposes a REST API secured with Laravel Sanctum tokens.

**Base URL:** `https://your-domain.com/api`

### Authentication

```bash
# Obtain a token
curl -X POST https://your-domain.com/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@hms.com","password":"Admin@123"}'

# Use the token
curl https://your-domain.com/api/patients \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json"
```

### Available Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/login` | Obtain API token |
| POST | `/api/auth/logout` | Revoke current token |
| GET | `/api/auth/me` | Authenticated user info |
| GET | `/api/dashboard` | Summary statistics |
| GET/POST | `/api/patients` | List / create patients |
| GET/PUT | `/api/patients/{id}` | Show / update patient |
| GET/POST | `/api/appointments` | List / create appointments |
| GET | `/api/appointments/{id}` | Show appointment |
| PATCH | `/api/appointments/{id}/status` | Update appointment status |
| GET/POST | `/api/opd` | List / create OPD visits |
| GET | `/api/opd/{id}` | Show OPD visit |
| GET/POST | `/api/ipd` | List / create IPD admissions |
| GET | `/api/ipd/{id}` | Show IPD admission |
| GET | `/api/doctors` | List doctors |
| GET | `/api/lab` | List lab bookings |
| GET | `/api/lab/{id}` | Show lab booking |
| GET | `/api/notifications` | List notifications |
| GET | `/api/notifications/unread-count` | Unread notification count |
| PATCH | `/api/notifications/{id}/read` | Mark notification as read |
| POST | `/api/notifications/mark-all-read` | Mark all as read |

---

## Logs

Application logs are written to `storage/logs/laravel.log`. For production, consider shipping logs to a service like Papertrail or Datadog.

```bash
php artisan pail          # stream logs in terminal (development)
tail -f storage/logs/laravel.log
```
