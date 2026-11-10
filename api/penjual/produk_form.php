<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Menu';
$tab_aktif = 'stok';
require __DIR__ . '/../../includes/init_penjual.php';

$produk = ['id' => '', 'nama_produk' => '', 'kategori' => '', 'harga' => '', 'stok' => '0'];
$edit = false;
if (isset($_GET['id'])) {
    $st = $pdo->prepare('SELECT * FROM ' . SQL_MENU . ' m WHERE id = ? AND lapak_id = ?');
    $st->execute([(int) $_GET['id'], $lapak['id']]);
    if ($data = $st->fetch()) {
        $produk = $data;
        $edit = true;
    }
}
?>
<section class="page-heading">
    <div>
        <h1><?= $edit ? 'Edit Menu' : 'Tambah Menu' ?></h1>
        <p><?= $edit ? 'Perbarui informasi menu.' : 'Tambahkan menu baru ke lapakmu.' ?></p>
    </div>
    <a class="btn-secondary" href="<?= e(url('api/penjual/stok.php')) ?>"><i class="ic ic-arrow-left" aria-hidden="true"></i> Kembali</a>
</section>

<section class="panel">
    <form method="post" action="<?= e(url('api/penjual/produk_proses.php')) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $produk['id'] ?>"><?php endif; ?>

        <div class="form-grid">
            <div class="form-group">
                <label for="nama_produk">Nama Menu</label>
                <input type="text" id="nama_produk" name="nama_produk" value="<?= e($produk['nama_produk']) ?>" required maxlength="150" placeholder="Contoh: Nasi Goreng">
            </div>

            <div class="form-group">
                <label for="kategori">Kategori</label>
                <input type="text" id="kategori" name="kategori" value="<?= e($produk['kategori']) ?>" required maxlength="100" list="daftar-kategori" placeholder="mis. Makanan, Minuman, Camilan">
                <datalist id="daftar-kategori"><option value="Makanan"><option value="Minuman"><option value="Camilan"></datalist>
            </div>

            <div class="form-group">
                <label for="harga">Harga (Rp)</label>
                <input type="number" id="harga" name="harga" value="<?= e($produk['harga'] === '' ? '' : (int) $produk['harga']) ?>" min="0" max="100000000" required inputmode="numeric" placeholder="15000">
            </div>

            <div class="form-group">
                <label for="stok">Jumlah Stok</label>
                <input type="number" id="stok" name="stok" value="<?= (int) $produk['stok'] ?>" min="0" max="100000" required inputmode="numeric">
            </div>

            <div class="form-group full">
                <label for="gambar">Foto Menu (opsional)</label>
                <?php if ($edit && bool_db($produk['punya_gambar'] ?? false)): ?>
                    <div class="foto-preview"><img src="<?= e(gambar_url('menu', (int) $produk['id'])) ?>" alt="Foto <?= e($produk['nama_produk']) ?>"></div>
                <?php endif; ?>
                <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/webp" data-preview="#foto-baru">
                <img id="foto-baru" class="foto-preview" alt="Pratinjau foto baru" hidden>
                <span class="hint">JPG, PNG, atau WebP, maks. 2 MB. Disarankan rasio persegi / 4:3.</span>
                <?php if ($edit && bool_db($produk['punya_gambar'] ?? false)): ?>
                    <label class="check"><input type="checkbox" name="hapus_gambar" value="1"> Hapus foto saat ini</label>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Simpan</button>
            <a class="btn-secondary" href="<?= e(url('api/penjual/stok.php')) ?>">Batal</a>
        </div>
    </form>

    <?php if ($edit): ?>
        <form method="post" action="<?= e(url('api/penjual/produk_proses.php')) ?>" data-konfirmasi="Hapus menu ini? Riwayat pesanan tetap tersimpan.">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="id" value="<?= (int) $produk['id'] ?>">
            <h2 class="section-title">Zona Berbahaya</h2>
            <p class="hint" style="margin-bottom:14px">Menu yang pernah dipesan tidak dapat dihapus; atur stoknya ke 0.</p>
            <button type="submit" class="btn-danger">Hapus Menu</button>
        </form>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
