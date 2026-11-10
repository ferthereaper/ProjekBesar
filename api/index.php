<?php
require_once __DIR__ . '/../includes/functions.php';

// Sudah login -> langsung ke beranda sesuai peran
if (sudah_login()) {
    redirect(beranda_peran((string) $_SESSION['role']));
}

$page_title = 'Selamat Datang';
$mode = 'welcome';
include __DIR__ . '/../includes/header.php';
?>
<section class="hero">
    <div class="hero-content">
        <h1><?= e(APP_NAME) ?></h1>
        <p class="hero-description">Pesan makanan dari lapak favoritmu dengan mudah.</p>

        <div class="hero-buttons">
            <a href="<?= e(url('auth/login.php')) ?>" class="btn btn-primary">Masuk</a>
            <a href="<?= e(url('api/pembeli/lapak.php')) ?>" class="btn btn-outline">Lihat Menu sebagai Tamu</a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
