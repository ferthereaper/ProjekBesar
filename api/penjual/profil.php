<?php
require_once __DIR__ . '/../../includes/functions.php';
$page_title = 'Profil Lapak';
$tab_aktif = 'profil';
require __DIR__ . '/../../includes/init_penjual.php';
$adaLogo = bool_db($lapak['punya_logo'] ?? false);
?>
<section class="page-heading">
    <div>
        <h1>Profil Lapak</h1>
        <p>Atur logo yang tampil di daftar lapak untuk pembeli.</p>
    </div>
</section>

<section class="panel">
    <div class="profil-logo">
        <div class="stall-avatar profil-avatar" aria-hidden="true"><?= avatar_lapak($lapak) ?></div>
        <div>
            <h2 style="margin-bottom:4px"><?= e($lapak['nama_lapak']) ?></h2>
            <p class="hint"><?= e($lapak['kategori']) ?></p>
        </div>
    </div>

    <form method="post" action="<?= e(url('api/penjual/profil_proses.php')) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="simpan">
        <div class="form-group">
            <label for="logo">Logo Lapak</label>
            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/webp" required data-preview="#logo-baru">
            <img id="logo-baru" class="foto-preview" alt="Pratinjau logo baru" hidden>
            <span class="hint">JPG, PNG, atau WebP, maks. 2 MB. Gunakan gambar persegi; akan tampil bulat.</span>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn-primary"><?= $adaLogo ? 'Ganti Logo' : 'Unggah Logo' ?></button>
        </div>
    </form>

    <?php if ($adaLogo): ?>
        <form method="post" action="<?= e(url('api/penjual/profil_proses.php')) ?>" data-konfirmasi="Hapus logo lapak?">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="hapus">
            <button type="submit" class="btn-danger">Hapus Logo</button>
        </form>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
