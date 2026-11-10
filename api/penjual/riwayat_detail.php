<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Detail Riwayat';
$tab_aktif = 'riwayat';
require __DIR__ . '/../../includes/init_penjual.php';

$st = $pdo->prepare('SELECT * FROM ' . SQL_PESANAN . ' p WHERE id = ? AND lapak_id = ?');
$st->execute([(int) ($_GET['id'] ?? 0), $lapak['id']]);
$p = $st->fetch();
if (!$p) {
    flash_set('error', 'Pesanan tidak ditemukan.');
    redirect('api/penjual/riwayat.php');
}
$items = ambil_items_pesanan($pdo, [(int) $p['id']])[(int) $p['id']] ?? [];
?>
<section class="page-heading">
    <div>
        <h1>Detail Pesanan</h1>
        <p><?= e($p['kode']) ?> · <?= e($p['nama_pemesan']) ?></p>
    </div>
    <a class="btn-secondary" href="<?= e(url('api/penjual/riwayat.php')) ?>"><i class="ic ic-arrow-left" aria-hidden="true"></i> Kembali</a>
</section>

<section class="panel">
    <div class="order-head" style="margin-bottom:16px">
        <h2 style="margin:0"><?= e($p['kode']) ?></h2>
        <span class="badge <?= $p['status'] === 'selesai' ? 'badge-success' : 'badge-danger' ?>"><?= e(label_status($p['status'])) ?></span>
    </div>

    <p class="order-meta" style="margin-bottom:16px">
        <?= $p['metode'] === 'tunai' ? 'Tunai' : 'QRIS' ?> · dibuat <?= e(waktu_wib($p['created_at'])) ?>
        <?php if ($p['selesai_at']): ?> · selesai <?= e(waktu_wib($p['selesai_at'])) ?><?php endif; ?>
    </p>

    <div class="table-wrap">
        <table>
            <thead><tr><th>Menu</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= e($it['nama_produk']) ?><?php if (!empty($it['catatan'])): ?><span class="item-note">Catatan: <?= e($it['catatan']) ?></span><?php endif; ?></td>
                        <td>×<?= (int) $it['qty'] ?></td>
                        <td><?= e(rupiah($it['harga'] * $it['qty'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="total-row">
        <span>Total</span>
        <strong><?= e(rupiah($p['total_harga'])) ?></strong>
    </div>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
