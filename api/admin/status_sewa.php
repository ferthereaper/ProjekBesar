<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Status Sewa';
$tab_aktif = 'sewa';
require __DIR__ . '/../../includes/init_admin.php';

$daftar = $pdo->query(
    'SELECT l.*, (l.sewa_berakhir - ' . SQL_HARI_INI . ') AS sisa_hari
     FROM ' . SQL_LAPAK . ' l
     ORDER BY l.sewa_berakhir ASC NULLS FIRST, l.nama_lapak'
)->fetchAll();

function badge_sewa(array $l): array
{
    if ($l['sewa_berakhir'] === null)  { return ['Belum ada sewa', 'badge-danger']; }
    if ($l['status_sewa'] !== 'aktif') { return ['Nonaktif', 'badge-danger']; }
    if ((int) $l['sisa_hari'] < 0)     { return ['Berakhir', 'badge-danger']; }
    if ((int) $l['sisa_hari'] <= 7)    { return ['Segera berakhir', 'badge-warning']; }
    return ['Aktif', 'badge-success'];
}

$hitung = ['Aktif' => 0, 'Segera berakhir' => 0, 'lain' => 0];
foreach ($daftar as $l) {
    [$lb] = badge_sewa($l);
    $hitung[isset($hitung[$lb]) ? $lb : 'lain']++;
}
?>
<section class="page-heading">
    <div>
        <h1>Status Sewa Lapak</h1>
        <p>Pantau masa sewa mitra, perpanjang, atau nonaktifkan lapak.</p>
    </div>
</section>

<section class="dashboard-grid">
    <article class="stat-card"><p>Sewa Aktif</p><h3><?= $hitung['Aktif'] ?></h3></article>
    <article class="stat-card"><p>Segera Berakhir</p><h3><?= $hitung['Segera berakhir'] ?></h3></article>
    <article class="stat-card"><p>Berakhir / Nonaktif</p><h3><?= $hitung['lain'] ?></h3></article>
    <article class="stat-card"><p>Total Data</p><h3><?= count($daftar) ?></h3></article>
</section>

<section class="panel">
    <?php if (!$daftar): ?>
        <p class="empty">Belum ada lapak.</p>
    <?php else: ?>
        <div class="toolbar">
            <div class="search-box">
                <i class="ic ic-search" aria-hidden="true"></i>
                <input type="search" placeholder="Cari nama lapak..." data-filter-input aria-label="Cari lapak">
            </div>
            <select data-filter-select="status" aria-label="Filter status sewa">
                <option value="">Semua Status</option>
                <option value="Aktif">Aktif</option>
                <option value="Segera berakhir">Segera berakhir</option>
                <option value="Berakhir">Berakhir</option>
                <option value="Nonaktif">Nonaktif</option>
            </select>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Lapak</th><th>Masa Sewa</th><th>Biaya</th><th>Sisa</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $l): [$label, $kelas] = badge_sewa($l); ?>
                        <tr data-filter-item data-status="<?= e($label) ?>">
                            <td>
                                <span class="cart-name"><?= e($l['nama_lapak']) ?></span>
                                <span class="cart-price"><?= e($l['kategori']) ?> · <?= e($l['no_telepon']) ?><?= $l['status_toko'] !== 'buka' ? ' · toko ditutup penjual' : '' ?></span>
                            </td>
                            <td>
                                <?php if ($l['sewa_berakhir'] !== null): ?>
                                    <?= e(date('d M Y', strtotime($l['sewa_mulai']))) ?> – <?= e(date('d M Y', strtotime($l['sewa_berakhir']))) ?>
                                <?php else: ?>–<?php endif; ?>
                            </td>
                            <td><?= $l['sewa_berakhir'] !== null ? e(rupiah($l['biaya_sewa'])) : '–' ?></td>
                            <td><?= ($l['sewa_berakhir'] !== null && (int) $l['sisa_hari'] >= 0 && $l['status_sewa'] === 'aktif') ? (int) $l['sisa_hari'] . ' hari' : '–' ?></td>
                            <td><span class="badge <?= $kelas ?>"><?= e($label) ?></span></td>
                            <td>
                                <div class="cart-actions">
                                    <form method="post" action="<?= e(url('api/admin/sewa_aksi.php')) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                                        <input type="hidden" name="aksi" value="perpanjang">
                                        <button type="submit" class="btn-success">Perpanjang 30 hari</button>
                                    </form>
                                    <form method="post" action="<?= e(url('api/admin/sewa_aksi.php')) ?>"
                                          <?= $l['status_sewa'] === 'aktif' ? 'data-konfirmasi="Nonaktifkan lapak ini? Lapak tidak akan tampil bagi pembeli."' : '' ?>>
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                                        <input type="hidden" name="aksi" value="toggle">
                                        <button type="submit" class="<?= $l['status_sewa'] === 'aktif' ? 'btn-danger' : 'btn-secondary' ?>"><?= $l['status_sewa'] === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="empty" data-filter-empty hidden>Tidak ada data yang cocok.</p>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
