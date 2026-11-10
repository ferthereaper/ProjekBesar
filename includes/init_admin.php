<?php
/** Dipanggil di awal setiap halaman admin (setelah $page_title & $tab_aktif diisi). */
require_once __DIR__ . '/functions.php';
wajib_peran('admin');

$tabs = [
    ['key' => 'list',   'label' => 'Daftar Lapak', 'href' => 'api/admin/lapak.php'],
    ['key' => 'tambah', 'label' => 'Tambah Lapak', 'href' => 'api/admin/lapak_form.php'],
];
include __DIR__ . '/header.php';
