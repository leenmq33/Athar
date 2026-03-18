-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Mar 18, 2026 at 10:31 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `athar_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `created_at`, `updated_at`) VALUES
(1, 'لين', 'محمد', 'Leen@gmail.com', '0504342634', '$2y$10$nJOBbM/zj4zuPCwFHXzmUeQh8fhbWASwZiha7JFH6K.oINNJ9Z.qe', '2026-03-10 23:05:31', '2026-03-12 12:50:49');

-- --------------------------------------------------------

--
-- Table structure for table `donation_cases`
--

CREATE TABLE `donation_cases` (
  `id` int(11) NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `other_category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'images/default-case.png',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by_admin_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donation_cases`
--

INSERT INTO `donation_cases` (`id`, `title`, `category`, `other_category`, `description`, `image_path`, `is_active`, `created_by_admin_id`, `created_at`, `updated_at`) VALUES
(5, 'دعم تعليم أبناء أسرة محدودة الدخل', 'تعليم', NULL, 'تعيش هذه الأسرة بظروف مادية صعبة ولديها ثلاثة أطفال في مراحل دراسية مختلفة, يواجه الأبناء صعوبة في توفير الكتب والادوات المدرسية التي تساعدهم في دراستهم, التبرع بكتب تعليمية أو مواد دراسية سيساعدهم على الاستمرار في تعليمهم وتحقيق طموحاتهم المستقبلية.', 'uploads/cases/1773375170_school.jpg', 1, 1, '2026-03-13 04:12:50', '2026-03-13 04:12:50'),
(6, 'أثاث أساسي لأسرة انتقلت إلى منزل جديد', 'أثاث', NULL, 'انتقلت هذه الأسرة مؤخرًا إلى منزل جديد بعد ظروف صعبة، ولكن المنزل يفتقر إلى بعض الأثاث الأساسي مثل الطاولات والكراسي والخزائنو التبرع بقطع أثاث بحالة جيدة سيساعد هذه الأسرة على توفير بيئة منزلية مريحة ومستقرة لأفرادها.', 'uploads/cases/1773375233_chair.jpg', 1, 1, '2026-03-13 04:13:53', '2026-03-13 04:13:53'),
(7, 'ملابس يومية لأفراد أسرة محتاجة', 'ملابس', NULL, 'تحتاج هذه الأسرة إلى ملابس يومية لأفرادها بسبب محدودية دخلهمو التبرع بملابس نظيفة وبحالة جيدة يمكن أن يساهم في تلبية احتياجاتهم الأساسية ويخفف من الأعباء المادية التي يواجهونها.', 'uploads/cases/1773375289_photo_2026-03-12_03-48-53.jpg', 0, 1, '2026-03-13 04:14:49', '2026-03-13 15:23:55'),
(8, 'ملابس شتوية لأسرة محتاجة', 'ملابس', NULL, 'مع انخفاض درجات الحرارة، تعاني هذه الأسرة من نقص في الملابس الشتوية التي تحميهم من البرد, التبرع بمعاطف أو ملابس شتوية بحالة جيدة سيساعدهم على الشعور بالدفء خلال فصل الشتاء.', 'uploads/cases/1773375348_coat.jpg', 0, 1, '2026-03-13 04:15:48', '2026-03-13 14:57:21'),
(9, 'أجهزة إلكترونية لدعم دراسة أبناء المحتاجين', 'أجهزة إلكترونية', NULL, 'يعاني أبناء هذه الأسرة من صعوبة في متابعة دراستهم بسبب عدم توفر جهاز إلكتروني في المنزل, التبرع بجهاز لابتوب أو جهاز لوحي بحالة جيدة سيمكنهم من متابعة الدروس وإكمال واجباتهم الدراسية بسهولة.', 'uploads/cases/1773375498_mobile.jpg', 0, 1, '2026-03-13 04:18:18', '2026-03-13 14:56:03'),
(10, 'ألعاب للأطفال في أسرة محدودة الدخل', 'ألعاب', NULL, 'لدى هذه الأسرة أطفال صغار لا يمتلكون ألعابًا تساعدهم على قضاء وقت ممتع في المنزل, التبرع بالألعاب مثل السيارات الصغيرة أو الدمى أو الألعاب التعليمية سيساعد في إدخال الفرح إلى قلوبهم ويوفر لهم لحظات ممتعة', 'uploads/cases/1773375646_play.jpg', 0, 1, '2026-03-13 04:20:46', '2026-03-13 14:56:55'),
(12, 'توفير أثاث أساسي لأسرة محتاجة', 'أثاث', NULL, 'تعيش هذه الأسرة في منزل بسيط وتحتاج إلى بعض قطع الأثاث الأساسية مثل طاولة طعام أو خزائن أو سرير, التبرعات بالأثاث المستعمل بحالة جيدة يمكن أن تساعدهم في تحسين ظروف معيشتهم اليومية.', 'uploads/cases/1773417190_table.jpg', 1, 1, '2026-03-13 15:49:21', '2026-03-13 15:53:10'),
(13, 'توفير ألعاب للأطفال من الأسر المحتاجة', 'ألعاب', NULL, 'تسعى هذه الحالة إلى توفير ألعاب للأطفال من الأسر المحتاجة لإدخال الفرح إلى قلوبهم, يمكن التبرع بالألعاب النظيفة والصالحة للاستخدام مثل الدمى أو الألعاب التعليمية.', 'uploads/cases/1773417040_play2.jpg', 1, 1, '2026-03-13 15:50:40', '2026-03-13 15:50:40'),
(14, 'احتياج ملابس لأفراد أسرة ذات الدخل المحدود', 'ملابس', NULL, 'تحتاج هذه الأسرة إلى ملابس يومية مناسبة لجميع أفرادها, يمكن التبرع بالملابس النظيفة وبحالة جيدة لمساعدة الأسرة في تلبية احتياجاتها الأساسية.', 'uploads/cases/1773417298_photo_2026-03-12_03-48-53.jpg', 1, 1, '2026-03-13 15:54:58', '2026-03-13 15:55:27'),
(15, 'توفير كتب تعليمية لأبناء أسرة محتاجة', 'تعليم', NULL, 'تحتاج هذه الأسرة إلى كتب تعليمية ومواد دراسية تساعد أبناءها على متابعة تعليمهم, التبرع بالكتب المفيدة أو القصص التعليمية يمكن أن يساهم في دعم تعلمهم وتطوير مهاراتهم.', 'uploads/cases/1773417431_book.jpg', 1, 1, '2026-03-13 15:57:11', '2026-03-13 15:57:11');

-- --------------------------------------------------------

--
-- Table structure for table `donation_requests`
--

CREATE TABLE `donation_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `case_id` int(11) DEFAULT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `other_category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','accepted','rejected','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `delivery_method` enum('pickup','dropoff') COLLATE utf8mb4_unicode_ci NOT NULL,
  `branch_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `street` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `building_number` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `reviewed_by_admin_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `donation_requests`
--

INSERT INTO `donation_requests` (`id`, `user_id`, `case_id`, `title`, `category`, `other_category`, `description`, `image_path`, `status`, `delivery_method`, `branch_name`, `city`, `street`, `building_number`, `pickup_date`, `reviewed_by_admin_id`, `created_at`, `updated_at`) VALUES
(1, 2, 7, 'ملابس يومية لأفراد أسرة محتاجة', 'ملابس', NULL, 'تحتاج هذه الأسرة إلى ملابس يومية لأفرادها بسبب محدودية دخلهمو التبرع بملابس نظيفة وبحالة جيدة يمكن أن يساهم في تلبية احتياجاتهم الأساسية ويخفف من الأعباء المادية التي يواجهونها.', 'uploads/cases/1773375289_photo_2026-03-12_03-48-53.jpg', 'rejected', 'pickup', NULL, 'الرياض', 'الملك سلمان', '51', '2026-08-03', 1, '2026-03-13 04:33:21', '2026-03-13 04:38:35'),
(2, 2, NULL, 'التبرع بملابس بحالة جيدة', 'ملابس', NULL, 'لدي مجموعة من الملابس بحالة جيدة لم أعد بحاجة إليها، وأرغب في التبرع بها ليستفيد منها أشخاص أو عائلات محتاجة. الملابس نظيفة وقابلة للاستخدام ويمكن أن تساعد في تلبية احتياجات الآخرين.', 'uploads/requests/1773376652_clothes.jpg', 'completed', 'dropoff', 'مركز أثر – فرع شمال الرياض (حي النرجس – طريق الأمير فيصل بن بندر)', NULL, NULL, NULL, NULL, 1, '2026-03-13 04:37:32', '2026-03-13 04:38:20'),
(3, 2, NULL, 'التبرع بألعاب أطفال', 'ألعاب', NULL, 'لدي عدد من ألعاب الأطفال التي لم يعد أطفالي يستخدمونها، وهي ما زالت بحالة جيدة, أرغب في التبرع بها حتى يتمكن أطفال آخرون من الاستمتاع بها والاستفادة منها.', 'uploads/requests/1773376796_play2.jpg', 'accepted', 'pickup', NULL, 'الرياض', 'الملك سلمان', '51', '2026-08-03', 1, '2026-03-13 04:39:56', '2026-03-13 04:40:06'),
(4, 3, 9, 'أجهزة إلكترونية لدعم دراسة أبناء المحتاجين', 'أجهزة إلكترونية', NULL, 'يعاني أبناء هذه الأسرة من صعوبة في متابعة دراستهم بسبب عدم توفر جهاز إلكتروني في المنزل, التبرع بجهاز لابتوب أو جهاز لوحي بحالة جيدة سيمكنهم من متابعة الدروس وإكمال واجباتهم الدراسية بسهولة.', 'uploads/cases/1773375498_mobile.jpg', 'completed', 'dropoff', 'مركز أثر – فرع وسط الرياض (حي الملز – طريق صلاح الدين الأيوبي)', NULL, NULL, NULL, NULL, 1, '2026-03-13 04:41:06', '2026-03-13 14:56:12'),
(16, 3, NULL, 'التبرع بجهاز الكتروني بحالة جيدة', 'أجهزة إلكترونية', NULL, 'لدي جهاز إلكتروني لم أعد أستخدمه وأرغب في التبرع به ليستفيد منه شخص آخر, الجهاز يعمل بشكل جيد ويمكن استخدامه في الدراسة أو الأعمال اليومية.', NULL, 'pending', 'dropoff', 'مركز أثر – فرع جنوب الرياض (حي الشفا – طريق ديراب)', NULL, NULL, NULL, NULL, NULL, '2026-03-13 14:26:33', '2026-03-13 14:26:33'),
(23, 3, 10, 'ألعاب للأطفال في أسرة محدودة الدخل', 'ألعاب', NULL, 'لدى هذه الأسرة أطفال صغار لا يمتلكون ألعابًا تساعدهم على قضاء وقت ممتع في المنزل, التبرع بالألعاب مثل السيارات الصغيرة أو الدمى أو الألعاب التعليمية سيساعد في إدخال الفرح إلى قلوبهم ويوفر لهم لحظات ممتعة', 'uploads/cases/1773375646_play.jpg', 'accepted', 'dropoff', 'مركز أثر – فرع وسط الرياض (حي الملز – طريق صلاح الدين الأيوبي)', NULL, NULL, NULL, NULL, 1, '2026-03-13 14:56:43', '2026-03-13 14:56:55'),
(24, 3, 8, 'ملابس شتوية لأسرة محتاجة', 'ملابس', NULL, 'مع انخفاض درجات الحرارة، تعاني هذه الأسرة من نقص في الملابس الشتوية التي تحميهم من البرد, التبرع بمعاطف أو ملابس شتوية بحالة جيدة سيساعدهم على الشعور بالدفء خلال فصل الشتاء.', 'uploads/cases/1773375348_coat.jpg', 'pending', 'dropoff', 'مركز أثر – فرع وسط الرياض (حي الملز – طريق صلاح الدين الأيوبي)', NULL, NULL, NULL, NULL, NULL, '2026-03-13 14:57:21', '2026-03-13 14:57:21'),
(25, 3, NULL, 'التبرع بملابس اطفال بحالة ممتازة', 'ملابس', NULL, 'لدي ملابس أطفال بحالة ممتازة لم تعد مناسبة لأطفالي بعد أن كبروا، وأرغب في التبرع بها ليستفيد منها أطفال من أسر محتاجة.', NULL, 'rejected', 'dropoff', 'مركز أثر – فرع جنوب الرياض (حي الشفا – طريق ديراب)', NULL, NULL, NULL, NULL, 1, '2026-03-13 15:03:04', '2026-03-13 15:03:12'),
(28, 2, NULL, 'ملابس بحالة جيدة', 'ملابس', NULL, 'ملابس بحالة جيدة', NULL, 'rejected', 'dropoff', 'مركز أثر – فرع شمال الرياض (حي النرجس – طريق الأمير فيصل بن بندر)', NULL, NULL, NULL, NULL, 1, '2026-03-14 05:03:39', '2026-03-18 22:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `created_at`, `updated_at`) VALUES
(1, 'عهود', 'محمد', 'Ohoud@gmail.com', '0555278365', '$2y$10$eQkkzKIPRNwIisa.SQw8yeATEiV1bpDGQxPOpPhBUVz8KiZ4nudHS', '2026-03-10 23:59:54', '2026-03-11 03:11:06'),
(2, 'ريماس', 'سالم', 'remas@gmail.com', '0546570776', '$2y$10$8qsL00ZWycTiVeHP4IhBAeBTb87EchA4GDsJu7ZPegVgDskTtI/eO', '2026-03-13 04:23:11', '2026-03-13 04:23:11'),
(3, 'لبنى', 'محمد', 'lubna@gmail.com', '0538126823', '$2y$10$OjdBB/lofLEOJD6LO3xQgOymKNzFDxsmxNkGuX3hzG9Hq3ZToGG8y', '2026-03-13 04:28:30', '2026-03-13 04:28:30'),
(4, 'حصة', 'العريفي', 'Hessa@gmail.com', '0500021682', '$2y$10$Uor1yX.oAxnagO.R2N6N8eGmZ1OLsFJwNuKD/LmXghYLiPocbMIGm', '2026-03-13 04:29:37', '2026-03-13 04:29:37'),
(5, 'ريما', 'البداح', 'Reema@gmail.com', '0554427451', '$2y$10$DPKgPEresrocc8hmzTsHoeXEgR0POXxsncrQFRZ.PIrmQITcgYFoW', '2026-03-13 04:30:29', '2026-03-13 04:30:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `donation_cases`
--
ALTER TABLE `donation_cases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cases_admin` (`created_by_admin_id`),
  ADD KEY `idx_cases_category` (`category`),
  ADD KEY `idx_cases_active` (`is_active`);

--
-- Indexes for table `donation_requests`
--
ALTER TABLE `donation_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_requests_reviewed_admin` (`reviewed_by_admin_id`),
  ADD KEY `idx_requests_status` (`status`),
  ADD KEY `idx_requests_category` (`category`),
  ADD KEY `idx_requests_case_id` (`case_id`),
  ADD KEY `idx_requests_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donation_cases`
--
ALTER TABLE `donation_cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `donation_requests`
--
ALTER TABLE `donation_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `donation_cases`
--
ALTER TABLE `donation_cases`
  ADD CONSTRAINT `fk_cases_admin` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `donation_requests`
--
ALTER TABLE `donation_requests`
  ADD CONSTRAINT `fk_requests_case` FOREIGN KEY (`case_id`) REFERENCES `donation_cases` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_requests_reviewed_admin` FOREIGN KEY (`reviewed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
