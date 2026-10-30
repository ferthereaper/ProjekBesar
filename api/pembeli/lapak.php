<?php
require_once __DIR__ . '/../../includes/functions.php';
wajib_pembeli_atau_guest();
bersihkan_kedaluwarsa($pdo);

$daftar = $pdo->query(
    'SELECT l.id, l.nama_lapak, l.kategori, l.punya_logo FROM ' . SQL_LAPAK . ' l WHERE ' . sql_lapak_aktif('l') . ' ORDER BY l.nama_lapak'
)->fetchAll();

const TAMPIL_AWAL = 9;
$page_title = 'Daftar Lapak';
$tab_aktif = 'lapak';
include __DIR__ . '/../../includes/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Daftar Lapak</h1>
        <p>Pilih lapak favoritmu untuk melihat menu dan memesan.</p>
    </div>
</section>

<?php if (!$daftar): ?>
    <p class="empty">Belum ada lapak yang buka saat ini.</p>
<?php else: ?>
    <div class="toolbar">
        <div class="search-box">
            <i class="ic ic-search" aria-hidden="true"></i>
            <input type="search" placeholder="Cari nama atau kategori lapak..." data-filter-input aria-label="Cari lapak">
        </div>
    </div>

    <section class="stall-grid">
        <?php foreach ($daftar as $i => $l): $extra = $i >= TAMPIL_AWAL; ?>
            <article class="stall-card" data-filter-item<?= $extra ? ' data-extra hidden' : '' ?>>
                <div class="stall-avatar" aria-hidden="true"><?= avatar_lapak($l) ?></div>
                <div class="stall-info">
                    <h3><?= e($l['nama_lapak']) ?></h3>
                    <p><?= e($l['kategori']) ?></p>
                    <span class="badge badge-success">Buka</span>
                </div>
                <a href="<?= e(url('api/pembeli/menu.php?lapak=' . (int) $l['id'])) ?>" class="btn-primary">Lihat Menu</a>
            </article>
        <?php endforeach; ?>
    </section>

    <p class="empty" data-filter-empty hidden>Tidak ada lapak yang cocok dengan pencarianmu.</p>

    <?php if (count($daftar) > TAMPIL_AWAL): ?>
        <div class="more-row">
            <button type="button" class="btn-secondary" data-more data-state="closed">Tampilkan Lebih Banyak</button>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
