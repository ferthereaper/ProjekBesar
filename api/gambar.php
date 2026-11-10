<?php
/**
 * Menampilkan gambar dari database:  api/gambar.php?jenis=menu|lapak&id=123
 * Publik (dilihat pembeli/tamu). Tidak memakai session agar ringan.
 */
require_once __DIR__ . '/../includes/koneksi.php';

// Daftar putih: nama tabel/kolom di bawah ini aman disisipkan ke SQL
$peta = [
    'menu'  => ['menu', 'id_menu', 'gambar', 'gambar_mime'],
    'lapak' => ['pedagang', 'id_pedagang', 'logo', 'logo_mime'],
];
$jenis = (string) ($_GET['jenis'] ?? '');
$id = (int) ($_GET['id'] ?? 0);
if (!isset($peta[$jenis]) || $id <= 0) {
    http_response_code(404);
    exit;
}
[$tabel, $pk, $kolom, $kolomMime] = $peta[$jenis];

$st = $pdo->prepare("SELECT md5($kolom) AS etag, $kolomMime AS mime FROM $tabel WHERE $pk = ? AND $kolom IS NOT NULL");
$st->execute([$id]);
$meta = $st->fetch();
if (!$meta) {
    http_response_code(404);
    exit;
}

$etag = '"' . $meta['etag'] . '"';
header('ETag: ' . $etag);
header('Cache-Control: public, no-cache'); // browser cek ETag dulu; 304 jika gambar belum berubah
if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
    http_response_code(304);
    exit;
}

$st = $pdo->prepare("SELECT $kolom FROM $tabel WHERE $pk = ?");
$st->execute([$id]);
$biner = $st->fetchColumn();
if (is_resource($biner)) {
    $biner = stream_get_contents($biner);
}
header('Content-Type: ' . $meta['mime']);
header('X-Content-Type-Options: nosniff');
header('Content-Length: ' . strlen((string) $biner));
echo $biner;
