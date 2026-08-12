# Digital Signature Website

Website doanh nghiep cho dich vu chu ky so, hoa don dien tu va hop dong dien tu. Du an gom frontend gioi thieu dich vu, blog, form lien he tao lead, va khu vuc admin quan ly noi dung.

## Cong Nghe

- PHP 8.3+
- Laravel 13
- Laravel Breeze authentication
- SQLite mac dinh cho local, MySQL khuyen dung cho production
- Vite
- Tailwind CSS
- Alpine.js
- Flowbite
- TinyMCE cho noi dung bai viet
- PHPUnit cho automated tests
- Laravel Pint cho code style

## Tinh Nang Chinh

- Auth dang ky, dang nhap, xac thuc email, Google login.
- Role `admin` va `user`.
- Khoa tai khoan bang `status = active / blocked`.
- Admin dashboard voi thong ke bai viet, user, comment, views.
- Quan ly category.
- Quan ly post: draft, published, soft delete, restore, force delete, upload thumbnail.
- Blog frontend chi hien bai published.
- Comment nested 1 cap, soft delete, policy update/delete.
- Like/dislike comment bang Fetch API, moi user chi co mot vote.
- Form lien he luu database va gui email admin.
- Admin quan ly comments, contacts, users.
- Rate limit cho login, register, comment, vote, contact form va upload.
- Sanitize HTML TinyMCE server-side truoc khi luu post.

## Yeu Cau He Thong

- PHP `^8.3`
- Composer
- Node.js va npm
- SQLite extension cho local hoac MySQL server cho production
- SMTP account neu muon gui mail that

## Cai Dat Local

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Neu dung SQLite:

```bash
type nul > database\database.sqlite
php artisan migrate --seed
```

Neu dung MySQL, cap nhat cac bien `DB_*` trong `.env`, sau do chay:

```bash
php artisan migrate --seed
```

Local co the tiep tuc dung SQLite de phat trien nhanh. Production nen dung MySQL vi phu hop hosting pho bien, quan ly backup tot hon va on dinh hon khi co nhieu nguoi truy cap dong thoi.

Tao public storage link cho thumbnail:

```bash
php artisan storage:link
```

## Cau Hinh .env

Cac bien quan trong:

```env
APP_NAME="Digital Signature"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite

MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-google-app-password
MAIL_FROM_ADDRESS=your-gmail@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
MAIL_ADMIN_ADDRESS=admin@example.com

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=
```

Voi Gmail SMTP, `MAIL_PASSWORD` phai la Google App Password, khong phai mat khau Gmail dang nhap binh thuong.

## Migration Va Seeder

Chay migration:

```bash
php artisan migrate
```

Chay seed demo:

```bash
php artisan db:seed
```

Hoac chay ca hai:

```bash
php artisan migrate:fresh --seed
```

## Tai Khoan Demo

Seeder hien tao 2 tai khoan:

```text
Admin
Email: admin@gmail.com
Password: 12345678

User
Email: user@gmail.com
Password: 12345678
```

## Chay Ung Dung

Chay Laravel server:

```bash
php artisan serve
```

Chay Vite dev server:

```bash
npm run dev
```

Mo website:

```text
http://127.0.0.1:8000
```

Khu vuc admin:

```text
http://127.0.0.1:8000/admin/dashboard
```

## Kiem Thu Va Chuan Hoa Code

Chay Laravel Pint:

```bash
vendor\bin\pint
```

PHP lint toan bo file PHP:

```bash
Get-ChildItem -Recurse -Filter *.php -File |
  Where-Object { $_.FullName -notmatch '\\vendor\\|\\storage\\framework\\views\\' } |
  ForEach-Object { php -l $_.FullName }
```

Chay automated tests:

```bash
php artisan test
```

Build frontend:

```bash
npm run build
```

Du an hien chua cau hinh ESLint. Neu them ESLint sau nay, nen them script `npm run lint` vao `package.json`.

## Deploy

### Chuyen Tu SQLite Sang MySQL Khi Trien Khai

Khong copy truc tiep file `database/database.sqlite` len server roi mong MySQL doc duoc. SQLite va MySQL la hai he quan tri du lieu khac nhau.

Luong trien khai production moi:

```text
Tao database MySQL
-> Cau hinh DB_* trong production .env
-> php artisan migrate --force
-> php artisan db:seed --force
```

Vi du production `.env` voi MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=digital_signature
DB_USERNAME=digital_signature_user
DB_PASSWORD=your-strong-password
```

Neu can giu du lieu that dang nam trong SQLite local, can lam mot buoc migrate du lieu rieng: export du lieu tu SQLite, map schema/format, import vao MySQL va kiem tra lai quan he, timestamp, soft deletes. Khong nen lam viec nay bang cach copy file SQLite.

Checklist deploy co ban:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Production `.env` can dam bao:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
DB_CONNECTION=mysql
```

Khuyen nghi truoc deploy:

- Doi mat khau tai khoan demo hoac xoa demo users.
- Cau hinh SMTP that va `MAIL_ADMIN_ADDRESS`.
- Dung database production rieng, backup dinh ky.
- Tro web server vao thu muc `public`.
- Cap quyen ghi cho `storage` va `bootstrap/cache`.
- Bat HTTPS va redirect HTTP sang HTTPS.
- Backup database va file upload trong `storage/app/public`.
- Cau hinh cron Laravel scheduler neu co scheduled jobs.
- Cau hinh queue worker neu chuyen email/job sang queue.
- Cau hinh log rotation va theo doi loi production.
- Khong commit file `.env`.
- Chay `php artisan test` va `npm run build` truoc khi release.

Runbook deploy chi tiet nam trong [DEPLOYMENT.md](DEPLOYMENT.md).
