<?php
/**
 * Variabel yang bisa diisi halaman sebelum include:
 *   $page_title     judul tab browser
 *   $mode           'app' (default, halaman dengan navigasi) | 'welcome' (beranda & kuliner) | 'auth' (login/daftar)
 *   $nav_aktif      mode welcome: 'beranda' | 'kuliner'
 *   $tabs           [['label'=>..,'href'=>..,'key'=>..,'count'=>..], ...] -> navigasi dashboard
 *                   (kosong untuk pembeli/guest -> dibuat otomatis: Daftar Lapak, Menu, Keranjang)
 *   $tab_aktif      key tab yang aktif
 *   $lapak_header   array lapak yang sedang dilihat (dipakai untuk tab "Menu" milik pembeli)
 */
require_once __DIR__ . '/functions.php';

$mode = $mode ?? 'app';
$flash = flash_ambil();
$login = sudah_login();
$role = peran(); // guest | pembeli | penjual | admin

// "Brawijaya Street Food" -> "Brawijaya" + "STREET FOOD"
$namaDepan = (string) strtok(APP_NAME, ' ');
$namaBelakang = mb_strtoupper(trim(substr(APP_NAME, strlen($namaDepan))));

// Tab otomatis untuk pembeli / guest
if ($mode === 'app' && empty($tabs) && in_array($role, ['guest', 'pembeli'], true)) {
    $tabs = [['key' => 'lapak', 'label' => 'Daftar Lapak', 'href' => 'api/pembeli/lapak.php']];
    if (!empty($lapak_header['id'])) {
        $tabs[] = ['key' => 'menu', 'label' => 'Menu', 'href' => 'api/pembeli/menu.php?lapak=' . (int) $lapak_header['id']];
    }
    $tabs[] = ['key' => 'keranjang', 'label' => 'Keranjang', 'href' => 'api/pembeli/checkout.php', 'count' => cart_jumlah('cart')];
    if ($login) {
        $tabs[] = ['key' => 'riwayat', 'label' => 'Riwayat', 'href' => 'api/pembeli/riwayat.php'];
    }
}

$berandaUrl = url($login ? beranda_peran((string) $_SESSION['role']) : 'api/pembeli/lapak.php');
$navBeranda = url('api/index.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#17130f">
    <title><?= isset($page_title) ? e($page_title) . ' | ' : '' ?><?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
</head>
<body<?= $mode === 'auth' ? ' class="auth-body"' : '' ?>>
<?php if ($mode === 'welcome'): ?>
    <header class="navbar">
        <a href="<?= e($navBeranda) ?>" class="brand">
            <span class="brand-icon" aria-hidden="true">♨</span>
            <span class="brand-text">
                <strong><?= e($namaDepan) ?></strong>
                <small><?= e($namaBelakang) ?></small>
            </span>
        </a>
        <nav class="nav-menu" aria-label="Navigasi utama">
            <a href="<?= e($navBeranda) ?>" class="nav-link<?= ($nav_aktif ?? 'beranda') === 'beranda' ? ' active' : '' ?>"><span class="icon">⌂</span> Beranda</a>
            <a href="<?= e(url('api/kuliner.php')) ?>" class="nav-link<?= ($nav_aktif ?? '') === 'kuliner' ? ' active' : '' ?>"><span class="icon">♨</span> Kuliner</a>
            <a href="<?= e($navBeranda) ?>#alamat" class="nav-link"><span class="icon">●</span> Lokasi</a>
            <a href="<?= e($navBeranda) ?>#tentang" class="nav-link"><span class="icon">ⓘ</span> Tentang</a>
        </nav>
    </header>
    <main>
    <?php if ($flash): ?>
        <div class="page-container" style="padding-bottom:0">
            <p class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['pesan']) ?></p>
        </div>
    <?php endif; ?>
<?php elseif ($mode === 'auth'): ?>
    <header class="bn auth-header">
        <a href="<?= e($navBeranda) ?>" class="bn-brand"><?= e(APP_NAME) ?></a>
        <a href="<?= e($navBeranda) ?>" class="bn-back"><i class="ic ic-arrow-left" aria-hidden="true"></i> Kembali</a>
    </header>
    <main class="auth-container">
<?php else: ?>
    <header class="bn">
        <a href="<?= e($berandaUrl) ?>" class="bn-brand"><?= e(APP_NAME) ?></a>
        <div class="bn-actions">
            <span class="user-chip"><?= e($login ? ($_SESSION['username'] ?? 'User') : 'Tamu') ?></span>
            <?php if ($login): ?>
                <form method="post" action="<?= e(url('auth/logout.php')) ?>" class="form-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-secondary">Keluar</button>
                </form>
            <?php else: ?>
                <a href="<?= e(url('auth/login.php')) ?>" class="btn-secondary">Masuk</a>
            <?php endif; ?>
        </div>
    </header>
    <?php if (!empty($tabs)): ?>
        <nav class="dashboard-nav" aria-label="Navigasi">
            <?php foreach ($tabs as $t): ?>
                <a href="<?= e(url($t['href'])) ?>"<?= ($tab_aktif ?? '') === $t['key'] ? ' class="active" aria-current="page"' : '' ?>>
                    <?= e($t['label']) ?>
                    <?php if (isset($t['count'])): ?><span class="count" data-cart-count><?= (int) $t['count'] ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
    <main class="page-container">
    <?php if ($flash): ?>
        <p class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['pesan']) ?></p>
    <?php endif; ?>
<?php endif; ?>
