-- ============================================================
-- Brawijaya Street Food - skema PostgreSQL (DBeaver)
-- Versi TANPA fitur sewa, dengan kolom gambar.
--
-- Aman dijalankan di database BARU maupun database yang SUDAH ada
-- (semua perintah memakai IF NOT EXISTS / IF EXISTS).
-- Jalankan seluruh script: Alt+X (Execute Script).
-- ============================================================

-- ---------- 1. Tipe enum ----------
DO $$ BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'peran_pengguna') THEN
        CREATE TYPE public.peran_pengguna AS ENUM ('pelanggan', 'pedagang', 'admin');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_meja') THEN
        CREATE TYPE public.status_meja AS ENUM ('tersedia', 'terisi', 'dipesan');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'ukuran_tenant') THEN
        CREATE TYPE public.ukuran_tenant AS ENUM ('kecil', 'sedang', 'besar');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_toko') THEN
        CREATE TYPE public.status_toko AS ENUM ('buka', 'tutup');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_pesanan') THEN
        CREATE TYPE public.status_pesanan AS ENUM ('menunggu', 'diproses', 'selesai', 'dibatalkan');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'status_pembayaran') THEN
        CREATE TYPE public.status_pembayaran AS ENUM ('pending', 'lunas', 'kedaluwarsa');
    END IF;
    IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'metode_pembayaran') THEN
        CREATE TYPE public.metode_pembayaran AS ENUM ('qris', 'tunai');
    END IF;
END $$;

