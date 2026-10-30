<?php
require_once __DIR__ . '/../../includes/functions.php';
wajib_peran('penjual');
wajib_post('api/penjual/profil.php');

$lapak = lapak_saya($pdo);
if (!$lapak) {
    redirect('api/penjual/profil.php');
}

if (($_POST['aksi'] ?? '') === 'hapus') {
    $pdo->prepare('UPDATE pedagang SET logo = NULL, logo_mime = NULL WHERE id_pedagang = ?')->execute([$lapak['id']]);
    flash_set('success', 'Logo dihapus.');
    redirect('api/penjual/profil.php');
}

$gambar = baca_upload_gambar('logo', $galat);
if (!$gambar) {
    flash_set('error', $galat ?? 'Pilih file gambar terlebih dahulu.');
    redirect('api/penjual/profil.php');
}

exec_dengan_blob(
    $pdo, 'UPDATE pedagang SET logo = ?, logo_mime = ? WHERE id_pedagang = ?',
    [$gambar['data'], $gambar['mime'], $lapak['id']], 0, $gambar['data']
);
flash_set('success', 'Logo lapak diperbarui.');
redirect('api/penjual/profil.php');
