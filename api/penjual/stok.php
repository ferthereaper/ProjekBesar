<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Stok Menu';
$tab_aktif = 'stok';
require __DIR__ . '/../../includes/init_penjual.php';

$st = $pdo->prepare('SELECT * FROM ' . SQL_MENU . ' m WHERE lapak_id = ? ORDER BY nama_produk');
$st->execute([$lapak['id']]);
$produk = $st->fetchAll();

$buka = $lapak['status_toko'] === 'buka';
?>
<section class="page-heading">
    <div>
        <h1><?= e($lapak['nama_lapak']) ?></h1>
        <p>Kelola menu dan stok. Status toko: <strong><?= $buka ? 'Buka' : 'Tutup' ?></strong></p>
    </div>
    <form method="post" action="<?= e(url('api/penjual/toko_aksi.php')) ?>">
        <?= csrf_field() ?>
        <button type="submit" class="<?= $buka ? 'btn-danger' : 'btn-success' ?>"><?= $buka ? 'Tutup Toko' : 'Buka Toko' ?></button>
    </form>
</section>

<form method="post" action="<?= e(url('api/penjual/stok_simpan.php')) ?>" class="panel">
    <?= csrf_field() ?>
    <div class="page-heading">
        <h2>Daftar Menu</h2>
        <a class="btn-primary" href="<?= e(url('api/penjual/produk_form.php')) ?>">＋ Tambah Menu</a>
    </div>

    <?php if (!$produk): ?>
        <p class="empty">Belum ada menu. Tambahkan menu pertama Anda.</p>
    <?php else: ?>
        <div class="toolbar">
            <div class="search-box">
                <i class="ic ic-search" aria-hidden="true"></i>
                <input type="search" placeholder="Cari menu..." data-filter-input aria-label="Cari menu">
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Menu</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($produk as $p): $ada = (int) $p['stok'] > 0; ?>
                        <tr data-filter-item>
                            <td><?= e($p['nama_produk']) ?></td>
                            <td><?= e($p['kategori']) ?></td>
                            <td><?= e(rupiah($p['harga'])) ?></td>
                            <td>
                                <div class="stepper">
                                    <button type="button" data-step="-1" aria-label="Kurangi stok">−</button>
                                    <input type="number" name="stok[<?= (int) $p['id'] ?>]" value="<?= (int) $p['stok'] ?>" min="0" max="100000" inputmode="numeric" aria-label="Stok <?= e($p['nama_produk']) ?>">
                                    <button type="button" data-step="1" aria-label="Tambah stok">+</button>
                                    <input type="hidden" name="awal[<?= (int) $p['id'] ?>]" value="<?= (int) $p['stok'] ?>">
                                </div>
                            </td>
                            <td><span class="badge <?= $ada ? 'badge-success' : 'badge-warning' ?>"><?= $ada ? 'Tersedia' : 'Habis' ?></span></td>
                            <td><a class="btn-secondary" href="<?= e(url('api/penjual/produk_form.php?id=' . (int) $p['id'])) ?>">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="empty" data-filter-empty hidden>Tidak ada menu yang cocok.</p>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan Stok</button>
        </div>
    <?php endif; ?>
</form>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
