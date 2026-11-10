<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Riwayat';
$tab_aktif = 'riwayat';
require __DIR__ . '/../../includes/init_penjual.php';

$st = $pdo->prepare(
    "SELECT * FROM " . SQL_PESANAN . " p WHERE lapak_id = ? AND status IN ('selesai', 'dibatalkan')
     ORDER BY COALESCE(selesai_at, created_at) DESC, id DESC LIMIT 100"
);
$st->execute([$lapak['id']]);
$daftar = $st->fetchAll();
?>
<section class="page-heading">
    <div>
        <h1>Riwayat Transaksi</h1>
        <p>Daftar pesanan yang sudah selesai atau dibatalkan.</p>
    </div>
</section>

<section class="panel">
    <?php if (!$daftar): ?>
        <p class="empty">Belum ada riwayat pesanan.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>No. Pesanan</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $p): ?>
                        <tr>
                            <td><?= e($p['kode']) ?></td>
                            <td><?= e(waktu_wib($p['selesai_at'] ?? $p['created_at'])) ?></td>
                            <td><?= e(rupiah($p['total_harga'])) ?></td>
                            <td><span class="badge <?= $p['status'] === 'selesai' ? 'badge-success' : 'badge-danger' ?>"><?= e(label_status($p['status'])) ?></span></td>
                            <td><a class="btn-secondary" href="<?= e(url('api/penjual/riwayat_detail.php?id=' . (int) $p['id'])) ?>">Detail</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($daftar) === 100): ?><p class="copy">Menampilkan 100 transaksi terbaru.</p><?php endif; ?>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
