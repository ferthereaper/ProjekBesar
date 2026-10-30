<?php
require_once __DIR__ . '/../../includes/functions.php';
wajib_pembeli_atau_guest();

// Riwayat terikat ke akun, jadi tamu diminta login dulu
if (!sudah_login()) {
    flash_set('error', 'Silakan login sebagai pembeli untuk melihat riwayat pesanan.');
    redirect('auth/login.php');
}
bersihkan_kedaluwarsa($pdo);

$st = $pdo->prepare(
    'SELECT p.id, p.kode, p.created_at, p.total_harga, p.status, p.metode, l.nama_lapak
     FROM ' . SQL_PESANAN . ' p JOIN ' . SQL_LAPAK . ' l ON l.id = p.lapak_id
     WHERE p.user_id = ? ORDER BY p.created_at DESC LIMIT 100'
);
$st->execute([(int) $_SESSION['user_id']]);
$daftar = $st->fetchAll();

$page_title = 'Riwayat Pesanan';
$tab_aktif = 'riwayat';
include __DIR__ . '/../../includes/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Riwayat Pesanan</h1>
        <p>Daftar pesanan yang pernah kamu buat.</p>
    </div>
</section>

<section class="panel">
    <?php if (!$daftar): ?>
        <p class="empty">Belum ada pesanan.</p>
        <div class="more-row" style="margin-top:0"><a class="btn-primary" href="<?= e(url('api/pembeli/lapak.php')) ?>">Pilih Lapak</a></div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>No. Pesanan</th><th>Lapak</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $p): ?>
                        <tr>
                            <td><?= e($p['kode']) ?></td>
                            <td><?= e($p['nama_lapak']) ?></td>
                            <td><?= e(waktu_wib($p['created_at'])) ?></td>
                            <td><?= e(rupiah($p['total_harga'])) ?></td>
                            <td><span class="badge <?= $p['status'] === 'selesai' ? 'badge-success' : ($p['status'] === 'dibatalkan' ? 'badge-danger' : 'badge-warning') ?>"><?= e(label_status($p['status'])) ?></span></td>
                            <td><a class="btn-secondary" href="<?= e(url('api/pembeli/bayar.php?kode=' . urlencode($p['kode']))) ?>"><?= $p['status'] === 'menunggu' ? 'Bayar' : 'Detail' ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($daftar) === 100): ?><p class="copy">Menampilkan 100 pesanan terbaru.</p><?php endif; ?>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
