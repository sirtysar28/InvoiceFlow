<?php
/**
 * InvoiceFlow - Deployment Checker
 * 
 * Upload ke: public_html/demo/invoiceflow.sulthanwijaya.com/check.php
 * Buka: https://invoiceflow.sulthanwijaya.com/check.php
 * 
 * ⚠️ HAPUS SETELAH SELESAI!
 */

$appPath = __DIR__ . '/../../../invoiceflow';

echo "<h1>🔍 InvoiceFlow Deployment Check</h1>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#eee}h1{color:#818cf8}.ok{color:#22c55e}.fail{color:#ef4444}.warn{color:#f59e0b}pre{background:#16213e;padding:10px;border-radius:5px}</style>";

// 1. PHP Version
echo "<h3>1. PHP Version</h3>";
$phpVer = phpversion();
$minVer = '8.2.0';
echo "<p>" . (version_compare($phpVer, $minVer, '>=') ? "<span class='ok'>✅ PHP {$phpVer}</span>" : "<span class='fail'>❌ PHP {$phpVer} (min: {$minVer})</span>") . "</p>";

// 2. Required Extensions
echo "<h3>2. PHP Extensions</h3>";
$extensions = ['pdo', 'pdo_mysql', 'mbstring', 'xml', 'json', 'fileinfo', 'curl', 'openssl', 'tokenizer', 'ctype', 'bcmath'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "<p>" . ($loaded ? "<span class='ok'>✅ {$ext}</span>" : "<span class='fail'>❌ {$ext}</span>") . "</p>";
}

// 3. App Path
echo "<h3>3. Application Path</h3>";
echo "<p>App Path: <code>{$appPath}</code></p>";
if (file_exists($appPath . '/bootstrap/app.php')) {
    echo "<p class='ok'>✅ bootstrap/app.php found</p>";
} else {
    echo "<p class='fail'>❌ bootstrap/app.php NOT FOUND! Cek path di index.php</p>";
}
if (file_exists($appPath . '/vendor/autoload.php')) {
    echo "<p class='ok'>✅ vendor/autoload.php found</p>";
} else {
    echo "<p class='fail'>❌ vendor/autoload.php NOT FOUND! Jalankan composer install</p>";
}
if (file_exists($appPath . '/.env')) {
    echo "<p class='ok'>✅ .env found</p>";
} else {
    echo "<p class='fail'>❌ .env NOT FOUND! Copy dari .env.cpanel</p>";
}

// 4. Storage writable
echo "<h3>4. Folder Permissions</h3>";
$storagePath = $appPath . '/storage';
$cachePath = $appPath . '/bootstrap/cache';
echo "<p>" . (is_writable($storagePath) ? "<span class='ok'>✅ storage/ writable</span>" : "<span class='fail'>❌ storage/ NOT writable (chmod 775)</span>") . "</p>";
echo "<p>" . (is_writable($cachePath) ? "<span class='ok'>✅ bootstrap/cache/ writable</span>" : "<span class='fail'>❌ bootstrap/cache/ NOT writable (chmod 775)</span>") . "</p>";

// 5. Database Connection
echo "<h3>5. Database Connection</h3>";
try {
    require $appPath . '/vendor/autoload.php';
    $app = require $appPath . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    $dbConfig = config('database.connections.' . config('database.default'));
    echo "<p>Driver: <code>" . config('database.default') . "</code></p>";
    echo "<p>Database: <code>" . ($dbConfig['database'] ?? '?') . "</code></p>";
    
    try {
        \DB::connection()->getPdo();
        echo "<p class='ok'>✅ Database connected!</p>";
        echo "<p>Tables: ";
        $tables = \DB::select('SHOW TABLES');
        echo count($tables) . " table(s) found</p>";
    } catch (\Exception $e) {
        echo "<p class='fail'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    }
} catch (\Exception $e) {
    echo "<p class='fail'>❌ Cannot bootstrap app: " . $e->getMessage() . "</p>";
}

// 6. APP_KEY
echo "<h3>6. APP_KEY</h3>";
$appKey = $_ENV['APP_KEY'] ?? getenv('APP_KEY') ?? '';
if (!empty($appKey) && $appKey !== 'base64:') {
    echo "<p class='ok'>✅ APP_KEY is set</p>";
} else {
    echo "<p class='fail'>❌ APP_KEY is empty! Run: php artisan key:generate --force</p>";
}

// 7. APP_URL
echo "<h3>7. APP_URL</h3>";
$appUrl = $_ENV['APP_URL'] ?? getenv('APP_URL') ?? '';
echo "<p>APP_URL: <code>{$appUrl}</code></p>";
echo "<p>Current URL: <code>https://{$_SERVER['HTTP_HOST']}</code></p>";

// 8. .htaccess
echo "<h3>8. .htaccess</h3>";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "<p class='ok'>✅ .htaccess found</p>";
} else {
    echo "<p class='fail'>❌ .htaccess NOT FOUND!</p>";
}

// 9. mod_rewrite
echo "<h3>9. mod_rewrite</h3>";
if (isset($_SERVER['HTTP_MOD_REWRITE']) || (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()))) {
    echo "<p class='ok'>✅ mod_rewrite enabled</p>";
} else {
    echo "<p class='warn'>⚠️ Cannot verify mod_rewrite. Jika ada error 404, cek mod_rewrite.</p>";
}

echo "<hr>";
echo "<h2 class='ok'>Selesai! HAPUS FILE INI SEKARANG (check.php)</h2>";
echo "<p><a href='/' style='color:#818cf8;'>→ Buka Aplikasi</a></p>";
