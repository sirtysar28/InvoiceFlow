<?php

/**
 * InvoiceFlow - cPanel Entry Point
 * Path: public_html/demo/invoiceflow.sulthanwijaya.com/index.php
 */

define('LARAVEL_START', microtime(true));

// === PAKE ABSOLUTE PATH (lebih aman) ===
// Cek di File Manager cPanel bagian kiri bawah: "Home Directory: /home/u1574315"
// Pastikan folder invoiceflow ada di /home/u1574315/invoiceflow/
$appPath = '/home/u1574315/invoiceflow';

// === JIKA FOLDER APLIKASI DI TEMPAT LAIN, UBAH DI SINI ===
// Contoh:
// $appPath = '/home/u1574315/invoiceflow-app';
// $appPath = '/home/u1574315/laravel/invoiceflow';

// Debug: cek apakah path benar (hapus setelah beres)
if (!file_exists($appPath . '/vendor/autoload.php')) {
    die('❌ ERROR: Folder aplikasi tidak ditemukan di: ' . $appPath . 
        '<br>Pastikan folder invoiceflow ada di ' . $appPath . 
        '<br>Cek via File Manager cPanel dan edit file index.php ini.');
}

// Maintenance mode
if (file_exists($maintenance = $appPath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Load Laravel
require $appPath . '/vendor/autoload.php';
$app = require_once $appPath . '/bootstrap/app.php';

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
