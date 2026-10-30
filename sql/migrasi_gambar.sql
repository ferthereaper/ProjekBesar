-- Menambah kolom gambar (logo lapak & foto menu). Aman dijalankan berulang.
ALTER TABLE public.pedagang ADD COLUMN IF NOT EXISTS logo        bytea;
ALTER TABLE public.pedagang ADD COLUMN IF NOT EXISTS logo_mime   varchar(50);
ALTER TABLE public.menu     ADD COLUMN IF NOT EXISTS gambar      bytea;
ALTER TABLE public.menu     ADD COLUMN IF NOT EXISTS gambar_mime varchar(50);
