# Production Deployment Runbook

Tai lieu nay dung cho deploy production Digital Signature Website. Local co the tiep tuc dung SQLite; production khuyen dung MySQL.

## 1. Chuan Bi Server

- PHP 8.3+ va cac extension Laravel can thiet.
- Composer va Node.js/npm neu server co SSH.
- MySQL database rieng cho production.
- Web server tro document root vao thu muc `public` neu hosting cho phep.
- HTTPS certificate cho domain production.
- SMTP account de gui email that.

Thu muc can cho web server ghi:

```text
storage
bootstrap/cache
```

Vi du Linux:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

## 2. Cau Hinh Production .env

Tao `.env` tren server tu `.env.production.example`, sau do cap nhat cac gia tri that:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tenmien.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=digital_signature
DB_USERNAME=digital_signature_user
DB_PASSWORD=your-strong-password

MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-google-app-password
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_ADMIN_ADDRESS=admin@tenmien.com
```

Neu tao `.env` moi, chay:

```bash
php artisan key:generate --force
```

Khong commit `.env` len git.

## 3. Deploy Code Tren VPS / Hosting Co SSH

Chay tren server:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Neu can seed tai khoan admin production, truoc khi chay seeder phai dat cac bien sau trong `.env`:

```env
ADMIN_NAME="Administrator"
ADMIN_EMAIL=admin@tenmien.com
ADMIN_PASSWORD=mat-khau-rieng-toi-thieu-12-ky-tu
```

Sau do chay:

```bash
php artisan db:seed --force
```

Khong dat cac bien `SEED_USER_*` tren production.

Khong copy truc tiep file SQLite len MySQL. Neu can giu du lieu that tu SQLite, can export/import du lieu rieng va kiem tra lai schema, foreign key, timestamp, soft delete.

## 4. HTTPS

- Bat HTTPS tren hosting, Nginx, Apache hoac reverse proxy.
- Redirect HTTP sang HTTPS.
- Dat `APP_URL=https://tenmien.com`.
- Kiem tra form login, contact, upload va admin sau khi bat HTTPS.

## 5. Backup

Can backup dinh ky:

- MySQL database.
- Thu muc upload, toi thieu la `storage/app/public`.
- File `.env` production o noi an toan.

Goi y lich:

- Database: hang ngay.
- Upload files: hang ngay hoac hang tuan tuy tan suat thay doi.
- Luu backup ngoai server chinh neu co the.

## 6. Cron Laravel Scheduler

Neu sau nay them scheduled jobs, cau hinh cron chay moi phut:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Hien tai du an chua co scheduler bat buoc, nhung nen san sang cau hinh khi co tac vu dinh ky.

## 7. Queue Worker

Hien tai email contact dang gui truc tiep. Neu chuyen email sang queue, production can queue worker.

Vi du voi Supervisor:

```ini
[program:digital-signature-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/project/storage/logs/worker.log
stopwaitsecs=3600
```

Sau moi lan deploy co queue worker, chay:

```bash
php artisan queue:restart
```

## 9. Deploy Rieng Cho InfinityFree

InfinityFree co PHP/MySQL/.htaccess nhung khong phai moi truong Laravel day du nhu VPS. Nen build va chuan bi tren may local, sau do upload ban da san sang.

Chay tren local truoc khi upload:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Neu chua co database production, tao MySQL tren control panel InfinityFree. Do InfinityFree thuong khong co SSH/artisan tren server, nen co 2 cach:

- Chay migration/seed tren mot MySQL local co schema tuong tu, export file `.sql`, roi import bang phpMyAdmin tren InfinityFree.
- Hoac import schema/du lieu thu cong bang phpMyAdmin neu ban da co file SQL rieng.

Upload toan bo Laravel codebase vao `htdocs`, bao gom:

- `app`, `bootstrap`, `config`, `database`, `public`, `resources`, `routes`, `storage`, `vendor`
- `composer.json`, `composer.lock`, `artisan`
- Thu muc `public/build` da duoc tao boi `npm run build`
- File `.env` production da tao tu `.env.production.example`
- File `.htaccess` o root project va file `public/.htaccess`

Khong upload:

- `.git`, `node_modules`, `.env` local, log local, cache local
- `tests` neu muon goi deploy gon hon

Voi upload anh, production `.env` nen de:

```env
FILESYSTEM_PUBLIC_ROOT=public/storage
```

Va tao thu muc `public/storage` tren hosting neu chua co. Cau hinh nay giup upload ghi truc tiep vao public storage, khong can `php artisan storage:link`.

Neu domain dang tro vao `htdocs` thay vi `htdocs/public`, root `.htaccess` trong repo se rewrite request vao `public/` va chan truy cap cac thu muc Laravel nhay cam.

## 8. Log Rotation

Khong de `storage/logs/laravel.log` phinh vo han. Cau hinh log rotation cua server hoac dung daily log:

```env
LOG_CHANNEL=daily
LOG_LEVEL=error
```

Theo doi dung luong thu muc:

```text
storage/logs
storage/app/public
```

## 10. Theo Doi Loi

Toi thieu can theo doi:

- HTTP 500/403/404 bat thuong.
- Laravel log.
- Mail delivery errors.
- Queue failed jobs neu dung queue.
- Dung luong disk va database.
- Backup co tao thanh cong hay khong.

Co the dung dich vu nhu Sentry, Bugsnag, hosting monitoring, hoac log collector rieng.

## 11. Checklist Sau Deploy

- Trang chu load duoc.
- `/blog` hien bai published.
- `/lien-he` gui form va tao contact trong admin.
- Email admin nhan duoc lead.
- `/admin/dashboard` chi admin vao duoc.
- Upload thumbnail hoat dong.
- `php artisan config:cache`, `route:cache`, `view:cache` khong loi.
- `APP_DEBUG=false` tren production.
