<?php
require_once __DIR__ . '/../../includes/functions.php';
wajib_pembeli_atau_guest();
bersihkan_kedaluwarsa($pdo);

$kode = (string) ($_GET['kode'] ?? '');
$st = $pdo->prepare(
    'SELECT p.*, l.nama_lapak, GREATEST(0, CEIL(EXTRACT(EPOCH FROM (p.kedaluwarsa_at - NOW())))) AS sisa
     FROM ' . SQL_PESANAN . ' p JOIN ' . SQL_LAPAK . ' l ON l.id = p.lapak_id WHERE p.kode = ?'
);
$st->execute([$kode]);
$p = $st->fetch();

$milikSaya = $p && (
    in_array($kode, $_SESSION['pesanan_saya'] ?? [], true)
    || (sudah_login() && (int) $p['user_id'] === (int) $_SESSION['user_id'])
);
if (!$milikSaya) {
    flash_set('error', 'Pesanan tidak ditemukan.');
    redirect('api/pembeli/lapak.php');
}

$items = ambil_items_pesanan($pdo, [(int) $p['id']])[(int) $p['id']] ?? [];
$qrisFile = is_file(__DIR__ . '/../../assets/img/qris.png') ? 'assets/img/qris.png' : 'assets/img/qris-contoh.svg';

$page_title = 'Pembayaran';
include __DIR__ . '/../../includes/header.php';
?>
<?php if ($p['status'] === 'menunggu'): ?>
    <section class="qris-card">
        <h1>Selesaikan Pembayaran</h1>
        <p class="muted">Scan QRIS dan bayar sebelum waktu habis.</p>
        <p class="timer" role="timer"><span data-sisa="<?= (int) $p['sisa'] ?>">--:--</span></p>

        <div class="qris-code"><img src="<?= e(url($qrisFile)) ?>" alt="Kode QRIS pembayaran"></div>

        <div class="qris-total">
            <span>Total Transaksi</span>
            <strong><?= e(rupiah($p['total_harga'])) ?></strong>
        </div>

        <ol class="steps">
            <li>Buka aplikasi pembayaran (LinkAja, Dana, GoPay, atau e-wallet lainnya).</li>
            <li>Scan QRIS, kemudian bayar.</li>
        </ol>

        <?php if (SIMULASI_PEMBAYARAN): ?>
            <p class="notice">Mode demo: belum terhubung ke layanan pembayaran. Gunakan tombol di bawah untuk mensimulasikan pembayaran.</p>
            <form method="post" action="<?= e(url('api/pembeli/bayar_konfirmasi.php')) ?>" class="stack">
                <?= csrf_field() ?>
                <input type="hidden" name="kode" value="<?= e($p['kode']) ?>">
                <button type="submit" class="btn-primary block">Simulasi: Saya Sudah Bayar</button>
            </form>
        <?php endif; ?>
    </section>

<?php elseif ($p['status'] === 'dibatalkan'): ?>
    <section class="qris-card">
        <div class="status-mark bad" aria-hidden="true">✕</div>
        <h1>Pesanan Dibatalkan</h1>
        <p class="muted">Waktu pembayaran habis atau pesanan dibatalkan. Stok sudah dikembalikan.</p>
        <div class="stack"><a class="btn-primary block" href="<?= e(url('api/pembeli/lapak.php')) ?>">Pesan Lagi</a></div>
    </section>

<?php else: ?>
    <section class="qris-card" <?= $p['status'] === 'diproses' ? 'data-refresh="15"' : '' ?>>
        <div class="status-mark <?= $p['status'] === 'selesai' ? 'ok' : '' ?>" aria-hidden="true"><?= $p['status'] === 'selesai' ? '✔' : '⏳' ?></div>
        <h1><?= $p['status'] === 'selesai' ? 'Pesanan Selesai' : 'Pembayaran Diterima' ?></h1>
        <p class="muted">
            <?= $p['status'] === 'selesai'
                ? 'Silakan ambil pesanan Anda di lapak. Terima kasih!'
                : 'Pesanan Anda sedang disiapkan oleh lapak. Halaman ini memperbarui status otomatis.' ?>
        </p>
        <div class="stack"><a class="btn-secondary block" href="<?= e(url('api/pembeli/lapak.php')) ?>">Kembali ke Daftar Lapak</a></div>
    </section>
<?php endif; ?>

<section class="panel pay-summary">
    <div class="order-head">
        <h2>No Pesanan <?= e($p['kode']) ?></h2>
        <span class="badge <?= $p['status'] === 'selesai' ? 'badge-success' : ($p['status'] === 'dibatalkan' ? 'badge-danger' : 'badge-warning') ?>"><?= e(label_status($p['status'])) ?></span>
    </div>
    <p class="muted" style="margin:6px 0 16px"><?= e($p['nama_lapak']) ?> · <?= e(waktu_wib($p['created_at'])) ?></p>

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
