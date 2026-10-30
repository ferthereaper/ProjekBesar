<?php
require_once __DIR__ . '/../includes/functions.php';

// Halaman informasi: terbuka untuk semua pengunjung. Hanya lapak yang sedang buka yang ditampilkan.
$daftar = $pdo->query(
    'SELECT l.id, l.nama_lapak, l.kategori, l.punya_logo FROM ' . SQL_LAPAK . ' l WHERE ' . sql_lapak_aktif('l') . ' ORDER BY l.nama_lapak'
)->fetchAll();

$menuPerLapak = [];
if ($daftar) {
    $in = implode(',', array_map('intval', array_column($daftar, 'id')));
    foreach ($pdo->query('SELECT lapak_id, nama_produk FROM ' . SQL_MENU . " m WHERE lapak_id IN ($in) ORDER BY nama_produk") as $r) {
        $menuPerLapak[(int) $r['lapak_id']][] = $r['nama_produk'];
    }
}
$bisaPesan = in_array(peran(), ['guest', 'pembeli'], true);

$page_title = 'Kuliner';
$mode = 'welcome';
$nav_aktif = 'kuliner';
include __DIR__ . '/../includes/header.php';
?>
<section class="kuliner-hero">
    <h1>Kuliner Kami</h1>
    <p>Kenali lapak-lapak di kawasan <?= e(APP_NAME) ?> dan apa saja yang mereka jual.</p>
</section>

<div class="page-container">
    <?php if (!$daftar): ?>
        <p class="empty">Belum ada lapak yang buka saat ini.</p>
    <?php else: ?>
        <section class="kuliner-grid">
            <?php foreach ($daftar as $l): $menu = $menuPerLapak[(int) $l['id']] ?? []; ?>
                <article class="kuliner-card">
                    <div class="kuliner-head">
                        <div class="stall-avatar" aria-hidden="true"><?= avatar_lapak($l) ?></div>
                        <div>
                            <h2><?= e($l['nama_lapak']) ?></h2>
                            <span class="badge badge-warning"><?= e($l['kategori']) ?></span>
                        </div>
                    </div>
                    <p><?= $menu ? 'Menyajikan ' . count($menu) . ' pilihan menu.' : 'Menu segera hadir.' ?></p>
                    <?php if ($menu): ?>
                        <ul class="kuliner-tags" aria-label="Menu andalan">
                            <?php foreach (array_slice($menu, 0, 4) as $nama): ?>
                                <li><?= e($nama) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if ($bisaPesan): ?>
                        <a class="btn-secondary" href="<?= e(url('api/pembeli/menu.php?lapak=' . (int) $l['id'])) ?>">Lihat Menu</a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
