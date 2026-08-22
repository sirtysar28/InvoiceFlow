# 🚀 Panduan Deploy InvoiceFlow ke cPanel (Subdomain)

Panduan spesifik untuk deploy ke subdomain `invoiceflow.sulthanwijaya.com` di path `public_html/demo/invoiceflow.sulthanwijaya.com/`

---

## 📁 Struktur Folder di cPanel

```
/home/username/
├── public_html/
│   └── demo/
│       └── invoiceflow.sulthanwijaya.com/    ← WEB ROOT subdomain
│           ├── index.php                      ← entry point
│           ├── .htaccess                      ← URL rewriting
│           ├── css/                           ← asset Laravel
│           ├── build/                         ← asset Vite (jika ada)
│           └── storage/                       ← symlink ke app storage
│
└── invoiceflow/                               ← APLIKASI LARAVEL (diluar web root)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── artisan
    ├── .env                                   ← WAJIB diedit!
    └── composer.json
```

> **PENTING:** Aplikasi Laravel (`invoiceflow/`) ditaruh **di luar** `public_html/` supaya file sensitif tidak bisa diakses publik!

---

## 🔧 CARA 1: Deploy via SSH (Direkomendasikan)

### Step 1: Upload file ke cPanel

Dari komputer lokal:

```bash
cd "/Users/permatabank/Desktop/tysar/dokumen penting/a Project/Aplikasi Invoice Flow"

# Siapkan zip aplikasi
zip -r invoiceflow-app.zip . \
    -x "public/*" "vendor/*" "node_modules/*" ".git/*" \
    -x ".env" ".DS_Store" "cpanel-deploy/*" "cpanel-upload/*" \
    -x "storage/framework/cache/*" "storage/framework/views/*" \
    -x "storage/framework/sessions/*" "storage/logs/*"

# Siapkan zip public (isi folder public/)
cd public && zip -r ../invoiceflow-public.zip . -x ".DS_Store" && cd ..

# Upload kedua file .zip ke cPanel (via File Manager / FTP)
# Taruh di /home/username/
```

### Step 2: SSH ke cPanel

```bash
ssh username@sulthanwijaya.com
# atau
ssh username@server-ip
```

### Step 3: Ekstrak file

```bash
cd ~

# Ekstrak aplikasi
mkdir -p ~/invoiceflow
unzip -o invoiceflow-app.zip -d ~/invoiceflow

# Ekstrak public files
mkdir -p ~/public_html/demo/invoiceflow.sulthanwijaya.com
unzip -o invoiceflow-public.zip -d ~/public_html/demo/invoiceflow.sulthanwijaya.com

# Copy file cpanel-deploy
cp ~/invoiceflow/cpanel-deploy/.htaccess ~/public_html/demo/invoiceflow.sulthanwijaya.com/.htaccess
cp ~/invoiceflow/cpanel-deploy/index.php ~/public_html/demo/invoiceflow.sulthanwijaya.com/index.php

# Hapus folder public dari aplikasi (tidak perlu)
rm -rf ~/invoiceflow/public
```

### Step 4: Install dependencies

```bash
cd ~/invoiceflow

# Cara 1: Jika composer tersedia
php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader

# Cara 2: Jika tidak ada composer, upload folder vendor/ dari lokal
# (zip vendor/ di lokal, upload, extract di ~/invoiceflow/)
```

### Step 5: Setup .env

```bash
cd ~/invoiceflow
cp .env.cpanel .env
nano .env
```

**Isi .env (yang wajib disesuaikan):**

```env
APP_NAME=InvoiceFlow
APP_ENV=production
APP_KEY=                    # dikosongkan, nanti di-generate
APP_DEBUG=false
APP_URL=https://invoiceflow.sulthanwijaya.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=namadatabase_anda       # ← ganti
DB_USERNAME=userdatabase_anda       # ← ganti
DB_PASSWORD=passworddatabase_anda   # ← ganti

SESSION_DOMAIN=.sulthanwijaya.com
SANCTUM_STATEFUL_DOMAINS=invoiceflow.sulthanwijaya.com

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email-anda@gmail.com     # ← ganti
MAIL_PASSWORD=app-password-google      # ← ganti (App Password!)
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=email-anda@gmail.com
MAIL_FROM_NAME="InvoiceFlow"

WHATSAPP_API_KEY=                      # ← isi jika pakai WA
```

### Step 6: Permission

```bash
cd ~/invoiceflow

# File: 644, Folder: 755
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Folder writable: 775
chmod -R 775 storage bootstrap/cache
chmod +x artisan

# Public folder
cd ~/public_html/demo/invoiceflow.sulthanwijaya.com
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
```

### Step 7: Setup Laravel

