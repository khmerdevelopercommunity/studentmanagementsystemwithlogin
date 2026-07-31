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
(1, 'Sarah', 'Johnson', 's.johnson@school.edu', '0123456789', '2026-07-27');

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
(1, 'Mathematics', 'Primary grade mathematics and basic arithmetic.');

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

-- Dumping 1 Class
INSERT INTO `classes` (`class_id`, `grade_level`, `section`, `academic_year`, `homeroom_teacher_id`) VALUES
(1, 1, 'A', '2026-2027', 1);

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
(2, 'Olivia', 'Williams', '2019-05-22', 'Female', '2026-07-27', 'Mild peanut allergy', 1),
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

-- Dumping 1 Enrollment (Liam Smith enrolled in Grade 1-A)
INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `class_id`, `enrollment_date`) VALUES
(1, 1, 1, '2026-07-27');

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

-- Dumping 1 Guardian
INSERT INTO `guardians` (`guardian_id`, `first_name`, `last_name`, `relationship`, `phone`, `email`, `address`) VALUES
(1, 'Robert', 'Smith', 'Father', '0987654321', 'r.smith@email.com', '123 Main Street, Suite 4B');

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

-- Dumping 1 Student-Guardian Association
INSERT INTO `student_guardians` (`student_id`, `guardian_id`, `is_primary_contact`) VALUES
(1, 1, 1);

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

-- Dumping 1 Attendance Record
INSERT INTO `attendance` (`attendance_id`, `enrollment_id`, `date`, `status`, `remarks`) VALUES
(1, 1, '2026-07-28', 'Present', 'On time');

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

-- Dumping 1 Grade Record
INSERT INTO `grades` (`grade_id`, `enrollment_id`, `subject_id`, `term`, `score`, `letter_grade`, `teacher_comments`) VALUES
(1, 1, 1, 'Term 1', 95.50, 'A', 'Excellent performance in math!');

COMMIT;