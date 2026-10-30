<?php
require_once __DIR__ . '/../includes/functions.php';

// Sudah login -> langsung ke beranda sesuai peran
if (sudah_login()) {
    redirect(beranda_peran((string) $_SESSION['role']));
}

$page_title = 'Selamat Datang';
$mode = 'welcome';
$nav_aktif = 'beranda';
include __DIR__ . '/../includes/header.php';
?>
<section class="hero" id="tentang">
    <div class="hero-content">
        <p class="welcome-label">
            <span class="ornament">✦</span>
            Selamat Datang
            <span class="ornament">✦</span>
        </p>

        <h1><?= e(APP_NAME) ?></h1>

        <p class="hero-description">
            Temukan cita rasa terbaik dan nikmati pengalaman kuliner yang istimewa bersama kami.
        </p>

        <p class="hero-address">
            <span class="address-icon">📍</span>
            <?= e(ALAMAT_LENGKAP) ?>
        </p>

        <div class="hero-buttons">
            <a href="<?= e(url('auth/login.php')) ?>" class="btn btn-primary">
                <span class="btn-icon">♟</span>
                LOGIN
                <span class="btn-arrow">→</span>
            </a>

            <a href="<?= e(url('api/pembeli/lapak.php')) ?>" class="btn btn-outline">
                <span class="btn-icon">♟</span>
                GUEST
                <span class="btn-arrow">→</span>
            </a>
        </div>

        <div class="hero-note">
            <p>Selamat menikmati pengalaman kuliner di <?= e(APP_NAME) ?>!</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