```bash
cd ~/invoiceflow

# Generate APP_KEY
php artisan key:generate --force

# Storage symlink (ke folder public subdomain)
ln -sf ~/invoiceflow/storage/app/public ~/public_html/demo/invoiceflow.sulthanwijaya.com/storage

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 8: Setup Database di cPanel

1. Buka **cPanel → MySQL® Databases**
2. Buat database baru → catat nama DB, user, password
3. Assign user ke database (ALL PRIVILEGES)

Lalu jalankan migrasi:

```bash
cd ~/invoiceflow
php artisan migrate --seed --force
```

### Step 9: Cron Job Notifikasi

1. Buka **cPanel → Cron Jobs**
2. Tambahkan:

```
Minute: *
Command: cd /home/namauser/invoiceflow && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1
```

> 🔍 Cek path home: di cPanel bagian kiri bawah ada info "Home Directory"

### Step 10: Testing

Buka browser: **https://invoiceflow.sulthanwijaya.com**

Akun demo:
- Email: `admin@invoiceflow.com`
- Password: `password`

---

## 📂 CARA 2: Upload Manual via File Manager

### Step 1: Siapkan di lokal

Jalankan script:
```bash
bash prepare-cpanel.sh
```

Atau manual bikin 2 zip:
1. **zip_app.zip** — semua file kecuali `public/`, `vendor/`
2. **zip_public.zip** — isi folder `public/`

### Step 2: Upload via File Manager cPanel

1. Buka **cPanel → File Manager**
2. Navigasi ke `/home/username/`
3. Buat folder `invoiceflow/`
4. Upload `zip_app.zip` ke `invoiceflow/` → Extract
5. Navigasi ke `public_html/demo/`
6. Buat folder `invoiceflow.sulthanwijaya.com/`
7. Upload `zip_public.zip` ke folder tersebut → Extract
8. Upload `cpanel-deploy/.htaccess` ke `public_html/demo/invoiceflow.sulthanwijaya.com/`
9. Upload `cpanel-deploy/index.php` ke `public_html/demo/invoiceflow.sulthanwijaya.com/`

### Step 3: Setup via File Manager

1. **File Manager** → edit `invoiceflow/.env` (copy dari `.env.cpanel`)
2. **MySQL Databases** → buat database
3. **File Manager** → edit `invoiceflow/.env` isi DB credentials

### Step 4: Jalankan migrasi

Jika **Terminal** tersedia di cPanel:
```bash
cd ~/invoiceflow && php artisan migrate --seed --force
```

Jika **tidak ada Terminal**, buat file `migrate.php` sementara (lihat bawah).

---

## 🆘 Jika Tidak Ada Akses SSH / Terminal

Buat file `migrate.php` di `public_html/demo/invoiceflow.sulthanwijaya.com/`:

```php
<?php
/**
 * INVOICEFLOW MIGRATION HELPER
 * Jalankan: https://invoiceflow.sulthanwijaya.com/migrate.php
 * 
 * ⚠️ HAPUS FILE INI SETELAH SELESAI!
 */

// Sesuaikan path ke folder aplikasi
$appPath = __DIR__ . '/../../../invoiceflow';

require $appPath . '/vendor/autoload.php';
$app = require $appPath . '/bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo '<pre>';
echo "Generate APP_KEY...\n";
\Artisan::call('key:generate', ['--force' => true]);
echo \Artisan::output();

echo "\nMigrasi database...\n";
\Artisan::call('migrate', ['--force' => true, '--seed' => true]);
echo \Artisan::output();

echo "\nStorage Link...\n";
\Artisan::call('storage:link', ['--force' => true]);
echo \Artisan::output();

echo "\nCache config...\n";
\Artisan::call('config:cache');
echo \Artisan::output();

echo "\n=== SELESAI! ===\n";
echo "HAPUS FILE INI SEKARANG!\n";
echo '</pre>';
```

Buka `https://invoiceflow.sulthanwijaya.com/migrate.php` → **HAPUS FILE setelah selesai!**

---

## 🔍 Verifikasi path index.php

File `index.php` di `public_html/demo/invoiceflow.sulthanwijaya.com/` harus berisi path yang benar ke folder aplikasi:

```php
// Path relatif (naik 3 level dari web root ke home, lalu ke invoiceflow/)
$appPath = __DIR__ . '/../../../invoiceflow';

// ATAU path absolute (lebih aman):
// $appPath = '/home/namauser/invoiceflow';
```

Cek di File Manager cPanel path home Anda, sesuaikan `namauser`.

---

## ⚠️ Troubleshooting Subdomain

### 1. "404 Not Found" atau "No input file specified"
- Cek `.htaccess` ada di folder web root
- Cek module `mod_rewrite` aktif (tanya support hosting)
- Coba akses langsung: `https://invoiceflow.sulthanwijaya.com/index.php`

### 2. Halaman putih / error 500
- Cek `~/invoiceflow/storage/logs/laravel.log`
- Pastikan folder `storage/` permission 775
- Cek versi PHP 8.2 (cPanel → Select PHP Version)

### 3. CSS / JS tidak muncul
- Cek APP_URL di .env: `https://invoiceflow.sulthanwijaya.com`
- Jalankan `php artisan config:cache` ulang
- Pastikan symlink `storage` ada di folder web root

### 4. Error "The environment file is invalid"
- Path DB_DATABASE di .env jangan pakai spasi
- Hapus karakter aneh / BOM di file .env

### 5. Session tidak berfungsi (login gagal)
- Pastikan SESSION_DOMAIN di .env: `.sulthanwijaya.com` (pakai titik di depan!)
- Clear cache: `php artisan config:clear && php artisan cache:clear`

### 6. Storage symlink error
```bash
# Cara manual:
ln -sf /home/username/invoiceflow/storage/app/public /home/username/public_html/demo/invoiceflow.sulthanwijaya.com/storage
```

### 7. Cek path di cPanel

Di cPanel bagian kiri bawah ada info:
```
Home Directory: /home/namauser
```
Catat `namauser` ini, gunakan di semua path.

---

## ✅ Checklist Pasca Deploy

- [ ] `https://invoiceflow.sulthanwijaya.com` bisa diakses
- [ ] Login berhasil (admin@invoiceflow.com / password)
- [ ] Dashboard menampilkan data
- [ ] Buat invoice baru → simpan → muncul di list
- [ ] Download PDF berhasil
- [ ] Cron job sudah ditambahkan
- [ ] **GANTI PASSWORD** admin demo!
- [ ] Hapus file `migrate.php` jika ada
- [ ] `APP_DEBUG=false` di .env

---

## 📞 Butuh bantuan?

Cek log error:
```bash
cat ~/invoiceflow/storage/logs/laravel-$(date +%Y-%m-%d).log
```
