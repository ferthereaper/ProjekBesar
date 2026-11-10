<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'List Lapak';
$tab_aktif = 'list';
require __DIR__ . '/../../includes/init_admin.php';

$daftar = $pdo->query(
    'SELECT l.id, l.nama_lapak, l.kategori, l.username, l.status_toko FROM ' . SQL_LAPAK . ' l ORDER BY l.nama_lapak'
)->fetchAll();
?>
<section class="page-heading">
    <div>
        <h1>Daftar Lapak</h1>
        <p><?= count($daftar) ?> lapak terdaftar.</p>
    </div>
    <a href="<?= e(url('api/admin/lapak_form.php')) ?>" class="btn-primary">＋ Tambah Lapak</a>
</section>

<section class="panel">
    <?php if (!$daftar): ?>
        <p class="empty">Belum ada lapak. Tambahkan lapak pertama.</p>
    <?php else: ?>
        <div class="toolbar">
            <div class="search-box">
                <i class="ic ic-search" aria-hidden="true"></i>
                <input type="search" placeholder="Cari lapak..." data-filter-input aria-label="Cari lapak">
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Nama Lapak</th><th>Pemilik</th><th>Kategori</th><th>Toko</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($daftar as $l): ?>
                        <tr data-filter-item>
                            <td><?= e($l['nama_lapak']) ?></td>
                            <td><?= e($l['username']) ?></td>
                            <td><?= e($l['kategori']) ?></td>
                            <td><span class="badge <?= $l['status_toko'] === 'buka' ? 'badge-success' : 'badge-danger' ?>"><?= $l['status_toko'] === 'buka' ? 'Buka' : 'Tutup' ?></span></td>
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
