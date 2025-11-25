-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 06:42 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `epiket_smekda`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `status` enum('hadir','izin','sakit','alpha') DEFAULT 'alpha',
  `photo_proof` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(11) NOT NULL,
  `class_name` varchar(20) NOT NULL,
  `grade` int(11) NOT NULL,
  `major` varchar(50) NOT NULL,
  `class_number` int(11) NOT NULL,
  `homeroom_teacher_id` int(11) DEFAULT NULL,
  `academic_year` varchar(9) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `class_name`, `grade`, `major`, `class_number`, `homeroom_teacher_id`, `academic_year`, `is_active`, `created_at`) VALUES
(1, 'XI RPL 1', 11, 'Rekayasa Perangkat Lunak', 1, 3, '2024/2025', 1, '2025-10-14 11:43:56');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `permission_type` enum('izin','sakit') NOT NULL,
  `reason` text NOT NULL,
  `letter_file` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `schedule_date` date NOT NULL,
  `day_name` varchar(10) NOT NULL,
  `shift` enum('pagi','siang') DEFAULT 'pagi',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'school_name', 'SMKN 2 SURABAYA', 'Nama Sekolah', '2025-10-14 11:43:57'),
(2, 'app_name', 'E-piket SMEKDA', 'Nama Aplikasi', '2025-10-14 11:43:57'),
(3, 'academic_year', '2024/2025', 'Tahun Ajaran Aktif', '2025-10-14 11:43:57'),
(4, 'check_in_start', '06:00', 'Waktu Mulai Absen Masuk', '2025-10-20 01:04:38'),
(5, 'check_in_end', '06:30', 'Waktu Batas Absen Masuk', '2025-10-20 01:04:38'),
(6, 'check_out_start', '15:00', 'Waktu Mulai Absen Pulang', '2025-10-20 01:04:38'),
(7, 'check_out_end', '15:30', 'Waktu Batas Absen Pulang', '2025-10-20 01:04:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  `nis` varchar(20) DEFAULT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT 'default.jpg',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `nis`, `nip`, `email`, `phone`, `class_id`, `profile_photo`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'Administrator SMEKDA', 'admin', NULL, NULL, 'admin@smkn2sby.sch.id', NULL, NULL, 'default.jpg', 1, '2025-10-14 11:43:55', '2025-10-14 11:43:55'),
(2, 'guru001', '9310f83135f238b04af729fec041cca8', 'meli, S.Pd', 'guru', NULL, '198501012010011001', 'meli@smkn2sby.sch.id', '', NULL, 'default.jpg', 1, '2025-10-14 11:43:56', '2025-11-03 03:55:58'),
(3, 'guru002', '9310f83135f238b04af729fec041cca8', 'Ida, S.Kom', 'guru', NULL, '199002022012012002', 'Ida@smkn2sby.sch.id', '081234567891', NULL, 'default.jpg', 1, '2025-10-14 11:43:56', '2025-10-14 11:53:57'),
(4, 'guru003', '9310f83135f238b04af729fec041cca8', 'suwon, S.T', 'guru', NULL, '198803032015011003', 'suwon@smkn2sby.sch.id', '081234567892', NULL, 'default.jpg', 1, '2025-10-14 11:43:56', '2025-10-20 00:55:58'),
(12, 'muna123', '202cb962ac59075b964b07152d234b70', 'muna', 'guru', NULL, '08327357203', 'drlgieorghsg@gmail.com', '085374235235', NULL, 'default.jpg', 1, '2025-11-03 03:56:39', '2025-11-03 03:56:39'),
(14, '33904', 'b11712a557efbc1dda47d9024b28fc78', 'Aditya Ramadhani Putra Pratama', 'siswa', '33904', NULL, 'adit@student.smkn2sby.sch.id', '085819820260', 1, 'default.jpg', 1, '2025-11-11 04:57:38', '2025-11-11 04:57:38'),
(15, '33905', 'a96683574013404fbdc72bcb5f4c80e7', 'Afrizal Nuril Firmansyah', 'siswa', '33905', NULL, 'rizal@student.smkn2sby.sch.id', '083857608623', 1, 'default.jpg', 1, '2025-11-11 04:59:25', '2025-11-11 04:59:25'),
(16, '33907', '3e0ccc57136661028ea5f440ed4e53d8', 'Andre Abdililah Ahwien', 'siswa', '33907', NULL, 'andre@student.smkn2sby.sch.id', '081336019251', 1, 'default.jpg', 1, '2025-11-11 05:00:51', '2025-11-12 09:31:36'),
(17, '33908', 'bf56d3ff4ea20391eeb73af2dc7e0d07', 'Ardila Ayuna Lestari', 'siswa', '33908', NULL, 'ardila@student.smkn2sby.sch.id', '083151567268', 1, 'default.jpg', 1, '2025-11-11 05:02:08', '2025-11-12 09:31:50'),
(18, '33909', '53abec67fb016be5f431dda0e1fe7473', 'Aurel Xaviera Suroso', 'siswa', '33909', NULL, 'aurel@student.smkn2sby.sch.id', '089661475040', 1, 'default.jpg', 1, '2025-11-12 09:33:13', '2025-11-12 09:33:13'),
(19, '33910', 'b42c89dec51b42acdff36745c8a4109a', 'Axlendra Haris Sanjaya', 'siswa', '33910', NULL, 'axle@student.smkn2sby.sch.id', '082192553756', 1, 'default.jpg', 1, '2025-11-12 09:35:08', '2025-11-12 09:35:08'),
(20, '33912', 'b3bf93c3f758b0e8881c3573883adcf9', 'Bima Mulana Hermanto', 'siswa', '33912', NULL, 'bima@student.smkn2sby.sch.id', '082338196267', 1, 'default.jpg', 1, '2025-11-12 09:36:42', '2025-11-12 09:36:42'),
(21, '33913', '0712bc453c98649bbc39b4f2117eef9f', 'Chika Aulia Fitri Oktavilla', 'siswa', '33913', NULL, 'chika@student.smkn2sby.sch.id', '0895803111983', 1, 'default.jpg', 1, '2025-11-12 09:40:12', '2025-11-12 09:40:12'),
(22, '33914', 'fc8d5986a039ea16ecfd79ac1c20a0b1', 'Destria Angelyne', 'siswa', '33914', NULL, 'destria@student.smkn2sby.sch.id', '0859171776488', 1, 'default.jpg', 1, '2025-11-12 09:42:06', '2025-11-12 09:42:06'),
(23, '33915', '09add3fd59925533c1bfa9c3048f5b96', 'Dzani Sanam Romansyah', 'siswa', '33915', NULL, 'dzani@student.smkn2sby.sch.id', '082331745576', 1, 'default.jpg', 1, '2025-11-12 09:44:01', '2025-11-12 09:44:01'),
(24, '33916', '973e871484054975ff69b4d23627e376', 'Faathirta Tri Tedsa', 'siswa', '33916', NULL, 'faathirta@student.smkn2sby.sch.id', '082140811418', 1, 'default.jpg', 1, '2025-11-12 09:46:25', '2025-11-12 09:46:25'),
(25, '33917', 'aba05e9ba7fdfe0164049eb9bfd495cf', 'Faizuz Zahid Hermawan', 'siswa', '33917', NULL, 'faizuz@student.smkn2sby.sch.id', '088971677366', 1, 'default.jpg', 1, '2025-11-12 09:48:45', '2025-11-12 09:48:45'),
(26, '33918', 'bc7a74c066018430f37b39be3353ba0e', 'Farel Yusuf Yoga Saputra', 'siswa', '33918', NULL, 'farel@student.smkn2sby.sch.id', '089501214628', 1, 'default.jpg', 1, '2025-11-12 09:50:19', '2025-11-12 09:50:19'),
(27, '33919', '44485793cae806cfc853649f75b55b2b', 'Fitriyah', 'siswa', '33919', NULL, 'fitriyah@student.smkn2sby.sch.id', '088217642446', 1, 'default.jpg', 1, '2025-11-12 09:53:49', '2025-11-17 04:18:50'),
(28, '33920', 'a4d8904831cfd921f81dc279df02f6c1', 'Gilang Ramadhan', 'siswa', '33920', NULL, 'gilang@student.smkn2sby.sch.id', '081231793810', 1, 'default.jpg', 1, '2025-11-12 09:55:10', '2025-11-12 09:55:10'),
(29, '33921', '5f1517b532a2dd760f7d865e4d4146c6', 'Kesya Amalia', 'siswa', '33921', NULL, 'kesya@student.smkn2sby.sch.id', '085707238922', 1, 'default.jpg', 1, '2025-11-12 09:57:10', '2025-11-12 09:57:10'),
(30, '33922', '6e90b269c3c3e6c335e85045b865f1df', 'Kheyza Chantika Nadifia Pratama', 'siswa', '33922', NULL, 'kheyza@student.smkn2sby.sch.id', '08818491782', 1, 'default.jpg', 1, '2025-11-12 09:58:26', '2025-11-12 09:58:26'),
(31, '33923', '77b89821e0025bb611afe13ab29c6cad', 'Khirania Trisya Putri Damayanti', 'siswa', '33923', NULL, 'khirania@student.smkn2sby.sch.id', '089501216697', 1, 'default.jpg', 1, '2025-11-12 10:00:09', '2025-11-12 10:00:09'),
(32, '33924', 'eebf8c4112978252010dbe58d06ad568', 'Lazuardi Iman AKbar Rakhmadi', 'siswa', '33924', NULL, 'lazuardi@student.smkn2sby.sch.id', '085790311844', 1, 'default.jpg', 1, '2025-11-12 10:01:39', '2025-11-12 10:01:39'),
(33, '33925', '71b539f8a0207a307900ca15cbebc334', 'Marcelino Dhimas Andrian', 'siswa', '33925', NULL, 'marcelino@student.smkn2sby.sch.id', '085748009470', 1, 'default.jpg', 1, '2025-11-12 10:02:42', '2025-11-12 10:02:42');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_student_attendance_summary`
-- (See below for the actual view)
--
CREATE TABLE `view_student_attendance_summary` (
`student_id` int(11)
,`full_name` varchar(100)
,`nis` varchar(20)
,`class_name` varchar(20)
,`total_schedule` bigint(21)
,`total_hadir` decimal(22,0)
,`total_izin` decimal(22,0)
,`total_sakit` decimal(22,0)
,`total_alpha` decimal(22,0)
,`persentase_kehadiran` decimal(28,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_today_schedule`
-- (See below for the actual view)
--
CREATE TABLE `view_today_schedule` (
`id` int(11)
,`schedule_date` date
,`day_name` varchar(10)
,`shift` enum('pagi','siang')
,`student_name` varchar(100)
,`nis` varchar(20)
,`class_name` varchar(20)
,`attendance_status` varchar(11)
,`check_in_time` time
);

-- --------------------------------------------------------

--
-- Structure for view `view_student_attendance_summary`
--
DROP TABLE IF EXISTS `view_student_attendance_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_student_attendance_summary`  AS SELECT `u`.`id` AS `student_id`, `u`.`full_name` AS `full_name`, `u`.`nis` AS `nis`, `c`.`class_name` AS `class_name`, count(`a`.`id`) AS `total_schedule`, sum(case when `a`.`status` = 'hadir' then 1 else 0 end) AS `total_hadir`, sum(case when `a`.`status` = 'izin' then 1 else 0 end) AS `total_izin`, sum(case when `a`.`status` = 'sakit' then 1 else 0 end) AS `total_sakit`, sum(case when `a`.`status` = 'alpha' then 1 else 0 end) AS `total_alpha`, round(sum(case when `a`.`status` = 'hadir' then 1 else 0 end) / count(`a`.`id`) * 100,2) AS `persentase_kehadiran` FROM ((`users` `u` left join `classes` `c` on(`u`.`class_id` = `c`.`id`)) left join `attendances` `a` on(`u`.`id` = `a`.`student_id`)) WHERE `u`.`role` = 'siswa' GROUP BY `u`.`id`, `u`.`full_name`, `u`.`nis`, `c`.`class_name` ;

-- --------------------------------------------------------

--
-- Structure for view `view_today_schedule`
--
DROP TABLE IF EXISTS `view_today_schedule`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_today_schedule`  AS SELECT `s`.`id` AS `id`, `s`.`schedule_date` AS `schedule_date`, `s`.`day_name` AS `day_name`, `s`.`shift` AS `shift`, `u`.`full_name` AS `student_name`, `u`.`nis` AS `nis`, `c`.`class_name` AS `class_name`, coalesce(`a`.`status`,'belum_absen') AS `attendance_status`, `a`.`check_in_time` AS `check_in_time` FROM (((`schedules` `s` join `users` `u` on(`s`.`student_id` = `u`.`id`)) join `classes` `c` on(`s`.`class_id` = `c`.`id`)) left join `attendances` `a` on(`s`.`id` = `a`.`schedule_id` and `a`.`attendance_date` = `s`.`schedule_date`)) WHERE `s`.`schedule_date` = curdate() ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`student_id`,`attendance_date`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `verified_by` (`verified_by`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `homeroom_teacher_id` (`homeroom_teacher_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_schedule` (`student_id`,`schedule_date`,`shift`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`homeroom_teacher_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissions_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissions_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