-- ---------- 2. Tabel ----------
CREATE TABLE IF NOT EXISTS public.pengguna (
    id_pengguna    integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama_pengguna  varchar(100) NOT NULL UNIQUE,
    email_pengguna varchar(255) NOT NULL UNIQUE,
    kata_sandi     varchar(255) NOT NULL,
    nomor_telepon  varchar(30),
    peran          public.peran_pengguna NOT NULL DEFAULT 'pelanggan',
    dibuat_pada    timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE IF NOT EXISTS public.meja (
    id_meja   integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    no_meja   varchar(20) NOT NULL UNIQUE,
    kapasitas integer NOT NULL CHECK (kapasitas > 0),
    status    public.status_meja NOT NULL DEFAULT 'tersedia'
);

CREATE TABLE IF NOT EXISTS public.pedagang (
    id_pedagang    integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_pengguna    integer NOT NULL UNIQUE REFERENCES public.pengguna(id_pengguna),
    nama_tenant    varchar(150) NOT NULL,
    kategori_lapak varchar(100) NOT NULL,
    ukuran_tenant  public.ukuran_tenant NOT NULL DEFAULT 'sedang',
    nomor_telepon  varchar(30),
    status_toko    public.status_toko NOT NULL DEFAULT 'buka',
    logo           bytea,
    logo_mime      varchar(50)
);

CREATE TABLE IF NOT EXISTS public.menu (
    id_menu     integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_pedagang integer NOT NULL REFERENCES public.pedagang(id_pedagang),
    nama_menu   varchar(150) NOT NULL,
    kategori    varchar(100) NOT NULL,
    harga       numeric NOT NULL CHECK (harga >= 0),
    stok        integer NOT NULL DEFAULT 0 CHECK (stok >= 0),
    gambar      bytea,
    gambar_mime varchar(50),
    CONSTRAINT uq_menu_pedagang UNIQUE (id_menu, id_pedagang)
);

CREATE TABLE IF NOT EXISTS public.pesanan (
    id_pesanan     integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    kode_pesanan   varchar(50) NOT NULL UNIQUE,
    id_pengguna    integer REFERENCES public.pengguna(id_pengguna),
    nama_pemesan   varchar(100),
    id_meja        integer REFERENCES public.meja(id_meja),
    id_pedagang    integer NOT NULL REFERENCES public.pedagang(id_pedagang),
    tgl_pesanan    timestamptz NOT NULL DEFAULT now(),
    total_harga    numeric NOT NULL DEFAULT 0,
    status_pesanan public.status_pesanan NOT NULL DEFAULT 'menunggu',
    tgl_selesai    timestamptz,
    CONSTRAINT uq_pesanan_pedagang UNIQUE (id_pesanan, id_pedagang)
);

CREATE TABLE IF NOT EXISTS public.item_pesanan (
    id_item_pesanan integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_pesanan      integer NOT NULL,
    id_menu         integer NOT NULL,
    id_pedagang     integer NOT NULL,
    nama_menu       varchar(150) NOT NULL,
    jumlah          integer NOT NULL CHECK (jumlah > 0),
    harga_satuan    numeric NOT NULL,
    subtotal_item   numeric NOT NULL,
    catatan         text,
    CONSTRAINT fk_item_pesanan FOREIGN KEY (id_pesanan, id_pedagang)
        REFERENCES public.pesanan (id_pesanan, id_pedagang),
    CONSTRAINT fk_item_menu FOREIGN KEY (id_menu, id_pedagang)
        REFERENCES public.menu (id_menu, id_pedagang)
);

CREATE TABLE IF NOT EXISTS public.pembayaran (
    id_pembayaran     integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_pesanan        integer NOT NULL UNIQUE REFERENCES public.pesanan(id_pesanan),
    metode_pembayaran public.metode_pembayaran NOT NULL,
    jumlah_bayar      numeric NOT NULL,
    status_pembayaran public.status_pembayaran NOT NULL DEFAULT 'pending',
    dibuat_pada       timestamptz NOT NULL DEFAULT now(),
    kedaluwarsa_pada  timestamptz,
    tgl_pembayaran    timestamptz
);

-- Session PHP disimpan di database
CREATE TABLE IF NOT EXISTS public.php_sessions (
    id         varchar(128) PRIMARY KEY,
    data       text NOT NULL DEFAULT '',
    updated_at timestamptz NOT NULL DEFAULT now()
);

-- ---------- 3. Untuk database yang SUDAH ada ----------
-- (a) Tambah kolom gambar bila tabel dibuat dari versi lama
ALTER TABLE public.pedagang ADD COLUMN IF NOT EXISTS logo        bytea;
ALTER TABLE public.pedagang ADD COLUMN IF NOT EXISTS logo_mime   varchar(50);
ALTER TABLE public.menu     ADD COLUMN IF NOT EXISTS gambar      bytea;
ALTER TABLE public.menu     ADD COLUMN IF NOT EXISTS gambar_mime varchar(50);

-- (b) Buang fitur sewa. PERHATIAN: data di tabel sewa_lapak akan terhapus permanen.
DROP TABLE IF EXISTS public.sewa_lapak;
DROP TYPE  IF EXISTS public.status_sewa;

-- ---------- 4. Index pendukung ----------
CREATE INDEX IF NOT EXISTS idx_pesanan_pedagang_status ON public.pesanan (id_pedagang, status_pesanan);
CREATE INDEX IF NOT EXISTS idx_menu_pedagang           ON public.menu (id_pedagang);
CREATE INDEX IF NOT EXISTS idx_item_pesanan_pesanan    ON public.item_pesanan (id_pesanan);

-- ---------- 5. Akun admin ----------
-- Password HARUS berupa hash (crypt), bukan teks biasa. Aman dijalankan berulang:
-- jika username 'admin' sudah ada, password-nya diganti dengan hash baru.
CREATE EXTENSION IF NOT EXISTS pgcrypto;

INSERT INTO public.pengguna (nama_pengguna, email_pengguna, kata_sandi, nomor_telepon, peran)
VALUES ('admin', 'ahhihihihihi55@gmail.com', crypt('admin123', gen_salt('bf')), '088888888888', 'admin')
ON CONFLICT (nama_pengguna) DO UPDATE
    SET kata_sandi = EXCLUDED.kata_sandi,
        peran      = 'admin';

-- ---------- 6. Pemeriksaan (opsional) ----------
-- Harus tampil 8 tabel dan TIDAK ada sewa_lapak:
SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name;
-- Hash admin harus diawali $2a$:
SELECT nama_pengguna, peran, left(kata_sandi, 4) AS awal_hash FROM public.pengguna WHERE peran = 'admin';
