# EcoTech OS – Production Deployment Guide

## 1. Server requirements
- PHP 8.4 (extensions: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, fileinfo, bcmath)
- MySQL 8 / MariaDB 10.6+ (SQLite is for development only)
- Nginx or Apache, Composer, Node 20+ (build step only)

## 2. First install
```bash
git clone https://github.com/rrose10/ecotech-os.git
cd ecotech-os/backend
composer install --no-dev --optimize-autoloader
cp .env.production.example .env      # then fill DB_* and APP_URL
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force          # categories (Salary, Advance Salary ...)
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
Point the web root to `backend/public`. Never expose `.env`, `storage` or `database`.

## 3. Must-have settings
- `APP_DEBUG=false`, `APP_ENV=production`, HTTPS enabled (Cloudflare Tunnel or Let's Encrypt)
- `SESSION_SECURE_COOKIE=true`
- Writable: `storage/`, `bootstrap/cache/`
- Change all default/seeded passwords before go-live

## 4. Scheduler and backups
Add one cron entry (Linux) or a Windows Task Scheduler job every minute:
```
* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1
```
This runs `php artisan db:backup` daily at 02:00 and keeps the last 14 backups in
`storage/app/backups`. Copy that folder to another machine/cloud regularly.
Restore test: do it once before go-live.

## 5. Updating
```bash
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## 6. Roles
| Role | Access |
|---|---|
| super_admin | everything incl. Activity Logs |
| admin | all management modules (HR + finance) |
| employee | Employee Portal only |

## 7. Accounting rules (do not bypass)
- Salary, Advance Salary and Loan expenses are created by their own modules and
  can only be changed there (the Expense module blocks editing/deleting them).
- A Paid payroll has exactly one salary expense and deducts the bank balance once.
- Loan-installment payrolls cannot be moved back to Pending or deleted automatically.

## 8. Tests
`php artisan test`
