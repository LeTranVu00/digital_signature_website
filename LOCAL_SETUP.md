# 🚀 Hướng dẫn Chạy Local Test - Digital Signature Website

## 1️⃣ **Chuẩn bị** (lần đầu)

```bash
# Cài dependencies
composer install
npm install

# Copy cấu hình
copy .env.example .env

# Generate APP_KEY
php artisan key:generate

# Tạo database SQLite (local)
type nul > database/database.sqlite

# Run migrations + seed data
php artisan migrate --seed
```

## 2️⃣ **Chạy Development Server**

### **Option A: Dùng script Composer (RECOMMENDED)**
```bash
composer run dev
```
Lệnh này tự động khởi động:
- ✅ Laravel server (port 8000)
- ✅ Queue listener
- ✅ Logs (Pail)
- ✅ Vite dev server

### **Option B: Chạy từng bộ phận riêng**

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```
👉 Truy cập: http://127.0.0.1:8000

**Terminal 2 - Vite Build:**
```bash
npm run dev
```
👉 Tự động rebuild khi thay đổi CSS/JS

**Terminal 3 - Queue (optional):**
```bash
php artisan queue:listen
```
*Chỉ cần nếu gửi email*

## 3️⃣ **Test Các Tính Năng**

### Frontend:
- 🏠 Home: http://127.0.0.1:8000
- 📰 Blog: http://127.0.0.1:8000/dien-dan
- 💬 Contact: http://127.0.0.1:8000/lien-he

### Admin Dashboard (sau khi tạo tài khoản admin):
```bash
php artisan tinker
# Chạy lệnh này rồi tạo admin:
>>> App\Models\User::create([
  'name' => 'Admin',
  'email' => 'admin@test.local',
  'password' => bcrypt('password123'),
  'email_verified_at' => now(),
  'role' => 'admin',
  'status' => 'active',
]);
```

Đăng nhập admin: http://127.0.0.1:8000/admin/dashboard

## 4️⃣ **Cấu hình Gmail SMTP (tuỳ chọn)**

Để test gửi email, thêm vào `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_ADMIN_ADDRESS=admin@test.local
```

## 5️⃣ **Build Production (Trước khi Deploy)**

```bash
# Build assets
npm run build

# Clear cache (tùy chọn)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 6️⃣ **Reset Database**

```bash
# Xóa data + re-migrate
php artisan migrate:fresh --seed

# Hoặc chỉ rollback
php artisan migrate:rollback
```

## 📝 **Cheat Sheet Commands**

| Lệnh | Mục đích |
|------|---------|
| `php artisan serve` | Chạy Laravel server |
| `npm run dev` | Chạy Vite dev server |
| `composer run dev` | Chạy tất cả (recommended) |
| `php artisan migrate --seed` | Tạo tables + demo data |
| `php artisan migrate:fresh --seed` | Reset DB toàn bộ |
| `php artisan tinker` | Interactive shell |
| `php artisan storage:link` | Tạo symlink public/storage |

## ⚠️ **Troubleshoot**

**Lỗi "SQLSTATE[HY000]: General error"**
```bash
php artisan migrate:fresh --seed
```

**Vite hot reload không hoạt động**
```bash
npm run dev
# Kiểm tra http://localhost:5173 tồn tại
```

**Port 8000 đã được dùng**
```bash
php artisan serve --port=8001
```

Vậy là sẵn sàng test trước khi push lên Render! 🚀
