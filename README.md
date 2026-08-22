# 🧾 InvoiceFlow

Aplikasi manajemen invoice modern berbasis Laravel. Buat, kirim, dan pantau invoice dengan mudah. Dilengkapi notifikasi otomatis via WhatsApp & Email.

---

## 🚀 Fitur Utama

### 📄 Manajemen Invoice (CRUD)
- Buat invoice dengan form sederhana
- Edit invoice sebelum/sesudah dikirim
- Soft delete / hard delete
- Daftar invoice dengan filter: semua, belum dibayar, lunas, jatuh tempo, lewat tempo
- Tandai lunas dengan satu klik

### 🔔 Notifikasi Jatuh Tempo (Otomatis)
- Pengaturan waktu notifikasi (H-3, H-1, H-0, dll)
- Notifikasi WhatsApp ke nomor Anda
- Notifikasi Email
- Cegah duplikasi notifikasi

### 📤 Kirim Invoice ke Klien
- Tombol kirim via WhatsApp (buka WhatsApp Web)
- Kirim via Email dengan pop-up editor
- Template pesan kustom

### ⚙️ Pengaturan Akun
- Profil bisnis (nama, logo, alamat, rekening)
- Pengaturan channel notifikasi
- Koneksi WhatsApp Gateway API (Fonnte, WAPlus, dll)

---

## 📋 Persyaratan

- PHP 8.2+
- MySQL / MariaDB
- Composer
- Node.js & NPM (opsional)

---

## 🛠️ Instalasi

```bash
# 1. Clone / masuk ke direktori project
cd "Aplikasi Invoice Flow"

# 2. Install dependencies
composer install

# 3. Copy .env
cp .env.example .env

# 4. Generate APP_KEY
php artisan key:generate

# 5. Setting database di .env
# DB_DATABASE=invoiceflow
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Jalankan migrasi + seeder
php artisan migrate --seed

# 7. Storage link
php artisan storage:link

# 8. Jalankan server
php artisan serve
```

Buka `http://localhost:8000` di browser.

---

## 👤 Akun Demo

- **Email:** admin@invoiceflow.com
- **Password:** password

---

## 📱 Konfigurasi WhatsApp Gateway

1. Daftar di penyedia gateway (Fonnte, WAPlus, dll)
2. Dapatkan API Key
3. Tambahkan di file `.env`:

```
WHATSAPP_API_KEY=your_api_key_here
WHATSAPP_API_URL=https://api.fonnte.com/send
```

---

## ⏰ Menjalankan Notifikasi Otomatis

Tambahkan cron job di server Anda untuk menjalankan scheduler Laravel setiap menit:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

Atau jalankan manual untuk testing:

```bash
php artisan invoiceflow:send-due-reminders
```

---

## 📁 Struktur Direktori Penting

```
├── app/
│   ├── Console/Commands/SendDueDateNotifications.php   # Command notifikasi
│   ├── Http/Controllers/                               # Controllers
│   ├── Mail/                                           # Mailables
│   ├── Models/                                         # Models
│   └── Services/                                       # WhatsApp & Email services
├── database/migrations/                                # Migrations
├── resources/views/                                    # Blade views
└── routes/web.php                                      # Routes
```

---

## 🔑 Variabel di Template Pesan

| Variabel | Deskripsi |
|----------|-----------|
| `{no_invoice}` | Nomor invoice |
| `{nama_klien}` | Nama klien |
| `{total}` | Total tagihan |
| `{tanggal_jatuh_tempo}` | Tanggal jatuh tempo |
| `{link_invoice}` | Link ke halaman invoice |

---

## 🚀 Deploy ke cPanel

Lihat panduan lengkap di:

```bash
cat cpanel-deploy/PANDUAN-DEPLOY-CPANEL.md
```

### Persiapan Upload (lokal)

```bash
# 1. Jalankan script persiapan
bash prepare-cpanel.sh

# 2. Upload file yang dihasilkan di folder cpanel-upload/ ke cPanel
```

### Di Server cPanel

```bash
# 1. SSH ke cPanel
ssh username@domain-anda.com

# 2. Jalankan script deploy
cd ~/invoiceflow
bash cpanel-deploy/deploy.sh

# 3. Jangan lupa edit .env dan setup database!
```

> 📖 **Panduan lengkap step-by-step:** `cpanel-deploy/PANDUAN-DEPLOY-CPANEL.md`

---

Dibuat dengan ❤️ menggunakan Laravel 10 + Bootstrap 5
# InvoiceFlow
