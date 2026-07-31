-- phpMyAdmin SQL Dump
-- version 5.2.1
-- Database: `primary_school_db`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ========================================================
-- SCHOOL MANAGEMENT TABLES (NO users OR audit_logs)
-- ========================================================

-- --------------------------------------------------------
-- Table structure for table `teachers`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `teachers` (
  `teacher_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `hire_date` date DEFAULT curdate(),
  PRIMARY KEY (`teacher_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping 1 Teacher
INSERT INTO `teachers` (`teacher_id`, `first_name`, `last_name`, `email`, `phone`, `hire_date`) VALUES
(1, 'Sarah', 'Johnson', 'sarah.johnson@school.edu', '+1-555-0101', '2026-07-27'),
(2, 'Michael', 'Chen', 'michael.chen@school.edu', '+1-555-0102', '2026-07-28'),
(3, 'Jennifer', 'Williams', 'jennifer.williams@school.edu', '+1-555-0103', '2026-07-29');

-- --------------------------------------------------------
-- Table structure for table `subjects`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `subjects` (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`subject_id`),
  UNIQUE KEY `subject_name` (`subject_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping 1 Subject
INSERT INTO `subjects` (`subject_id`, `subject_name`, `description`) VALUES
(1, 'Mathematics', 'Primary grade mathematics covering arithmetic, geometry, and basic algebra.'),
(2, 'English', 'Reading, writing, grammar, and literature for elementary students.'),
(3, 'Science', 'Basic physical science, biology, and environmental studies.'),
(4, 'Social Studies', 'History, geography, and community awareness.'),
(5, 'Physical Education', 'Sports, fitness, and healthy lifestyle education.'),
(6, 'Arts', 'Visual arts, music, and creative expression.');

-- --------------------------------------------------------
-- Table structure for table `classes`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `classes` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT,
  `grade_level` int(11) NOT NULL CHECK (`grade_level` between 1 and 6),
  `section` varchar(5) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `homeroom_teacher_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`class_id`),
  UNIQUE KEY `unique_class_per_year` (`grade_level`,`section`,`academic_year`),
  KEY `homeroom_teacher_id` (`homeroom_teacher_id`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`homeroom_teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping 3 Classes
INSERT INTO `classes` (`class_id`, `grade_level`, `section`, `academic_year`, `homeroom_teacher_id`) VALUES
(1, 1, 'A', '2026-2027', 1),
(2, 2, 'A', '2026-2027', 2),
(3, 3, 'A', '2026-2027', 3);

-- --------------------------------------------------------
-- Table structure for table `students`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `admission_date` date DEFAULT curdate(),
  `medical_notes` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`student_id`),
  KEY `idx_students_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping 10 Fake Students
INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `dob`, `gender`, `admission_date`, `medical_notes`, `is_active`) VALUES
(1, 'Liam', 'Smith', '2019-03-12', 'Male', '2026-07-27', 'None', 1),
(2, 'Olivia', 'Williams', '2019-05-22', 'Female', '2026-07-27', 'Mild peanut allergy - needs EPI pen', 1),
(3, 'Noah', 'Brown', '2019-01-10', 'Male', '2026-07-27', 'None', 1),
(4, 'Emma', 'Jones', '2019-08-14', 'Female', '2026-07-27', 'Wears reading glasses', 1),
(5, 'Oliver', 'Garcia', '2019-11-03', 'Male', '2026-07-27', 'Asthma inhaler required', 1),
(6, 'Ava', 'Miller', '2019-02-19', 'Female', '2026-07-27', 'None', 1),
(7, 'Elijah', 'Davis', '2019-07-25', 'Male', '2026-07-27', 'Lactose intolerant', 1),
(8, 'Sophia', 'Rodriguez', '2019-09-09', 'Female', '2026-07-27', 'None', 1),
(9, 'James', 'Martinez', '2019-12-01', 'Male', '2026-07-27', 'None', 1),
(10, 'Isabella', 'Hernandez', '2019-04-18', 'Female', '2026-07-27', 'None', 1);

-- --------------------------------------------------------
-- Table structure for table `enrollments`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `enrollments` (
  `enrollment_id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `enrollment_date` date DEFAULT curdate(),
  PRIMARY KEY (`enrollment_id`),
  UNIQUE KEY `unique_student_class` (`student_id`,`class_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping Enrollments for Grade 1-A
INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `class_id`, `enrollment_date`) VALUES
(1, 1, 1, '2026-07-27'),
(2, 2, 1, '2026-07-27'),
(3, 3, 1, '2026-07-27'),
(4, 4, 1, '2026-07-27'),
(5, 5, 1, '2026-07-27'),
(6, 6, 2, '2026-07-28'),
(7, 7, 2, '2026-07-28'),
(8, 8, 2, '2026-07-28'),
(9, 9, 3, '2026-07-29'),
(10, 10, 3, '2026-07-29');

-- --------------------------------------------------------
-- Table structure for table `guardians`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `guardians` (
  `guardian_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `relationship` varchar(30) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text NOT NULL,
  PRIMARY KEY (`guardian_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping Guardians
INSERT INTO `guardians` (`guardian_id`, `first_name`, `last_name`, `relationship`, `phone`, `email`, `address`) VALUES
(1, 'Robert', 'Smith', 'Father', '+1-555-0201', 'robert.smith@email.com', '123 Main Street, Suite 4B, New York, NY 10001'),
(2, 'Maria', 'Williams', 'Mother', '+1-555-0202', 'maria.williams@email.com', '456 Oak Avenue, Los Angeles, CA 90001'),
(3, 'David', 'Brown', 'Father', '+1-555-0203', 'david.brown@email.com', '789 Pine Road, Chicago, IL 60601'),
(4, 'Sarah', 'Jones', 'Mother', '+1-555-0204', 'sarah.jones@email.com', '321 Elm Street, Houston, TX 77001'),
(5, 'Carlos', 'Garcia', 'Father', '+1-555-0205', 'carlos.garcia@email.com', '654 Maple Drive, Phoenix, AZ 85001');

-- --------------------------------------------------------
-- Table structure for table `student_guardians`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `student_guardians` (
  `student_id` int(11) NOT NULL,
  `guardian_id` int(11) NOT NULL,
  `is_primary_contact` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`student_id`,`guardian_id`),
  KEY `guardian_id` (`guardian_id`),
  CONSTRAINT `student_guardians_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `student_guardians_ibfk_2` FOREIGN KEY (`guardian_id`) REFERENCES `guardians` (`guardian_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping Student-Guardian Associations
INSERT INTO `student_guardians` (`student_id`, `guardian_id`, `is_primary_contact`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 1),
(4, 4, 1),
(5, 5, 1),
(6, 2, 1),
(7, 3, 1),
(8, 4, 1),
(9, 5, 1),
(10, 1, 1);

-- --------------------------------------------------------
-- Table structure for table `attendance`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(11) NOT NULL,
  `date` date NOT NULL DEFAULT curdate(),
  `status` enum('Present','Absent','Late','Excused') NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `unique_attendance_per_day` (`enrollment_id`,`date`),
  KEY `idx_attendance_date` (`date`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping Attendance Records
INSERT INTO `attendance` (`attendance_id`, `enrollment_id`, `date`, `status`, `remarks`) VALUES
(1, 1, '2026-07-28', 'Present', 'On time'),
(2, 2, '2026-07-28', 'Present', 'On time'),
(3, 3, '2026-07-28', 'Late', 'Traffic delay'),
(4, 4, '2026-07-28', 'Present', 'On time'),
(5, 5, '2026-07-28', 'Absent', 'Sick - called in');

-- --------------------------------------------------------
-- Table structure for table `grades`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `grades` (
  `grade_id` int(11) NOT NULL AUTO_INCREMENT,
  `enrollment_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `term` enum('Term 1','Term 2','Term 3','Term 4') NOT NULL,
  `score` decimal(5,2) DEFAULT NULL CHECK (`score` >= 0 and `score` <= 100),
  `letter_grade` varchar(5) DEFAULT NULL,
  `teacher_comments` text DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`grade_id`),
  UNIQUE KEY `unique_grade_per_term` (`enrollment_id`,`subject_id`,`term`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`enrollment_id`) ON DELETE CASCADE,
  CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping Grade Records (Liam Smith - Grade 1-A)
INSERT INTO `grades` (`grade_id`, `enrollment_id`, `subject_id`, `term`, `score`, `letter_grade`, `teacher_comments`) VALUES
(1, 1, 1, 'Term 1', 95.50, 'A', 'Excellent performance in mathematics!'),
(2, 1, 2, 'Term 1', 88.00, 'B+', 'Good reading comprehension skills.'),
(3, 1, 3, 'Term 1', 92.00, 'A-', 'Shows great interest in science.'),
(4, 2, 1, 'Term 1', 78.50, 'C+', 'Needs extra practice with multiplication.'),
(5, 2, 2, 'Term 1', 91.00, 'A-', 'Excellent writing skills.'),
(6, 3, 1, 'Term 1', 85.00, 'B', 'Good progress this term.'),
(7, 3, 2, 'Term 1', 87.00, 'B+', 'Strong vocabulary.'),
(8, 4, 1, 'Term 1', 93.00, 'A', 'Outstanding effort!'),
(9, 4, 3, 'Term 1', 89.00, 'B+', 'Great participation in class.'),
(10, 5, 1, 'Term 1', 75.00, 'C', 'Needs to complete homework regularly.');

-- ========================================================
-- Reset Auto-Increment Counters
-- ========================================================
ALTER TABLE `teachers` AUTO_INCREMENT = 4;
ALTER TABLE `subjects` AUTO_INCREMENT = 7;
ALTER TABLE `classes` AUTO_INCREMENT = 4;
ALTER TABLE `students` AUTO_INCREMENT = 11;
ALTER TABLE `enrollments` AUTO_INCREMENT = 11;
ALTER TABLE `guardians` AUTO_INCREMENT = 6;
ALTER TABLE `student_guardians` AUTO_INCREMENT = 1;
ALTER TABLE `attendance` AUTO_INCREMENT = 6;
ALTER TABLE `grades` AUTO_INCREMENT = 11;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;