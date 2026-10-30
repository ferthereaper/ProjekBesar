<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Pendapatan';
$tab_aktif = 'pendapatan';
require __DIR__ . '/../../includes/init_penjual.php';

// Hanya pesanan berstatus 'selesai', dihitung berdasarkan waktu selesai (WIB)
$tz = "AT TIME ZONE 'Asia/Jakarta'";
$st = $pdo->prepare(
    "SELECT
        COALESCE(SUM(total_harga) FILTER (WHERE (COALESCE(tgl_selesai, tgl_pesanan) $tz)::date = (NOW() $tz)::date), 0) AS hari,
        COALESCE(SUM(total_harga) FILTER (WHERE date_trunc('week',  COALESCE(tgl_selesai, tgl_pesanan) $tz) = date_trunc('week',  NOW() $tz)), 0) AS minggu,
        COALESCE(SUM(total_harga) FILTER (WHERE date_trunc('month', COALESCE(tgl_selesai, tgl_pesanan) $tz) = date_trunc('month', NOW() $tz)), 0) AS bulan,
        COALESCE(SUM(total_harga) FILTER (WHERE date_trunc('year',  COALESCE(tgl_selesai, tgl_pesanan) $tz) = date_trunc('year',  NOW() $tz)), 0) AS tahun,
        COUNT(*) FILTER (WHERE (COALESCE(tgl_selesai, tgl_pesanan) $tz)::date = (NOW() $tz)::date) AS jumlah_hari
     FROM pesanan WHERE id_pedagang = ? AND status_pesanan = 'selesai'"
);
$st->execute([$lapak['id']]);
$r = $st->fetch();
?>
<section class="page-heading">
    <div>
        <h1>Pendapatan</h1>
        <p>Ringkasan pendapatan <?= e($lapak['nama_lapak']) ?>.</p>
    </div>
</section>

<section class="dashboard-grid">
    <article class="stat-card"><p>Hari Ini</p><h3><?= e(rupiah($r['hari'])) ?></h3><span class="cart-price"><?= (int) $r['jumlah_hari'] ?> pesanan selesai</span></article>
    <article class="stat-card"><p>Minggu Ini</p><h3><?= e(rupiah($r['minggu'])) ?></h3></article>
    <article class="stat-card"><p>Bulan Ini</p><h3><?= e(rupiah($r['bulan'])) ?></h3></article>
    <article class="stat-card"><p>Tahun Ini</p><h3><?= e(rupiah($r['tahun'])) ?></h3></article>
</section>

<p class="notice">Dihitung dari pesanan berstatus Selesai (zona waktu WIB).</p>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
