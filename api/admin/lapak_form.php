<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Form Lapak';
$tab_aktif = 'tambah';
require __DIR__ . '/../../includes/init_admin.php';

$hari = (new DateTime('today', new DateTimeZone('Asia/Jakarta')));
$lapak = [
    'id' => '', 'nama_lapak' => '', 'kategori' => '', 'ukuran' => '', 'no_telepon' => '',
    'sewa_mulai' => $hari->format('Y-m-d'),
    'sewa_berakhir' => (clone $hari)->modify('+30 days')->format('Y-m-d'),
    'biaya_sewa' => '', 'username' => '', 'email' => '',
];
$edit = false;
if (isset($_GET['id'])) {
    $st = $pdo->prepare('SELECT l.* FROM ' . SQL_LAPAK . ' l WHERE l.id = ?');
    $st->execute([(int) $_GET['id']]);
    if ($data = $st->fetch()) {
        $lapak = $data;
        $edit = true;
    }
}
// Isi ulang form jika validasi gagal
$old = $_SESSION['old_lapak'] ?? null;
unset($_SESSION['old_lapak']);
if (is_array($old)) {
    $lapak = array_merge($lapak, $old);
}
?>
<section class="page-heading">
    <div>
        <h1><?= $edit ? 'Edit Lapak' : 'Tambah Lapak' ?></h1>
        <p><?= $edit ? 'Perbarui data lapak, masa sewa, dan akun penjual.' : 'Daftarkan mitra lapak baru beserta akun penjualnya.' ?></p>
    </div>
    <?php if ($edit): ?>
        <a class="btn-secondary" href="<?= e(url('api/admin/lapak.php')) ?>"><i class="ic ic-arrow-left" aria-hidden="true"></i> Kembali</a>
    <?php endif; ?>
</section>

<section class="panel">
    <form method="post" action="<?= e(url('api/admin/lapak_proses.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $lapak['id'] ?>"><?php endif; ?>

        <h2>Data Lapak</h2>
        <div class="form-grid">
            <div class="form-group">
                <label for="nama_lapak">Nama Lapak</label>
                <input type="text" id="nama_lapak" name="nama_lapak" value="<?= e($lapak['nama_lapak']) ?>" required maxlength="150">
            </div>

            <div class="form-group">
                <label for="kategori">Kategori Lapak</label>
                <input type="text" id="kategori" name="kategori" value="<?= e($lapak['kategori']) ?>" required maxlength="100" placeholder="mis. Makanan berat, Minuman, Camilan">
            </div>

            <div class="form-group">
                <label for="ukuran">Ukuran Tenant</label>
                <select id="ukuran" name="ukuran" required>
                    <option value="">Pilih ukuran</option>
                    <?php foreach (['besar' => 'Besar', 'sedang' => 'Sedang', 'kecil' => 'Kecil'] as $v => $t): ?>
                        <option value="<?= $v ?>" <?= $lapak['ukuran'] === $v ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="no_telepon">No Telepon</label>
                <input type="tel" id="no_telepon" name="no_telepon" value="<?= e($lapak['no_telepon']) ?>" required maxlength="20" pattern="[0-9+\- ]{6,20}" inputmode="tel">
            </div>

            <div class="form-group">
                <label for="biaya_sewa">Biaya Sewa (Rp)</label>
                <input type="number" id="biaya_sewa" name="biaya_sewa" value="<?= e($lapak['biaya_sewa'] === '' || $lapak['biaya_sewa'] === null ? '' : (int) $lapak['biaya_sewa']) ?>" min="0" max="1000000000" required inputmode="numeric">
            </div>

            <div class="form-group"></div>

            <div class="form-group">
                <label for="sewa_mulai">Sewa Mulai</label>
                <input type="date" id="sewa_mulai" name="sewa_mulai" value="<?= e($lapak['sewa_mulai']) ?>" required>
            </div>

            <div class="form-group">
                <label for="sewa_berakhir">Sewa Berakhir</label>
                <input type="date" id="sewa_berakhir" name="sewa_berakhir" value="<?= e($lapak['sewa_berakhir']) ?>" required>
            </div>
        </div>

        <h2 class="section-title">Akun Penjual</h2>
        <p class="hint" style="margin-bottom:16px">Dipakai pemilik lapak untuk masuk sebagai Penjual / Kasir.</p>
        <div class="form-grid">
            <div class="form-group">
                <label for="username">Username Penjual</label>
                <input type="text" id="username" name="username" value="<?= e($lapak['username'] ?? '') ?>"
                       <?= $edit ? 'readonly' : 'required' ?> maxlength="30" pattern="[A-Za-z0-9_.]{3,30}" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="email">Email Penjual</label>
                <input type="email" id="email" name="email" value="<?= e($lapak['email'] ?? '') ?>" <?= $edit ? 'readonly' : 'required' ?> maxlength="255" autocomplete="off">
            </div>

            <div class="form-group full">
                <label for="password"><?= $edit ? 'Password Baru (kosongkan jika tidak diubah)' : 'Password Penjual' ?></label>
                <input type="password" id="password" name="password" <?= $edit ? '' : 'required' ?> minlength="8" autocomplete="new-password">
                <span class="hint">Minimal 8 karakter.</span>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary"><?= $edit ? 'Simpan Perubahan' : 'Simpan Lapak' ?></button>
            <?php if ($edit): ?><a class="btn-secondary" href="<?= e(url('api/admin/lapak.php')) ?>">Batal</a><?php endif; ?>
        </div>
    </form>

    <?php if ($edit): ?>
        <form method="post" action="<?= e(url('api/admin/lapak_proses.php')) ?>"
              data-konfirmasi="Hapus lapak ini beserta akun penjual, menu, dan SELURUH riwayat pesanannya? Tindakan ini tidak bisa dibatalkan.">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="hapus">
            <input type="hidden" name="id" value="<?= (int) $lapak['id'] ?>">
            <h2 class="section-title">Zona Berbahaya</h2>
            <p class="hint" style="margin-bottom:14px">Menghapus lapak juga menghapus akun penjual, menu, dan seluruh riwayat pesanannya.</p>
            <button type="submit" class="btn-danger">Hapus Lapak</button>
        </form>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
