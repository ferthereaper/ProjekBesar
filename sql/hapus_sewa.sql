-- OPSIONAL: hapus sisa fitur sewa dari database yang SUDAH ada.
-- Aplikasi tidak lagi membaca/menulis tabel ini, jadi menjalankan script ini
-- hanya merapikan database. Data sewa yang ada akan hilang permanen.
DROP TABLE IF EXISTS public.sewa_lapak;
DROP TYPE  IF EXISTS public.status_sewa;
