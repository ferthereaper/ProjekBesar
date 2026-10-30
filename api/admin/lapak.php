<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'List Lapak';
$tab_aktif = 'list';
require __DIR__ . '/../../includes/init_admin.php';

$daftar = $pdo->query(
    'SELECT l.id, l.nama_lapak, l.kategori, l.ukuran, l.no_telepon, l.username, (' . sql_lapak_aktif('l') . ') AS aktif
     FROM ' . SQL_LAPAK . ' l ORDER BY l.nama_lapak'
)->fetchAll();

$total = count($daftar);
$aktif = count(array_filter($daftar, fn($l) => $l['aktif']));
$kategori = array_values(array_unique(array_column($daftar, 'kategori')));
sort($kategori);
?>
<section class="page-heading">
    <div>
        <h1>Daftar Lapak</h1>
        <p>Informasi dan status operasional seluruh lapak mitra.</p>
    </div>
    <a href="<?= e(url('api/admin/lapak_form.php')) ?>" class="btn-primary">＋ Tambah Lapak</a>
</section>

<section class="dashboard-grid">
    <article class="stat-card"><p>Total Lapak</p><h3><?= $total ?></h3></article>
    <article class="stat-card"><p>Lapak Aktif</p><h3><?= $aktif ?></h3></article>
    <article class="stat-card"><p>Lapak Nonaktif</p><h3><?= $total - $aktif ?></h3></article>
    <article class="stat-card"><p>Kategori</p><h3><?= count($kategori) ?></h3></article>
</section>

<section class="panel">
    <?php if (!$daftar): ?>
        <p class="empty">Belum ada lapak. Tambahkan lapak pertama.</p>
    <?php else: ?>
        <div class="toolbar">
            <div class="search-box">
                <i class="ic ic-search" aria-hidden="true"></i>
                <input type="search" placeholder="Cari nama lapak atau pemilik..." data-filter-input aria-label="Cari lapak">
            </div>
            <select data-filter-select="status" aria-label="Filter status">
                <option value="">Semua Status</option>
                <option value="Aktif">Aktif</option>
                <option value="Nonaktif">Nonaktif</option>
            </select>
            <select data-filter-select="kategori" aria-label="Filter kategori">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategori as $k): ?><option value="<?= e($k) ?>"><?= e($k) ?></option><?php endforeach; ?>
            </select>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>No.</th><th>Nama Lapak</th><th>Pemilik</th><th>Kategori</th><th>Ukuran</th><th>Telepon</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $i => $l): ?>
                        <tr data-filter-item data-status="<?= $l['aktif'] ? 'Aktif' : 'Nonaktif' ?>" data-kategori="<?= e($l['kategori']) ?>">
                            <td><?= $i + 1 ?></td>
                            <td><?= e($l['nama_lapak']) ?></td>
                            <td><?= e($l['username']) ?></td>
                            <td><?= e($l['kategori']) ?></td>
                            <td><?= e(ucfirst($l['ukuran'])) ?></td>
                            <td><?= e($l['no_telepon']) ?></td>
                            <td><span class="badge <?= $l['aktif'] ? 'badge-success' : 'badge-danger' ?>"><?= $l['aktif'] ? 'Aktif' : 'Nonaktif' ?></span></td>
                            <td><a class="btn-secondary" href="<?= e(url('api/admin/lapak_form.php?id=' . (int) $l['id'])) ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="empty" data-filter-empty hidden>Tidak ada lapak yang cocok.</p>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
