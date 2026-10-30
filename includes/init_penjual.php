<?php
/**
 * Dipanggil di awal setiap halaman penjual (setelah $page_title & $tab_aktif diisi).
 * Memeriksa peran, memuat lapak milik penjual, lalu mencetak header + tab.
 * Menyediakan: $lapak (array lapak milik penjual).
 */
require_once __DIR__ . '/functions.php';
wajib_peran('penjual');
bersihkan_kedaluwarsa($pdo);

$lapak = lapak_saya($pdo);

$tabs = [
    ['key' => 'stok',      'label' => 'Stok Menu',         'href' => 'api/penjual/stok.php'],
    ['key' => 'kasir',     'label' => 'Kasir',          'href' => 'api/penjual/kasir.php'],
    ['key' => 'pesanan',   'label' => 'Pesanan', 'href' => 'api/penjual/pesanan.php'],
    ['key' => 'riwayat',   'label' => 'Riwayat',        'href' => 'api/penjual/riwayat.php'],
    ['key' => 'pendapatan', 'label' => 'Pendapatan',    'href' => 'api/penjual/pendapatan.php'],
    ['key' => 'profil',    'label' => 'Profil Lapak',   'href' => 'api/penjual/profil.php'],
];
$judul_header = $lapak['nama_lapak'] ?? 'Lapak belum terdaftar';
$lapak_header = $lapak ?: ['nama_lapak' => '?'];

// Banner status sewa (jika tidak aktif, pembeli tidak bisa melihat lapak ini)
$sewaInfo = null;
if ($lapak) {
    $hariIni = (new DateTime('today', new DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
    if ($lapak['status_toko'] !== 'buka') {
        $sewaInfo = 'Toko Anda sedang ditutup, sehingga tidak tampil bagi pembeli. Buka kembali di tab Stok.';
    } elseif ($lapak['status_sewa'] !== 'aktif') {
        $sewaInfo = 'Lapak Anda sedang dinonaktifkan oleh admin, sehingga tidak tampil bagi pembeli.';
    } elseif ($lapak['sewa_berakhir'] < $hariIni) {
        $sewaInfo = 'Masa sewa lapak sudah berakhir (' . $lapak['sewa_berakhir'] . '). Hubungi admin untuk perpanjangan.';
    }
}

include __DIR__ . '/header.php';

if (!$lapak) {
    echo '<p class="flash flash-error" role="status">Akun Anda belum terhubung ke lapak mana pun. Hubungi admin.</p>';
    include __DIR__ . '/footer.php';
    exit;
}
if ($sewaInfo) {
    echo '<p class="flash flash-info" role="status">' . e($sewaInfo) . '</p>';
}
