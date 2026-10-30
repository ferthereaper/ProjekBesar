<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Menu Admin';
$tab_aktif = 'beranda';
require __DIR__ . '/../../includes/init_admin.php';

$s = $pdo->query(
    "SELECT COUNT(*) AS total,
            COUNT(*) FILTER (WHERE " . sql_lapak_aktif('l') . ") AS aktif,
            COUNT(*) FILTER (WHERE status_sewa = 'aktif' AND sewa_berakhir >= " . SQL_HARI_INI . "
                              AND sewa_berakhir <= " . SQL_HARI_INI . " + 7) AS segera
     FROM " . SQL_LAPAK . " l"
)->fetch();
$terbaru = $pdo->query(
    'SELECT l.id, l.nama_lapak, l.kategori, l.ukuran, (' . sql_lapak_aktif('l') . ') AS aktif
     FROM ' . SQL_LAPAK . ' l ORDER BY l.id DESC LIMIT 5'
)->fetchAll();
?>
<section class="page-heading">
    <div>
        <h1>Dashboard Admin</h1>
        <p>Kelola operasional kawasan <?= e(APP_NAME) ?>.</p>
    </div>
</section>

<section class="dashboard-grid">
    <article class="stat-card"><p>Total Lapak</p><h3><?= (int) $s['total'] ?></h3></article>
    <article class="stat-card"><p>Lapak Aktif</p><h3><?= (int) $s['aktif'] ?></h3></article>
    <article class="stat-card"><p>Lapak Nonaktif</p><h3><?= (int) $s['total'] - (int) $s['aktif'] ?></h3></article>
    <article class="stat-card"><p>Sewa Berakhir ≤ 7 Hari</p><h3><?= (int) $s['segera'] ?></h3></article>
</section>

<section class="panel">
    <div class="page-heading">
        <div>
            <h2>Lapak Terbaru</h2>
            <p>Ringkasan mitra yang terdaftar di kawasan.</p>
        </div>
        <a href="<?= e(url('api/admin/lapak_form.php')) ?>" class="btn-primary">＋ Tambah Lapak</a>
    </div>

    <?php if (!$terbaru): ?>
        <p class="empty">Belum ada lapak. Tambahkan lapak pertama.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead><tr><th>No.</th><th>Nama Lapak</th><th>Kategori</th><th>Ukuran</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($terbaru as $i => $l): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= e($l['nama_lapak']) ?></td>
                            <td><?= e($l['kategori']) ?></td>
                            <td><?= e(ucfirst($l['ukuran'])) ?></td>
                            <td><span class="badge <?= $l['aktif'] ? 'badge-success' : 'badge-danger' ?>"><?= $l['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td><a class="btn-secondary" href="<?= e(url('api/admin/lapak_form.php?id=' . (int) $l['id'])) ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
