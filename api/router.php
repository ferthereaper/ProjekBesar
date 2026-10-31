<?php
/**
 * Pintu masuk tunggal untuk Vercel.
 * Semua URL (selain /assets dan /images, lihat vercel.json) masuk ke sini, lalu
 * diteruskan ke file PHP yang sesuai. Dengan begitu seluruh aplikasi hanya memakai
 * SATU serverless function (batas paket Hobby: 12) dan file di includes/ atau sql/
 * tidak bisa diakses langsung dari browser.
 */
$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
$path = '/' . trim($path, '/');
if ($path === '/') {
    $path = '/api/index.php';
}

// Hanya halaman di api/ dan auth/ yang boleh dijalankan; router sendiri dikecualikan
$aman = preg_match('#^/(api|auth)/[A-Za-z0-9_/]+\.php$#', $path) === 1 && $path !== '/api/router.php';
$file = dirname(__DIR__) . $path;
if (!$aman || !is_file($file)) {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Halaman tidak ditemukan.';
    exit;
}

$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $path;
$_SERVER['SCRIPT_FILENAME'] = $file;
chdir(dirname($file));
require $file;
