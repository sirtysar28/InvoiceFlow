#!/bin/bash
# ============================================
# InvoiceFlow - cPanel Deploy Script (Subdomain)
# 
# Struktur:
# /home/username/
# ├── public_html/demo/invoiceflow.sulthanwijaya.com/  ← web root
# └── invoiceflow/                                      ← aplikasi Laravel
#
# Jalankan via SSH:  bash deploy-subdomain.sh
# ============================================

set -e

echo "========================================"
echo "  InvoiceFlow - Deploy Subdomain cPanel"
echo "  invoiceflow.sulthanwijaya.com"
echo "========================================"
echo ""

# === KONFIGURASI - SESUAIKAN! ===
APP_DIR="${HOME}/invoiceflow"
PUBLIC_DIR="${HOME}/public_html/demo/invoiceflow.sulthanwijaya.com"
PHP_BIN="/usr/local/bin/php"

echo "Struktur yang akan dibuat:"
echo "  App  : ${APP_DIR}/"
echo "  Web  : ${PUBLIC_DIR}/"
echo ""

read -p "Lanjutkan? (y/n) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    exit 1
fi

# === 1. Buat folder ===
echo "[1/9] Membuat folder..."
mkdir -p "${APP_DIR}"
mkdir -p "${PUBLIC_DIR}"
mkdir -p "${APP_DIR}/storage/framework/cache"
mkdir -p "${APP_DIR}/storage/framework/sessions"
mkdir -p "${APP_DIR}/storage/framework/views"
mkdir -p "${APP_DIR}/storage/logs"
mkdir -p "${APP_DIR}/storage/app/public"
mkdir -p "${APP_DIR}/bootstrap/cache"

# === 2. Copy aplikasi ===
echo "[2/9] Copy file aplikasi ke ${APP_DIR}..."
rsync -av --exclude='public' --exclude='.git' --exclude='node_modules' \
      --exclude='.DS_Store' --exclude='cpanel-deploy' \
      --exclude='cpanel-upload' --exclude='.env' \
      --exclude='README.md' --exclude='prepare-cpanel.sh' \
      ./ "${APP_DIR}/"

# === 3. Copy public files ===
echo "[3/9] Copy file public ke ${PUBLIC_DIR}..."
cp -r public/* "${PUBLIC_DIR}/" 2>/dev/null || true
cp cpanel-deploy/.htaccess "${PUBLIC_DIR}/.htaccess"
cp cpanel-deploy/index.php "${PUBLIC_DIR}/index.php"

# === 4. Setup .env ===
echo "[4/9] Setup .env..."
if [ ! -f "${APP_DIR}/.env" ]; then
    cp .env.cpanel "${APP_DIR}/.env"
    echo "  ✅ .env dibuat dari .env.cpanel"
else
    echo "  ⚠️  .env sudah ada, tidak ditimpa"
fi

# === 5. Permission ===
echo "[5/9] Setting permission..."
cd "${APP_DIR}"
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod +x artisan

cd "${PUBLIC_DIR}"
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# === 6. Composer ===
echo "[6/9] Install dependencies..."
cd "${APP_DIR}"
if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader 2>&1 | tail -5
elif [ -f /usr/local/bin/composer ]; then
    php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader 2>&1 | tail -5
else
    echo "  ⚠️  Composer tidak ditemukan!"
    echo "  Upload folder vendor/ manual dari lokal, atau install composer dulu."
fi

# === 7. Generate key ===
echo "[7/9] Generate APP_KEY..."
cd "${APP_DIR}"
php artisan key:generate --force

# === 8. Storage link ===
echo "[8/9] Storage link..."
cd "${APP_DIR}"
php artisan storage:link --force 2>/dev/null || {
    # Symlink di subdomain folder
    ln -sf "${APP_DIR}/storage/app/public" "${PUBLIC_DIR}/storage" 2>/dev/null || true
    echo "  Symlink manual dibuat"
}

# === 9. Optimize ===
echo "[9/9] Optimasi Laravel..."
cd "${APP_DIR}"
php artisan config:cache 2>/dev/null || echo "  ⚠️  config:cache skipped"
php artisan route:cache 2>/dev/null || echo "  ⚠️  route:cache skipped"
php artisan view:cache 2>/dev/null || echo "  ⚠️  view:cache skipped"

echo ""
echo "========================================"
echo "  ✅ DEPLOYMENT SELESAI!"
echo "========================================"
echo ""
echo "  ⚠️  LANGKAH SELANJUTNYA (WAJIB!):"
echo ""
echo "  1. Edit .env:"
echo "     nano ${APP_DIR}/.env"
echo ""
echo "     Pastikan isian berikut benar:"
echo "     - DB_DATABASE, DB_USERNAME, DB_PASSWORD"
echo "     - MAIL_USERNAME, MAIL_PASSWORD"
echo "     - WHATSAPP_API_KEY (jika pakai)"
echo ""
echo "  2. Buat database di cPanel → MySQL Databases"
echo "     Lalu jalankan migrasi:"
echo ""
echo "     cd ${APP_DIR}"
echo "     php artisan migrate --seed --force"
echo ""
echo "  3. Setup cron job di cPanel → Cron Jobs:"
echo ""
echo "     * * * * * cd ${APP_DIR} && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1"
echo ""
echo "  4. Buka browser:"
echo "     https://invoiceflow.sulthanwijaya.com"
echo ""
echo "  📖 Panduan lengkap: cat cpanel-deploy/PANDUAN-DEPLOY-CPANEL.md"
echo ""
