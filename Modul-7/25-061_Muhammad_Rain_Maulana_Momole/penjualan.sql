-- Database: penjualan (sesuai skema pada soal Modul 7 - Reporting)
CREATE DATABASE IF NOT EXISTS penjualan CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE penjualan;

DROP TABLE IF EXISTS pembayaran;
DROP TABLE IF EXISTS transaksi_detail;
DROP TABLE IF EXISTS transaksi;
DROP TABLE IF EXISTS barang;
DROP TABLE IF EXISTS supplier;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS user;

CREATE TABLE supplier (
  id int NOT NULL AUTO_INCREMENT,
  nama varchar(100) NOT NULL,
  telp varchar(12),
  alamat text,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE barang (
  id int NOT NULL AUTO_INCREMENT,
  kode_barang varchar(10) NOT NULL,
  nama_barang varchar(100) NOT NULL,
  harga int NOT NULL,
  stok int NOT NULL,
  supplier_id int,
  PRIMARY KEY (id),
  UNIQUE KEY (kode_barang),
  FOREIGN KEY (supplier_id) REFERENCES supplier(id)
) ENGINE=InnoDB;

CREATE TABLE pelanggan (
  id varchar(20) NOT NULL,
  nama varchar(100) NOT NULL,
  jenis_kelamin enum('L','P'),
  telp varchar(12),
  alamat text,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE transaksi (
  id int NOT NULL AUTO_INCREMENT,
  waktu_transaksi date NOT NULL,
  keterangan text,
  total int NOT NULL,
  pelanggan_id varchar(20),
  PRIMARY KEY (id),
  FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id)
) ENGINE=InnoDB;

CREATE TABLE transaksi_detail (
  transaksi_id int NOT NULL,
  barang_id int NOT NULL,
  harga int NOT NULL,
  qty int NOT NULL,
  FOREIGN KEY (transaksi_id) REFERENCES transaksi(id),
  FOREIGN KEY (barang_id) REFERENCES barang(id)
) ENGINE=InnoDB;

CREATE TABLE pembayaran (
  id int NOT NULL AUTO_INCREMENT,
  waktu_bayar datetime,
  total int NOT NULL,
  metode enum('TUNAI','TRANSFER','EDC'),
  transaksi_id int NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (transaksi_id) REFERENCES transaksi(id)
) ENGINE=InnoDB;

CREATE TABLE user (
  id_user tinyint NOT NULL AUTO_INCREMENT,
  username varchar(30) NOT NULL,
  password varchar(35) NOT NULL,
  nama varchar(50),
  alamat varchar(150),
  hp varchar(20),
  level tinyint(1),
  PRIMARY KEY (id_user),
  UNIQUE KEY (username)
) ENGINE=InnoDB;

-- ===== DATA CONTOH =====
INSERT INTO supplier (nama, telp, alamat) VALUES
('PT Sumber Makmur', '081234567890', 'Jl. Merdeka No. 1, Bandung');

INSERT INTO barang (kode_barang, nama_barang, harga, stok, supplier_id) VALUES
('BRG001', 'Kopi Susu 250ml', 13000, 100, 1),
('BRG002', 'Teh Botol 350ml', 6000, 100, 1);

INSERT INTO pelanggan (id, nama, jenis_kelamin, telp, alamat) VALUES
('P001','Andi Saputra','L','081111111111','Bandung'),
('P002','Budi Santoso','L','081111111112','Bandung'),
('P003','Citra Dewi','P','081111111113','Cimahi'),
('P004','Dina Lestari','P','081111111114','Bandung'),
('P005','Eko Prasetyo','L','081111111115','Lembang'),
('P006','Fitri Handayani','P','081111111116','Bandung'),
('P007','Gilang Ramadhan','L','081111111117','Cimahi'),
('P008','Hana Putri','P','081111111118','Bandung'),
('P009','Indra Wijaya','L','081111111119','Bandung');

-- 13 transaksi x Rp13.000 => total Rp169.000, 9 pelanggan unik (cocok dengan Gambar 1)
INSERT INTO transaksi (id, waktu_transaksi, keterangan, total, pelanggan_id) VALUES
(1,'2023-09-01','Penjualan',13000,'P001'),
(2,'2023-09-01','Penjualan',13000,'P002'),
(3,'2023-09-01','Penjualan',13000,'P003'),
(4,'2023-09-02','Penjualan',13000,'P004'),
(5,'2023-09-02','Penjualan',13000,'P005'),
(6,'2023-09-02','Penjualan',13000,'P001'),
(7,'2023-09-02','Penjualan',13000,'P006'),
(8,'2023-09-03','Penjualan',13000,'P007'),
(9,'2023-09-04','Penjualan',13000,'P008'),
(10,'2023-09-05','Penjualan',13000,'P009'),
(11,'2023-09-05','Penjualan',13000,'P002'),
(12,'2023-09-06','Penjualan',13000,'P003'),
(13,'2023-09-09','Penjualan',13000,'P004');

INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty)
SELECT id, 1, 13000, 1 FROM transaksi;

INSERT INTO pembayaran (waktu_bayar, total, metode, transaksi_id)
SELECT CONCAT(waktu_transaksi, ' 12:00:00'), total, 'TUNAI', id FROM transaksi;

-- password: admin (MD5)
INSERT INTO user (username, password, nama, alamat, hp, level) VALUES
('admin', '21232f297a57a5a743894a0e4a801fc3', 'Administrator', 'Bandung', '081200000000', 1);
