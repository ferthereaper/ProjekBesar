<?php
require_once __DIR__ . '/../includes/functions.php';

if (sudah_login()) {
    redirect(beranda_peran((string) $_SESSION['role']));
}

$page_title = 'Login';
$mode = 'auth';
include __DIR__ . '/../includes/header.php';
?>
<section class="auth-card">
    <div class="auth-icon" aria-hidden="true"></div>

    <h1>Selamat Datang!</h1>
    <p class="auth-description">
        Masuk ke akunmu untuk mengakses layanan <?= e(APP_NAME) ?>.
    </p>

    <?php if ($flash): ?>
        <p class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['pesan']) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= e(url('auth/proses_login.php')) ?>" autocomplete="on">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Masukkan username"
                   required autocomplete="username" maxlength="50">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="password-wrapper">
                <input type="password" id="password" name="password" placeholder="Masukkan password"
                       required autocomplete="current-password">
                <button type="button" class="password-toggle" data-toggle-password="#password" aria-label="Tampilkan password">
                    <i class="ic ic-eye" aria-hidden="true"></i>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="sebagai">Masuk sebagai</label>
            <select id="sebagai" name="sebagai">
                <option value="pembeli">Pembeli</option>
                <option value="penjual">Penjual / Kasir</option>
                <option value="admin">Admin</option>
            </select>
        </div>

        <button type="submit" class="auth-submit">Masuk</button>
    </form>

    <div class="auth-divider"><span>atau</span></div>

    <a href="<?= e(url('api/pembeli/lapak.php')) ?>" class="auth-guest">Jelajahi Menu sebagai Tamu</a>

    <p class="auth-register">
        Belum memiliki akun?
        <a href="<?= e(url('auth/register.php')) ?>">Daftar sekarang</a>
    </p>
</section>

<div class="auth-information">
    <div><span>Pesan praktis melalui QR Code</span></div>
    <div><span>Beragam menu dalam satu tempat</span></div>
    <div><span>Pembayaran QRIS untuk pesanan web</span></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
