<?php
require_once __DIR__ . '/../../includes/functions.php';
wajib_pembeli_atau_guest();
bersihkan_kedaluwarsa($pdo);

$cart = cart_ambil('cart');
$lapak = null;
$baris = [];
$total = 0.0;

if ($cart['items']) {
    $st = $pdo->prepare('SELECT l.*, (' . sql_lapak_aktif('l') . ') AS aktif FROM ' . SQL_LAPAK . ' l WHERE l.id = ?');
    $st->execute([$cart['lapak_id']]);
    $lapak = $st->fetch();

    $in = implode(',', array_map('intval', array_keys($cart['items'])));
    $st = $pdo->prepare("SELECT id, nama_produk, kategori, harga, stok FROM " . SQL_MENU . " m WHERE lapak_id = ? AND id IN ($in) ORDER BY nama_produk");
    $st->execute([$cart['lapak_id']]);
    foreach ($st->fetchAll() as $p) {
        $qty = $cart['items'][(int) $p['id']];
        $baris[] = $p + ['qty' => $qty, 'subtotal' => (float) $p['harga'] * $qty];
        $total += (float) $p['harga'] * $qty;
    }
}

$kembali = url('api/pembeli/checkout.php');
$page_title = 'Checkout';
$tab_aktif = 'keranjang';
if ($lapak) {
    $lapak_header = $lapak;
}
include __DIR__ . '/../../includes/header.php';
?>
<section class="page-heading">
    <div>
        <h1>Halaman Checkout</h1>
        <p><?= $lapak && $baris ? 'Periksa pesananmu dari ' . e($lapak['nama_lapak']) . ' sebelum membayar.' : 'Periksa pesananmu sebelum membayar.' ?></p>
    </div>
</section>

<?php if (!$baris || !$lapak): ?>
    <section class="panel">
        <h2>Detail Pesanan</h2>
        <p class="empty">Keranjang masih kosong.</p>
        <div class="more-row" style="margin-top:0"><a class="btn-primary" href="<?= e(url('api/pembeli/lapak.php')) ?>">Pilih Lapak</a></div>
    </section>
<?php else: ?>
    <div class="split">
        <section class="panel">
            <h2>Detail Pesanan</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Menu</th><th>Jumlah</th><th>Subtotal</th><th><span class="sr-only">Aksi</span></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($baris as $b): ?>
                            <tr>
                                <td>
                                    <span class="cart-name"><?= e($b['nama_produk']) ?></span>
                                    <span class="cart-price"><?= e(rupiah($b['harga'])) ?> / porsi</span>
                                    <input type="text" class="cart-note" form="form-bayar" name="catatan[<?= (int) $b['id'] ?>]" maxlength="200"
                                           placeholder="Catatan, mis. tidak pedas" aria-label="Catatan untuk <?= e($b['nama_produk']) ?>">
                                </td>
                                <td>
                                    <div class="qty">
                                        <?= form_keranjang('dec', (int) $b['id'], '−', $kembali) ?>
                                        <span><?= (int) $b['qty'] ?></span>
                                        <?= form_keranjang('inc', (int) $b['id'], '+', $kembali) ?>
                                    </div>
                                </td>
                                <td><?= e(rupiah($b['subtotal'])) ?></td>
                                <td><?= form_keranjang('hapus', (int) $b['id'], 'Hapus', $kembali, 'btn-danger', 'Hapus item ini?') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="panel sticky">
            <h2>Ringkasan</h2>
            <div class="total-row">
                <span>Total Pesanan</span>
                <strong><?= e(rupiah($total)) ?></strong>
            </div>

            <div class="stack">
                <?php if (!$lapak['aktif']): ?>
                    <p class="flash flash-error" style="margin:0">Lapak ini sedang tidak menerima pesanan.</p>
                <?php else: ?>
                    <form method="post" id="form-bayar" action="<?= e(url('api/pembeli/buat_pesanan.php')) ?>" class="stack">
                        <?= csrf_field() ?>
                        <?php if (!sudah_login()): ?>
                            <div class="form-group">
                                <label for="nama">Nama pemesan (opsional)</label>
                                <input type="text" id="nama" name="nama" maxlength="100" placeholder="Guest">
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn-primary block">Bayar dengan QRIS</button>
                    </form>
                <?php endif; ?>

                <a href="<?= e(url('api/pembeli/menu.php?lapak=' . (int) $lapak['id'])) ?>" class="btn-secondary block">+ Tambah menu lain</a>

                <form method="post" action="<?= e(url('api/keranjang.php')) ?>" data-konfirmasi="Kosongkan seluruh keranjang?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="kosong">
                    <input type="hidden" name="kembali" value="<?= e(url('api/pembeli/lapak.php')) ?>">
                    <button type="submit" class="btn-danger block">Kosongkan Keranjang</button>
                </form>
            </div>
        </aside>
    </div>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
