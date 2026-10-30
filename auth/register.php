<?php
require_once __DIR__ . '/../includes/functions.php';

if (sudah_login()) {
    redirect(beranda_peran((string) $_SESSION['role']));
}

$page_title = 'Buat Akun';
$mode = 'auth';
include __DIR__ . '/../includes/header.php';
?>
<section class="auth-card">
    <div class="auth-icon" aria-hidden="true"></div>

    <h1>Buat Akun Baru</h1>
    <p class="auth-description">Daftar untuk menikmati layanan <?= e(APP_NAME) ?>.</p>

    <?php if ($flash): ?>
        <p class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['pesan']) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= e(url('auth/proses_register.php')) ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Buat username" required minlength="3" maxlength="30"
                   pattern="[A-Za-z0-9_.]+" autocomplete="username">
            <span class="hint">3–30 karakter: huruf, angka, titik, atau garis bawah.</span>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="nama@email.com" required maxlength="255" autocomplete="email">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password">
        </div>

        <div class="form-group">
            <label for="konfirmasi">Konfirmasi Password</label>
            <input type="password" id="konfirmasi" name="konfirmasi" placeholder="Ulangi password" required minlength="8"
                   autocomplete="new-password" data-sama-dengan="#password">
        </div>

        <button type="submit" class="auth-submit">Daftar Sekarang</button>
    </form>

    <div class="auth-divider"><span>atau</span></div>

    <a href="<?= e(url('api/pembeli/lapak.php')) ?>" class="auth-guest">Lihat Menu sebagai Tamu</a>

    <p class="auth-register">
        Sudah memiliki akun?
        <a href="<?= e(url('auth/login.php')) ?>">Masuk di sini</a>
    </p>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
