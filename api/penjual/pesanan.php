<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Daftar Pesanan';
$tab_aktif = 'pesanan';
require __DIR__ . '/../../includes/init_penjual.php';

$st = $pdo->prepare("SELECT * FROM " . SQL_PESANAN . " p WHERE lapak_id = ? AND status = 'diproses' ORDER BY COALESCE(dibayar_at, created_at) ASC, id ASC");
$st->execute([$lapak['id']]);
$daftar = $st->fetchAll();
$items = ambil_items_pesanan($pdo, array_column($daftar, 'id'));

$st = $pdo->prepare("SELECT COUNT(*) FROM pesanan WHERE id_pedagang = ? AND status_pesanan = 'menunggu'");
$st->execute([$lapak['id']]);
$menunggu = (int) $st->fetchColumn();
?>
<section class="page-heading" data-refresh="30">
    <div>
        <h1>Daftar Pesanan</h1>
        <p>Pantau dan perbarui status pesanan yang masuk. Halaman ini diperbarui otomatis.</p>
    </div>
</section>

<?php if ($menunggu): ?>
    <p class="flash flash-info"><?= (int) $menunggu ?> pesanan menunggu pembayaran QRIS.</p>
<?php endif; ?>

<?php if (!$daftar): ?>
    <p class="empty">Belum ada pesanan yang perlu diproses.</p>
<?php else: ?>
    <section class="order-grid">
        <?php foreach ($daftar as $p): ?>
            <article class="order-card">
                <div class="order-head">
                    <h3><?= e($p['kode']) ?></h3>
                    <span class="badge badge-warning"><?= $p['metode'] === 'tunai' ? 'Tunai' : 'QRIS' ?></span>
                </div>
                <p class="order-meta"><?= e($p['nama_pemesan']) ?> · <?= e(waktu_wib($p['dibayar_at'] ?? $p['created_at'], 'd M H:i')) ?></p>
                <ul class="order-items">
                    <?php foreach ($items[(int) $p['id']] ?? [] as $it): ?>
                        <li><span><?= (int) $it['qty'] ?>× <?= e($it['nama_produk']) ?><?php if (!empty($it['catatan'])): ?><span class="item-note">Catatan: <?= e($it['catatan']) ?></span><?php endif; ?></span><span><?= e(rupiah($it['harga'] * $it['qty'])) ?></span></li>
                    <?php endforeach; ?>
                </ul>
                <p class="order-total">Total: <?= e(rupiah($p['total_harga'])) ?></p>
                <div class="order-actions">
                    <form method="post" action="<?= e(url('api/penjual/pesanan_aksi.php')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <input type="hidden" name="aksi" value="selesai">
                        <button type="submit" class="btn-primary">Tandai Selesai</button>
                    </form>
                    <form method="post" action="<?= e(url('api/penjual/pesanan_aksi.php')) ?>" data-konfirmasi="Batalkan pesanan ini? Stok akan dikembalikan.">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <input type="hidden" name="aksi" value="batal">
                        <button type="submit" class="btn-danger">Batalkan</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
