-- Tambah kolom harga_total ke tabel reservations jika belum ada
ALTER TABLE `reservations` ADD COLUMN `harga_total` INT DEFAULT 0;

-- Update existing reservations dengan harga berdasarkan kapasitas meja
UPDATE reservations r
JOIN tables t ON r.meja_id = t.id
SET r.harga_total = t.kapasitas * 10000
WHERE r.harga_total = 0;
