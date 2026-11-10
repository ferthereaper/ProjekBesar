<?php
require_once __DIR__ . '/../../includes/functions.php';
wajib_pembeli_atau_guest();
bersihkan_kedaluwarsa($pdo);

$lapakId = (int) ($_GET['lapak'] ?? 0);
$st = $pdo->prepare('SELECT l.* FROM ' . SQL_LAPAK . ' l WHERE l.id = ? AND ' . sql_lapak_aktif('l'));
$st->execute([$lapakId]);
$lapak = $st->fetch();
if (!$lapak) {
    flash_set('error', 'Lapak tidak ditemukan atau sedang tutup.');
    redirect('api/pembeli/lapak.php');
}

$st = $pdo->prepare('SELECT * FROM ' . SQL_MENU . ' m WHERE lapak_id = ? ORDER BY kategori, nama_produk');
$st->execute([$lapakId]);
$produk = $st->fetchAll();

$cart = cart_ambil('cart');
$jumlahCart = array_sum($cart['items']);
$bedaLapak = $jumlahCart > 0 && $cart['lapak_id'] !== $lapakId;
$kembali = url('api/pembeli/menu.php?lapak=' . $lapakId);

$page_title = $lapak['nama_lapak'];
$tab_aktif = 'menu';
$lapak_header = $lapak;
include __DIR__ . '/../../includes/header.php';
?>
<section class="page-heading">
    <div>
        <h1><?= e($lapak['nama_lapak']) ?></h1>
        <p>Halaman Pesanan: pilih menu, lalu lanjut ke keranjang.</p>
    </div>
    <a href="<?= e(url('api/pembeli/checkout.php')) ?>" class="btn-primary">
        Lihat Keranjang <span class="count" data-cart-count><?= (int) $jumlahCart ?></span>
    </a>
</section>

<?php if (!$produk): ?>
    <p class="empty">Lapak ini belum memiliki menu.</p>
<?php else: ?>
    <div class="toolbar">
        <div class="search-box">
            <i class="ic ic-search" aria-hidden="true"></i>
            <input type="search" placeholder="Cari menu..." data-filter-input aria-label="Cari menu">
        </div>
    </div>

    <section class="menu-grid">
        <?php foreach ($produk as $p):
            $stok = (int) $p['stok'];
            $habis = $stok <= 0;
            $dipesan = $cart['items'][(int) $p['id']] ?? 0;
            ?>
            <article class="menu-card<?= $habis ? ' habis' : '' ?>" data-filter-item>
                <div class="menu-image" aria-hidden="true"><?= foto_menu($p) ?></div>
                <div class="menu-content">
                    <h3>
                        <?= e($p['nama_produk']) ?>
                        <?php if ($dipesan && $cart['lapak_id'] === $lapakId): ?><span class="count">×<?= (int) $dipesan ?></span><?php endif; ?>
                    </h3>
                    <p><?= e($p['kategori']) ?></p>
                    <div class="menu-meta">
                        <strong><?= e(rupiah($p['harga'])) ?></strong>
                        <?php if ($habis): ?>
                            <span class="badge badge-danger">Habis</span>
                        <?php else: ?>
                            <span class="badge <?= $stok <= 5 ? 'badge-warning' : 'badge-success' ?>">Stok <?= $stok ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($habis): ?>
                        <button type="button" class="btn-primary" disabled>Stok habis</button>
                    <?php else: ?>
                        <?= form_keranjang(
                            'add', (int) $p['id'], '＋ Tambah', $kembali, 'btn-primary',
                            $bedaLapak ? 'Keranjang Anda berisi pesanan dari lapak lain. Kosongkan dan mulai pesanan baru dari lapak ini?' : '',
                            true
                        ) ?>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
    <p class="empty" data-filter-empty hidden>Tidak ada menu yang cocok dengan pencarianmu.</p>
<?php endif; ?>

<p class="more-row"><a href="<?= e(url('api/pembeli/lapak.php')) ?>" class="bn-back"><i class="ic ic-arrow-left" aria-hidden="true"></i> Kembali ke Daftar Lapak</a></p>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
