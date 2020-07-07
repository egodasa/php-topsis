-- Adminer 4.7.5 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `tbl_alternatif`;
CREATE TABLE `tbl_alternatif` (
  `id_alternatif` int(11) NOT NULL AUTO_INCREMENT,
  `id_bantuan` int(11) NOT NULL,
  `nm_alternatif` varchar(50) NOT NULL,
  `jumlah_siswa` int(11) NOT NULL,
  PRIMARY KEY (`id_alternatif`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `tbl_alternatif` (`id_alternatif`, `id_bantuan`, `nm_alternatif`, `jumlah_siswa`) VALUES
(12,	7,	'TK Martondi',	32),
(13,	7,	'TK ABA 2',	106),
(14,	7,	'TK Haholongan',	123),
(15,	7,	'KB Martondi',	20),
(16,	7,	'TK Sari Putra',	67);

DROP TABLE IF EXISTS `tbl_bantuan`;
CREATE TABLE `tbl_bantuan` (
  `id_bantuan` int(11) NOT NULL AUTO_INCREMENT,
  `nm_bantuan` varchar(50) NOT NULL,
  `besar_bantuan` int(11) NOT NULL,
  `satuan` varchar(50) NOT NULL,
  PRIMARY KEY (`id_bantuan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `tbl_bantuan` (`id_bantuan`, `nm_bantuan`, `besar_bantuan`, `satuan`) VALUES
(7,	'Dana  BOP Pusat',	200000000,	'juta');

DROP TABLE IF EXISTS `tbl_hasil_perhitungan`;
CREATE TABLE `tbl_hasil_perhitungan` (
  `id_hasil` int(11) NOT NULL AUTO_INCREMENT,
  `id_bantuan` int(11) NOT NULL,
  `id_alternatif` int(11) NOT NULL,
  `nilai` float NOT NULL,
  PRIMARY KEY (`id_hasil`),
  KEY `id_bantuan` (`id_bantuan`),
  KEY `id_alternatif` (`id_alternatif`),
  CONSTRAINT `tbl_hasil_perhitungan_ibfk_3` FOREIGN KEY (`id_bantuan`) REFERENCES `tbl_bantuan` (`id_bantuan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `tbl_hasil_perhitungan_ibfk_4` FOREIGN KEY (`id_alternatif`) REFERENCES `tbl_alternatif` (`id_alternatif`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `tbl_hasil_perhitungan` (`id_hasil`, `id_bantuan`, `id_alternatif`, `nilai`) VALUES
(300,	7,	12,	0.387612),
(301,	7,	13,	0.847119),
(302,	7,	14,	0.673619),
(303,	7,	15,	0.190577),
(304,	7,	16,	0.847119);

DROP TABLE IF EXISTS `tbl_kriteria`;
CREATE TABLE `tbl_kriteria` (
  `id_kriteria` int(11) NOT NULL AUTO_INCREMENT,
  `nm_kriteria` varchar(50) NOT NULL,
  `jenis` varchar(50) NOT NULL,
  `bobot` int(11) NOT NULL,
  PRIMARY KEY (`id_kriteria`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `tbl_kriteria` (`id_kriteria`, `nm_kriteria`, `jenis`, `bobot`) VALUES
(4,	'Kelengkapan Berkas',	'Benefit',	5),
(5,	'Jumlah Siswa',	'Benefit',	5),
(6,	'Lama Berdiri',	'Benefit',	4),
(7,	'Jumlah Pengajar',	'Benefit',	3);

DROP TABLE IF EXISTS `tbl_perhitungan`;
CREATE TABLE `tbl_perhitungan` (
  `id_perhitungan` int(11) NOT NULL AUTO_INCREMENT,
  `id_bantuan` int(11) NOT NULL,
  `id_alternatif` int(11) NOT NULL,
  `c4` float NOT NULL,
  `c5` float NOT NULL,
  `c6` float NOT NULL,
  `c7` float NOT NULL,
  PRIMARY KEY (`id_perhitungan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `tbl_perhitungan` (`id_perhitungan`, `id_bantuan`, `id_alternatif`, `c4`, `c5`, `c6`, `c7`) VALUES
(1,	7,	12,	5,	3,	4,	4),
(2,	7,	13,	5,	5,	5,	4),
(3,	7,	14,	5,	5,	3,	5),
(4,	7,	15,	5,	2,	4,	3),
(5,	7,	16,	5,	5,	5,	4);

DROP TABLE IF EXISTS `tbl_pilihan_alternatif`;
CREATE TABLE `tbl_pilihan_alternatif` (
  `id_pilihan_alternatif` int(11) NOT NULL AUTO_INCREMENT,
  `id_alternatif` int(11) NOT NULL,
  `id_pilihan_kriteria` int(11) NOT NULL,
  PRIMARY KEY (`id_pilihan_alternatif`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `tbl_pilihan_alternatif` (`id_pilihan_alternatif`, `id_alternatif`, `id_pilihan_kriteria`) VALUES
(54,	13,	8),
(55,	13,	13),
(56,	13,	18),
(57,	13,	20),
(62,	12,	8),
(63,	12,	11),
(64,	12,	17),
(65,	12,	20),
(66,	14,	8),
(67,	14,	13),
(68,	14,	16),
(69,	14,	21),
(70,	15,	8),
(71,	15,	10),
(72,	15,	17),
(73,	15,	19),
(74,	16,	8),
(75,	16,	13),
(76,	16,	18),
(77,	16,	20);

DROP TABLE IF EXISTS `tbl_pilihan_kriteria`;
CREATE TABLE `tbl_pilihan_kriteria` (
  `id_pilihan_kriteria` int(11) NOT NULL AUTO_INCREMENT,
  `id_kriteria` int(11) NOT NULL,
  `nm_pilihan` varchar(50) NOT NULL,
  `nilai` tinyint(4) NOT NULL,
  PRIMARY KEY (`id_pilihan_kriteria`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `tbl_pilihan_kriteria` (`id_pilihan_kriteria`, `id_kriteria`, `nm_pilihan`, `nilai`) VALUES
(4,	4,	'0 Berkas',	1),
(5,	4,	'1 Berkas',	2),
(6,	4,	'>1 Berkas',	3),
(7,	4,	'>2 Berkas',	4),
(8,	4,	'>3 Berkas',	5),
(9,	5,	'1 - 12 Siswa',	1),
(10,	5,	'13 - 24 Siswa',	2),
(11,	5,	'25 - 35 Siswa',	3),
(12,	5,	'36 - 47 Siswa',	4),
(13,	5,	'>46 Siswa',	5),
(14,	6,	'1 Tahun',	1),
(15,	6,	'1.1 - 3 Tahun',	2),
(16,	6,	'4 - 7 Tahun',	3),
(17,	6,	'8 - 10 Tahun',	4),
(18,	6,	'>10 Tahun',	5),
(19,	7,	'1 - 3 Guru',	3),
(20,	7,	'4 - 6 Guru',	4),
(21,	7,	'7 - 12 Guru',	5);

DROP TABLE IF EXISTS `tbl_user`;
CREATE TABLE `tbl_user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(35) NOT NULL,
  `username` varchar(35) NOT NULL,
  `password` varchar(35) NOT NULL,
  `level` varchar(35) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `tbl_user` (`id_user`, `nama_lengkap`, `username`, `password`, `level`) VALUES
(1,	'irpanda',	'panda',	'panda',	'admin');

-- 2020-07-07 03:14:32
