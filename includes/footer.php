<?php
/** Menutup <main> yang dibuka header.php, lalu mencetak footer sesuai $mode. */
$mode = $mode ?? 'app';
?>
    </main>

<?php if ($mode === 'welcome'): ?>
    <footer class="copy">
        <p><?= e(ALAMAT_SINGKAT) ?></p>
        <p><?= e(JAM_BUKA) ?> · <?= e(KONTAK_WA) ?> · <?= e(KONTAK_EMAIL) ?></p>
        <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?></p>
    </footer>
<?php elseif ($mode === 'auth'): ?>
    <footer class="auth-footer">
        <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Semua hak dilindungi.</p>
        <p><?= e(ALAMAT_SINGKAT) ?></p>
    </footer>
<?php else: ?>
    <footer class="copy">&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. <?= e(ALAMAT_SINGKAT) ?>.</footer>
<?php endif; ?>

    <script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
