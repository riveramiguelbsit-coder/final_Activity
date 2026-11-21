SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `term` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `activities` (`id`, `title`, `term`, `description`, `created_at`) VALUES
(1, 'FINALS Lab Exercises #1 JSON', 'Midterm', 'JSON exercises', '2026-11-21 04:41:40'),
(2, 'FINAL Lab Exercise 2 – Exploring XMLHttpRequest', 'Midterm', 'XML http Request', '2026-11-21 04:46:38'),
(3, 'FINAL Lab Exercise 3 – Exploring the Fetch API', 'Midterm', 'Fetch API', '2026-11-21 04:47:44'),
(4, 'FInals Lab #4 Lab Activity: Exploring XML', 'Midterm', 'XML', '2026-11-21 04:48:34');

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `students` (`id`, `fullname`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'MIGUEL O. RIVERA', 'Student005', 'riveramiguel_bsit@plmun.edu.ph', '$2y$12$SnWYBVE24Lg94s.4SSAZheRkW0SWou0c1ORBncwbixAjY5GVu9ULW', '2026-11-21 04:13:03');

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `submissions` (`id`, `student_id`, `activity_id`, `file_name`, `file_path`, `submitted_at`) VALUES
(1, 1, 1, 'Lab-1-JSON-main.zip', 'uploads/midterm/70e9d9572dddb805597fbb0e408a29f5.zip', '2026-11-21 04:41:40'),
(2, 1, 2, 'FINAL-Lab-Exercise-2-Exploring-XMLHttpRequest-main.zip', 'uploads/midterm/52abe26b2b0b40eec877f6dc902b8148.zip', '2026-11-21 04:46:38'),
(3, 1, 3, 'FINAL-Lab-Exercise-3-Exploring-the-Fetch-API-main.zip', 'uploads/midterm/bbcf970e6d562706dbcd82f820d7a007.zip', '2026-11-21 04:47:44'),
(4, 1, 4, 'FInals-Lab-4-Lab-Activity-Exploring-XML-main.zip', 'uploads/midterm/e84434bacafa83e596213a9e6615d819.zip', '2026-11-21 04:48:34');


ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);


ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;


ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

