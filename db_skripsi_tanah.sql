SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_skripsi_tanah`
--

CREATE TABLE `detail_log` (
  `id_detail` int(11) NOT NULL,
  `id_sesi` int(11) NOT NULL,
  `id_soal` int(11) NOT NULL,
  `jawaban_siswa` varchar(100) DEFAULT NULL,
  `status_jawaban` enum('benar','salah') NOT NULL,
  `level_saat_itu` enum('mudah','sedang','susah') NOT NULL,
  `waktu_jawab` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `kelas` (
  `id_kelas` int(11) NOT NULL,
  `nama_kelas` varchar(20) NOT NULL,
  `id_guru` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `id_guru`) VALUES
(1, '10A', 2);

CREATE TABLE `log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `aksi` varchar(50) NOT NULL,
  `keterangan` text NOT NULL,
  `waktu` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `log_eksplorasi` (
  `id_log` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `waktu_mulai` datetime DEFAULT current_timestamp(),
  `waktu_selesai` datetime DEFAULT NULL,
  `durasi_detik` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `log_sesi` (
  `id_sesi` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `tanggal` datetime DEFAULT current_timestamp(),
  `skor_akhir` int(11) DEFAULT 0,
  `status` enum('berjalan','selesai') DEFAULT 'berjalan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `materi` (
  `id_materi` int(11) NOT NULL,
  `sensor_ke` int(11) NOT NULL,
  `judul_lapisan` varchar(100) NOT NULL,
  `isi_penjelasan` text NOT NULL,
  `file_audio` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `materi` (`id_materi`, `sensor_ke`, `judul_lapisan`, `isi_penjelasan`, `file_audio`) VALUES
(1, 1, 'Lapisan Organik (O)', 'Horizon O atau horizon organik adalah lapisan tanah paling atas yang berasal dari berbagai sisa material organik, seperti dedaunan atau jasad tumbuhan dan hewan. Horizon organik banyak ditemukan di hutan yang masih alami. Horizon O ini mengandung bahan organik lebih dari 20 persen.', 'assets/audio/materi_1.mp3'),
(2, 2, 'Lapisan Topsoil (A)', 'Horizon A terdiri dari material organik berwarna gelap yang disebut humus. Humus (bunga tanah) berasal dari sisa atau jasad material organik yang telah membusuk. Horizon ini ditandai dengan adanya akar tumbuhan. Horizon A juga terdiri dari material organik yang tercampur dengan butiran mineral karena aktivitas organisme.', 'assets/audio/materi_2.mp3'),
(3, 3, 'Lapisan Eluviasi (E)', 'Horizon E atau horizon eluvial adalah lapisan tanah yang berwarna lebih terang akibat pencucian intensif oleh air. Akibat pencucian, kadar bahan organik dan mineral silikat, seperti Fe (ferro=besi) dan Al (aluminium), menjadi sangat rendah sehingga lapisan ini didominasi oleh mineral resisten lain, seperti kuarsa yang berwarna terang.', 'assets/audio/materi_3.mp3'),
(4, 4, 'Lapisan Subsoil (B)', 'Horizon B adalah lapisan material hasil pencucian di horizon eluvial yang terkumpul. Horizon ini kaya akan mineral, tetapi hanya memiliki sedikit materi organik.', 'assets/audio/materi_4.mp3'),
(5, 5, 'Lapisan Bahan Induk (C)', 'Horizon C adalah lapisan tanah terbawah. Horizon ini terdiri atas batuan induk yang mulai menunjukkan tanda-tanda pelapukan. Horizon ini berupa sedimen yang belum memadat sehingga akar tanaman tidak dapat menembus. Bahan organik sangat sedikit ditemukan pada horizon ini.', 'assets/audio/materi_5.mp3'),
(6, 6, 'Lapisan Batuan Dasar (R)', 'Horizon D atau R adalah horizon tempat batuan induk yang belum mengalami pelapukan berada.', 'assets/audio/materi_6.mp3');

CREATE TABLE `siswa` (
  `id_siswa` int(11) NOT NULL,
  `rfid_uid` varchar(50) NOT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `id_kelas` int(11) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `siswa` (`id_siswa`, `rfid_uid`, `nis`, `nama_siswa`, `id_kelas`, `jenis_kelamin`) VALUES
(1, '123456789012', '10001', 'Siswa Contoh', 1, 'L');

CREATE TABLE `soal` (
  `id_soal` int(11) NOT NULL,
  `pertanyaan_teks` text NOT NULL,
  `file_audio` varchar(255) NOT NULL,
  `level` enum('mudah','sedang','susah') NOT NULL,
  `jenis_input` enum('sentuh','suara') NOT NULL,
  `kunci_jawaban` varchar(100) NOT NULL,
  `penjelasan` text DEFAULT NULL,
  `id_guru` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `soal` (`id_soal`, `pertanyaan_teks`, `file_audio`, `level`, `jenis_input`, `kunci_jawaban`, `penjelasan`, `id_guru`) VALUES
(23, 'Apakah sebutan untuk material yang berasal dari sisa tumbuhan dan hewan pada lapisan tanah paling atas?', '', 'mudah', 'suara', 'organik', 'Jawaban benar adalah organik karena Horizon O merupakan lapisan organik.', 2),
(24, 'Manakah horizon yang mengandung bahan organik lebih dari 20 persen?', '', 'mudah', 'sentuh', 'sensor_0', 'Jawaban benar adalah Horizon O karena mengandung bahan organik lebih dari 20%.', 2),
(25, 'Manakah horizon yang terdiri dari material organik berwarna gelap yang disebut humus?', '', 'mudah', 'sentuh', 'sensor_1', 'Jawaban benar adalah Horizon A karena merupakan lapisan humus.', 2),
(26, 'Manakah lapisan yang disebut sebagai horizon eluvial?', '', 'sedang', 'sentuh', 'sensor_2', 'Jawaban benar adalah Horizon E karena disebut horizon eluvial.', 2),
(27, 'Manakah lapisan yang menjadi tempat penumpukan mineral dari proses pencucian?', '', 'sedang', 'sentuh', 'sensor_3', 'Jawaban benar adalah Horizon B karena merupakan tempat penumpukan hasil pencucian.', 2),
(28, 'Manakah lapisan tanah terbawah yang mulai menunjukkan tanda-tanda pelapukan?', '', 'sedang', 'sentuh', 'sensor_4', 'Jawaban benar adalah Horizon C karena mulai menunjukkan pelapukan.', 2),
(29, 'Manakah horizon tempat batuan induk yang belum mengalami pelapukan berada?', '', 'mudah', 'sentuh', 'sensor_5', 'Jawaban benar adalah Horizon D/R karena merupakan batuan induk yang belum lapuk.', 2),
(30, 'Apakah nama material organik berwarna gelap pada Horizon A?', '', 'mudah', 'suara', 'humus', 'Jawaban benar adalah humus karena Horizon A terdiri dari humus.', 2),
(31, 'Manakah horizon yang ditandai dengan adanya akar tumbuhan?', '', 'mudah', 'sentuh', 'sensor_1', 'Jawaban benar adalah Horizon A karena memiliki akar tumbuhan.', 2),
(32, 'Manakah lapisan yang mengalami pencucian intensif oleh air?', '', 'sedang', 'sentuh', 'sensor_2', 'Jawaban benar adalah Horizon E karena mengalami pencucian intensif.', 2),
(33, 'Mineral apakah yang mendominasi Horizon E?', '', 'susah', 'suara', 'kuarsa', 'Jawaban benar adalah kuarsa karena Horizon E didominasi mineral kuarsa.', 2),
(34, 'Manakah horizon yang kaya mineral tetapi hanya memiliki sedikit bahan organik?', '', 'sedang', 'sentuh', 'sensor_3', 'Jawaban benar adalah Horizon B karena kaya mineral namun sedikit bahan organik.', 2),
(35, 'Manakah lapisan yang tidak dapat ditembus oleh akar tanaman?', '', 'sedang', 'sentuh', 'sensor_4', 'Jawaban benar adalah Horizon C karena berupa sedimen belum memadat.', 2),
(36, 'Hutan seperti apakah yang banyak memiliki lapisan tanah organik?', '', 'mudah', 'suara', 'alami', 'Jawaban benar adalah alami karena Horizon O banyak ditemukan di hutan alami.', 2),
(37, 'Manakah horizon yang terdiri atas sedimen yang belum memadat?', '', 'sedang', 'sentuh', 'sensor_4', 'Jawaban benar adalah Horizon C karena berupa sedimen belum memadat.', 2),
(38, 'Berapa persen batas kandungan bahan organik yang disebutkan pada Horizon O?', '', 'mudah', 'suara', '20', 'Jawaban benar adalah duapuluh karena Horizon O mengandung bahan organik lebih dari 20 persen.', 2),
(39, 'Manakah horizon yang berwarna lebih terang akibat pencucian?', '', 'sedang', 'sentuh', 'sensor_2', 'Jawaban benar adalah Horizon E karena berwarna lebih terang akibat pencucian.', 2),
(40, 'Manakah horizon yang menjadi tempat penumpukan hasil eluviasi?', '', 'susah', 'sentuh', 'sensor_3', 'Jawaban benar adalah Horizon B karena merupakan tempat illuviasi.', 2),
(41, 'Apakah nama unsur yang dilambangkan dengan Fe pada Horizon E?', '', 'susah', 'suara', 'besi', 'Jawaban benar adalah besi karena Fe adalah simbol unsur besi.', 2),
(42, 'Apakah nama unsur yang dilambangkan dengan Al pada Horizon E?', '', 'susah', 'suara', 'aluminium', 'Jawaban benar adalah aluminium karena Al adalah simbol unsur aluminium.', 2),
(43, 'Manakah lapisan tanah yang disebut tanah humus?', '', 'mudah', 'sentuh', 'sensor_1', 'Jawaban benar adalah Horizon A karena merupakan lapisan humus.', 2),
(44, 'Manakah lapisan tanah yang didominasi oleh mineral resisten?', '', 'susah', 'sentuh', 'sensor_2', 'Jawaban benar adalah Horizon E karena didominasi mineral resisten.', 2),
(45, 'Manakah horizon tempat batuan induk mulai mengalami pelapukan?', '', 'sedang', 'sentuh', 'sensor_4', 'Jawaban benar adalah Horizon C karena mulai mengalami pelapukan.', 2),
(46, 'Manakah horizon yang paling sedikit mengandung bahan organik?', '', 'susah', 'sentuh', 'sensor_5', 'Jawaban benar adalah Horizon D/R karena berupa batuan induk.', 2),
(47, 'Apakah nama material yang berasal dari sisa organisme yang telah membusuk?', '', 'mudah', 'suara', 'humus', 'Jawaban benar adalah humus karena berasal dari organisme yang membusuk.', 2),
(48, 'Manakah horizon yang mengalami proses eluviasi?', '', 'susah', 'sentuh', 'sensor_2', 'Jawaban benar adalah Horizon E karena disebut horizon eluvial.', 2),
(49, 'Manakah lapisan yang merupakan hasil pengumpulan pencucian dari horizon eluvial?', '', 'susah', 'sentuh', 'sensor_3', 'Jawaban benar adalah Horizon B karena merupakan hasil pengumpulan pencucian.', 2),
(50, 'Manakah lapisan yang berada tepat di bawah Horizon A?', '', 'sedang', 'sentuh', 'sensor_2', 'Jawaban benar adalah Horizon E karena berada tepat di bawah Horizon A.', 2),
(51, 'Manakah horizon yang berada paling atas dalam struktur tanah?', '', 'mudah', 'sentuh', 'sensor_0', 'Jawaban benar adalah Horizon O karena berada paling atas.', 2),
(52, 'Manakah lapisan yang berupa batuan induk dan belum mengalami pelapukan?', '', 'mudah', 'sentuh', 'sensor_5', 'Jawaban benar adalah Horizon D/R karena belum mengalami pelapukan.', 2),
(53, 'Manakah horizon yang kaya bahan organik dan bercampur mineral?', '', 'sedang', 'sentuh', 'sensor_1', 'Jawaban benar adalah Horizon A karena terdiri dari organik dan mineral.', 2),
(54, 'Mineral apakah yang menyebabkan warna terang pada Horizon E?', '', 'susah', 'suara', 'kuarsa', 'Jawaban benar adalah kuarsa karena mendominasi Horizon E.', 2),
(55, 'Manakah lapisan tanah yang menjadi tempat aktivitas organisme tinggi?', '', 'sedang', 'sentuh', 'sensor_1', 'Jawaban benar adalah Horizon A karena banyak aktivitas organisme.', 2),
(56, 'Manakah horizon yang menjadi tempat penumpukan mineral hasil pencucian?', '', 'sedang', 'sentuh', 'sensor_3', 'Jawaban benar adalah Horizon B karena tempat akumulasi mineral.', 2),
(57, 'Manakah horizon yang langsung berada di atas batuan induk?', '', 'sedang', 'sentuh', 'sensor_4', 'Jawaban benar adalah Horizon C karena berada di atas batuan induk.', 2),
(58, 'Manakah lapisan tanah yang terdiri dari dedaunan dan jasad hewan?', '', 'mudah', 'sentuh', 'sensor_0', 'Jawaban benar adalah Horizon O karena berasal dari sisa organisme.', 2),
(59, 'Manakah horizon yang memiliki kandungan silikat rendah akibat pencucian?', '', 'susah', 'sentuh', 'sensor_2', 'Jawaban benar adalah Horizon E karena kehilangan silikat akibat pencucian.', 2),
(60, 'Manakah tanah yang memiliki sedikit materi organik tetapi kaya mineral?', '', 'sedang', 'sentuh', 'sensor_3', 'Jawaban benar adalah Horizon B karena kaya mineral.', 2),
(61, 'Manakah lapisan tanah yang menjadi sumber bahan induk?', '', 'sedang', 'sentuh', 'sensor_5', 'Jawaban benar adalah Horizon D/R karena merupakan bahan induk.', 2),
(62, 'Manakah horizon yang memiliki akar tumbuhan yang paling banyak?', '', 'sedang', 'sentuh', 'sensor_1', 'Jawaban benar adalah Horizon A karena banyak akar tumbuhan.', 2);

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','guru') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Akun default: admin (pass: admin123) & guru (pass: guru123)
INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator Sistem', 'admin'),
(2, 'guru', '9310f83135f238b04af729fec041cca8', 'Guru Geografi', 'guru');

ALTER TABLE `detail_log`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_sesi` (`id_sesi`),
  ADD KEY `id_soal` (`id_soal`);

ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`),
  ADD KEY `id_guru` (`id_guru`);

ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

ALTER TABLE `log_eksplorasi`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_siswa` (`id_siswa`);

ALTER TABLE `log_sesi`
  ADD PRIMARY KEY (`id_sesi`),
  ADD KEY `id_siswa` (`id_siswa`);

ALTER TABLE `materi`
  ADD PRIMARY KEY (`id_materi`);

ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `rfid_uid` (`rfid_uid`),
  ADD KEY `id_kelas` (`id_kelas`);

ALTER TABLE `soal`
  ADD PRIMARY KEY (`id_soal`),
  ADD KEY `id_guru` (`id_guru`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

ALTER TABLE `detail_log` MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `kelas` MODIFY `id_kelas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `log_aktivitas` MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `log_eksplorasi` MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `log_sesi` MODIFY `id_sesi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
ALTER TABLE `materi` MODIFY `id_materi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `siswa` MODIFY `id_siswa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `soal` MODIFY `id_soal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;
ALTER TABLE `users` MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `detail_log`
  ADD CONSTRAINT `detail_log_ibfk_1` FOREIGN KEY (`id_sesi`) REFERENCES `log_sesi` (`id_sesi`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_log_ibfk_2` FOREIGN KEY (`id_soal`) REFERENCES `soal` (`id_soal`) ON DELETE CASCADE;

ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

ALTER TABLE `log_eksplorasi`
  ADD CONSTRAINT `log_eksplorasi_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;

ALTER TABLE `log_sesi`
  ADD CONSTRAINT `log_sesi_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`) ON DELETE CASCADE;

ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE SET NULL;

ALTER TABLE `soal`
  ADD CONSTRAINT `soal_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;