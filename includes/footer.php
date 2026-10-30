<?php
/** Menutup <main> yang dibuka header.php, lalu mencetak footer sesuai $mode. */
$mode = $mode ?? 'app';
?>
    </main>

<?php if ($mode === 'welcome'): ?>
    <?php
    $alamatBaris = array_map('trim', explode(',', ALAMAT_SINGKAT));
    $jam = array_map('trim', explode('•', JAM_BUKA));
    ?>
    <footer class="footer" id="alamat">
        <div class="footer-container">
            <div class="footer-column">
                <div class="footer-heading"><h3>Alamat</h3></div>
                <p><?= implode(',<br>', array_map('e', $alamatBaris)) ?></p>
                <a href="https://www.google.com/maps/search/?api=1&amp;query=<?= e(urlencode(ALAMAT_SINGKAT)) ?>"
                   target="_blank" rel="noopener noreferrer" class="footer-link">
                    Lihat lokasi <i class="ic ic-arrow-up-right" aria-hidden="true"></i>
                </a>
            </div>

            <div class="footer-column">
                <div class="footer-heading"><h3>Jam Operasional</h3></div>
                <p><?= e($jam[0] ?? '') ?></p>
                <?php if (!empty($jam[1])): ?><p class="operating-hours"><?= e($jam[1]) ?></p><?php endif; ?>
                <span class="status-label"><span class="status-dot"></span> Melayani setiap hari</span>
            </div>

            <div class="footer-column">
                <div class="footer-heading"><h3>Hubungi Kami</h3></div>
                <p>💬 <?= e(KONTAK_WA) ?></p>
                <p>☎ <?= e(KONTAK_TELP) ?></p>
                <p>✉ <?= e(KONTAK_EMAIL) ?></p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. All rights reserved.</p>
            <p class="footer-tagline">Made with <i class="ic ic-heart" aria-hidden="true"></i> in Malang</p>
        </div>
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
