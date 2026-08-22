#!/bin/bash
# ============================================
# InvoiceFlow - Persiapan Upload ke cPanel
# Jalankan di LOKAL (komputer Anda)
# ============================================

echo "========================================"
echo "  InvoiceFlow - Persiapan cPanel"
echo "========================================"
echo ""

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
OUTPUT_DIR="${PROJECT_DIR}/cpanel-upload"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)

cd "${PROJECT_DIR}"

echo "[1/4] Membersihkan cache lokal..."
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/views/*
rm -rf storage/framework/sessions/*
rm -rf storage/logs/*
rm -rf bootstrap/cache/*.php

echo "[2/4] Membuat folder output..."
rm -rf "${OUTPUT_DIR}"
mkdir -p "${OUTPUT_DIR}"

echo "[3/4] Membuat zip_app.zip (aplikasi Laravel)..."
# Semua file kecuali public/, vendor/ opsional, dan file gak penting
zip -r "${OUTPUT_DIR}/zip_app.zip" . \
    -x "public/*" \
    -x "vendor/*" \
    -x "node_modules/*" \
    -x ".git/*" \
    -x ".env" \
    -x ".DS_Store" \
    -x "*.log" \
    -x "cpanel-deploy/*" \
    -x "cpanel-upload/*" \
    -x "storage/framework/cache/*" \
    -x "storage/framework/views/*" \
    -x "storage/framework/sessions/*" \
    -x "storage/logs/*" \
    -x "storage/debugbar/*"

echo "[4/4] Membuat zip_public.zip (file public)..."
cd public
zip -r "${OUTPUT_DIR}/zip_public.zip" . -x ".DS_Store"
cd ..

echo ""
echo "========================================"
echo "  ✅ FILE SIAP DIUPLOAD"
echo "========================================"
echo ""
echo "  File yang harus diupload ke cPanel:"
echo ""
echo "  1. ${OUTPUT_DIR}/zip_app.zip"
echo "     → Upload ke cPanel, extract ke /home/username/invoiceflow/"
echo ""
echo "  2. ${OUTPUT_DIR}/zip_public.zip"
echo "     → Upload ke cPanel, extract ke:"
echo "     /home/username/public_html/demo/invoiceflow.sulthanwijaya.com/"
echo ""
echo "  ⚠️  JIKA TIDAK BISA INSTALL COMPOSER DI CPANEL:"
echo "     Upload juga folder vendor/ (zip manual)"
echo "     cd ${PROJECT_DIR}"
echo "     zip -r ${OUTPUT_DIR}/zip_vendor.zip vendor/ -x \".DS_Store\""
echo ""
echo "  📖 Baca panduan lengkap: cpanel-deploy/PANDUAN-DEPLOY-CPANEL.md"
echo ""
