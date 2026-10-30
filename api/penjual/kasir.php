<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Kasir';
$tab_aktif = 'kasir';
require __DIR__ . '/../../includes/init_penjual.php';

$st = $pdo->prepare('SELECT * FROM ' . SQL_MENU . ' m WHERE lapak_id = ? ORDER BY nama_produk');
$st->execute([$lapak['id']]);
$produk = $st->fetchAll();

$cart = cart_ambil('kasir_cart');
if ($cart['lapak_id'] !== (int) $lapak['id']) {
    $cart = ['lapak_id' => 0, 'items' => []];
}
$map = [];
foreach ($produk as $p) { $map[(int) $p['id']] = $p; }

$baris = [];
$total = 0.0;
foreach ($cart['items'] as $pid => $qty) {
    if (isset($map[$pid])) {
        $sub = (float) $map[$pid]['harga'] * $qty;
        $baris[] = ['p' => $map[$pid], 'qty' => $qty, 'sub' => $sub];
        $total += $sub;
    }
}
$kembali = url('api/penjual/kasir.php');
?>
<section class="page-heading">
    <div>
        <h1>Kasir</h1>
        <p>Catat transaksi langsung di lapak.</p>
    </div>
</section>

<div class="split">
    <section>
        <?php if (!$produk): ?>
            <p class="empty">Belum ada menu. Tambahkan di tab Stok Menu.</p>
        <?php else: ?>
            <div class="toolbar">
                <div class="search-box">
                    <i class="ic ic-search" aria-hidden="true"></i>
                    <input type="search" placeholder="Cari menu..." data-filter-input aria-label="Cari menu">
                </div>
            </div>
            <div class="menu-grid">
                <?php foreach ($produk as $p): $stok = (int) $p['stok']; $habis = $stok <= 0; ?>
                    <article class="menu-card<?= $habis ? ' habis' : '' ?>" data-filter-item>
                        <div class="menu-image" aria-hidden="true"><?= foto_menu($p) ?></div>
                        <div class="menu-content">
                            <h3><?= e($p['nama_produk']) ?></h3>
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
                                <?= form_keranjang('add', (int) $p['id'], '＋ Tambah', $kembali, 'btn-primary') ?>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="empty" data-filter-empty hidden>Tidak ada menu yang cocok.</p>
        <?php endif; ?>
    </section>

    <aside class="panel sticky">
        <h2>Pesanan di Kasir</h2>
        <?php if (!$baris): ?>
            <p class="empty">Belum ada item. Pilih menu di sebelah kiri.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Menu</th><th>Jumlah</th><th>Subtotal</th></tr></thead>
                    <tbody>
                        <?php foreach ($baris as $b): ?>
                            <tr>
                                <td>
                                    <span class="cart-name"><?= e($b['p']['nama_produk']) ?></span>
                                    <span class="cart-price"><?= e(rupiah($b['p']['harga'])) ?></span>
                                </td>
                                <td>
                                    <div class="qty">
                                        <?= form_keranjang('dec', (int) $b['p']['id'], '−', $kembali) ?>
                                        <span><?= (int) $b['qty'] ?></span>
                                        <?= form_keranjang('inc', (int) $b['p']['id'], '+', $kembali) ?>
                                    </div>
                                </td>
                                <td><?= e(rupiah($b['sub'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="total-row">
                <span>Total</span>
                <strong><?= e(rupiah($total)) ?></strong>
            </div>

            <div class="stack">
                <form method="post" action="<?= e(url('api/penjual/kasir_proses.php')) ?>" class="stack">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="nama">Nama pemesan (opsional)</label>
                        <input type="text" id="nama" name="nama" maxlength="100" placeholder="Pembeli di tempat">
                    </div>
                    <button type="submit" class="btn-primary block">Proses Pesanan (Tunai)</button>
                </form>
                <form method="post" action="<?= e(url('api/keranjang.php')) ?>" data-konfirmasi="Kosongkan pesanan kasir?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="kosong">
                    <input type="hidden" name="kembali" value="<?= e($kembali) ?>">
                    <button type="submit" class="btn-danger block">Kosongkan</button>
                </form>
            </div>
        <?php endif; ?>
    </aside>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
