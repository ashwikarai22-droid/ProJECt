-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2026 at 12:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_submission`
--

-- --------------------------------------------------------

--
-- Table structure for table `calendar_events`
--

CREATE TABLE `calendar_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `priority` varchar(20) NOT NULL,
  `event_date` date NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `event_type` varchar(20) DEFAULT 'task',
  `meeting_time` time DEFAULT NULL,
  `meeting_link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `calendar_events`
--

INSERT INTO `calendar_events` (`id`, `title`, `priority`, `event_date`, `project_id`, `event_type`, `meeting_time`, `meeting_link`) VALUES
(2, 'Task 1', 'p-high', '2026-01-30', NULL, 'task', NULL, NULL),
(3, 'task 3', 'p-low', '2026-01-27', NULL, 'task', NULL, NULL),
(4, 'task 4', 'p-med', '2026-01-19', NULL, 'task', NULL, NULL),
(5, 'test', 'p-high', '0000-00-00', NULL, 'task', NULL, NULL),
(6, 'test', 'p-high', '0000-00-00', NULL, 'task', NULL, NULL),
(7, 'test', 'p-med', '2026-01-25', NULL, 'task', NULL, NULL),
(8, 'teat 1', 'p-high', '2026-01-07', NULL, 'task', NULL, NULL),
(9, 'test 4', 'p-high', '2026-01-02', NULL, 'task', NULL, NULL),
(10, 'task 1', 'p-high', '2026-01-15', NULL, 'task', NULL, NULL),
(11, 'task 1', 'p-high', '2026-01-22', NULL, 'task', NULL, NULL),
(12, 'Task 6', 'p-med', '2026-01-10', NULL, 'task', NULL, NULL),
(13, 'Task', 'p-high', '2026-01-23', NULL, 'task', NULL, NULL),
(14, 'm1', '', '0000-00-00', NULL, 'meeting', '15:30:00', 'https://meet.google.com/kad-szet-unn'),
(15, 'm1', '', '0000-00-00', NULL, 'meeting', '15:30:00', 'https://meet.google.com/kad-szet-unn'),
(16, 'Discussion', '', '0000-00-00', NULL, 'meeting', '12:30:00', 'https://meet.google.com/kad-szet-unn'),
(17, 'Discussion', 'p-high', '0000-00-00', NULL, 'task', NULL, NULL),
(18, 'Discussion', '', '0000-00-00', NULL, 'meeting', '12:30:00', 'https://meet.google.com/kad-szet-unn');

-- --------------------------------------------------------

--
-- Table structure for table `forum_messages`
--

CREATE TABLE `forum_messages` (
  `msg_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `sender_name` varchar(100) DEFAULT NULL,
  `sender_role` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_messages`
--

INSERT INTO `forum_messages` (`msg_id`, `project_id`, `sender_id`, `sender_name`, `sender_role`, `message`, `is_private`, `sent_at`) VALUES
(1, 10, 14, 'Dr Akriti Shukla', 'Faculty', 'Where have you reached in Analytics and Desingning?', 0, '2026-01-27 18:27:32'),
(2, 10, 14, 'Dr Akriti Shukla', 'Faculty', 'We’ve completed analytics and are in the advanced UI/UX designing phase with core screens already developed', 0, '2026-01-27 18:29:12'),
(3, 10, 5, 'Ashwika Rai', 'Team Leader', 'We’ve completed analytics and are in the advanced UI/UX designing phase with core screens already developed', 0, '2026-01-27 18:29:51');

-- --------------------------------------------------------

--
-- Table structure for table `kanban`
--

CREATE TABLE `kanban` (
  `task_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `task_title` varchar(255) NOT NULL,
  `assigned_to` varchar(50) DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `due_date` date DEFAULT NULL,
  `status` enum('Todo','InProgress','Review','Done') DEFAULT 'Todo',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kanban`
--

INSERT INTO `kanban` (`task_id`, `project_id`, `task_title`, `assigned_to`, `priority`, `due_date`, `status`, `created_at`) VALUES
(1, 4, 'task 1', '9876543210AI', 'High', '2026-01-27', 'Review', '2026-01-25 15:25:31'),
(2, 4, 'task 1', '9876543210AI', 'High', '2026-01-30', 'Todo', '2026-01-25 15:25:45'),
(3, 4, 'Data ', '0987654321AI', 'High', '2026-01-29', 'Todo', '2026-01-26 04:50:20'),
(4, 4, 'task name', '0987654321AI', 'High', '2026-01-29', 'Todo', '2026-01-26 05:45:49'),
(5, 10, 'Design Analytics', '3456789012AI', 'High', '2026-01-30', 'Todo', '2026-01-27 14:00:04'),
(6, 10, 'System Design', '0201AI251012', 'Medium', '2026-01-31', 'InProgress', '2026-01-27 14:04:47'),
(7, 10, 'Analysis and Modeling', '3456789012AI', 'Medium', '2026-02-05', 'Review', '2026-01-27 14:08:24'),
(8, 10, 'Visualization & Communication', '3456789012AI', 'Low', '2026-02-05', 'InProgress', '2026-01-27 14:09:03'),
(9, 10, 'Data Collection & Management', '3456789012AI', 'Low', '2026-02-03', 'Done', '2026-01-27 14:09:40');

-- --------------------------------------------------------

--
-- Table structure for table `mentors`
--

CREATE TABLE `mentors` (
  `mentor_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mentors`
--

INSERT INTO `mentors` (`mentor_id`, `name`, `email`, `department`, `phone`) VALUES
(5, 'Dr Y', 'Y@gmail.com', 'IT', NULL),
(6, 'Dr Akriti Shukla', 'akritishukla@gmail.com', 'AI&DS', NULL),
(7, 'Dr Abhishek Singh', 'singh@gmail.com', 'IT', NULL),
(8, 'Prof Smriti Jain', 'smriti@gmail.com', 'EC', NULL),
(9, 'Dr Shweta Nema', 'shweta@gmail.com', 'CS', NULL),
(12, 'Dr Bhavan jain ', 'bhavan123@gmail.com', 'AI&DS', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `title` varchar(200) DEFAULT NULL,
  `team_name` varchar(100) DEFAULT NULL,
  `total_members` int(11) DEFAULT NULL,
  `project_file` varchar(255) DEFAULT NULL,
  `submission_date` timestamp NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `project_type` enum('Minor','Major') DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `mentor_id` int(11) DEFAULT NULL,
  `requested_faculty_name` varchar(100) DEFAULT NULL,
  `mentorship_request_status` varchar(20) DEFAULT 'Pending',
  `mentorship_request_date` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('Proposed','Approved','Rejected') DEFAULT 'Proposed',
  `team_size` int(11) DEFAULT NULL,
  `leader_name` varchar(100) DEFAULT NULL,
  `leader_roll_no` varchar(50) DEFAULT NULL,
  `leader_branch` enum('AI&DS','CS','IT','ME','CE','IP','EE','EC','MT') NOT NULL,
  `leader_semester` enum('I','II','III','IV','V','VI','VII','VIII') NOT NULL,
  `leader_email` varchar(100) DEFAULT NULL,
  `leader_phone` varchar(20) DEFAULT NULL,
  `leader_sem` varchar(20) DEFAULT NULL,
  `member1_name` varchar(100) DEFAULT NULL,
  `member1_roll_no` varchar(50) DEFAULT NULL,
  `member1_branch` enum('AI&DS','CS','IT','ME','CE','IP','EE','EC','MT') DEFAULT NULL,
  `member1_email` varchar(100) DEFAULT NULL,
  `member1_phone` varchar(20) DEFAULT NULL,
  `member1_sem` enum('I','II','III','IV','V','VI','VII','VIII') DEFAULT NULL,
  `member2_name` varchar(100) DEFAULT NULL,
  `member2_roll_no` varchar(50) DEFAULT NULL,
  `member2_branch` enum('AI&DS','CS','IT','ME','CE','IP','EE','EC','MT') DEFAULT NULL,
  `member2_email` varchar(100) DEFAULT NULL,
  `member2_phone` varchar(20) DEFAULT NULL,
  `member2_sem` enum('I','II','III','IV','V','VI','VII','VIII') DEFAULT NULL,
  `member3_name` varchar(100) DEFAULT NULL,
  `member3_roll_no` varchar(50) DEFAULT NULL,
  `member3_branch` enum('AI&DS','CS','IT','ME','CE','IP','EE','EC','MT') DEFAULT NULL,
  `member3_email` varchar(100) DEFAULT NULL,
  `member3_phone` varchar(20) DEFAULT NULL,
  `member3_sem` enum('I','II','III','IV','V','VI','VII','VIII') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `title`, `team_name`, `total_members`, `project_file`, `submission_date`, `description`, `project_type`, `student_id`, `mentor_id`, `requested_faculty_name`, `mentorship_request_status`, `mentorship_request_date`, `status`, `team_size`, `leader_name`, `leader_roll_no`, `leader_branch`, `leader_semester`, `leader_email`, `leader_phone`, `leader_sem`, `member1_name`, `member1_roll_no`, `member1_branch`, `member1_email`, `member1_phone`, `member1_sem`, `member2_name`, `member2_roll_no`, `member2_branch`, `member2_email`, `member2_phone`, `member2_sem`, `member3_name`, `member3_roll_no`, `member3_branch`, `member3_email`, `member3_phone`, `member3_sem`) VALUES
(1, 'Computer Architecture', NULL, NULL, NULL, '2026-01-24 06:29:20', 'In computer science and computer engineering, a computer architecture is the structure of a computer system made from component parts.[1] It can sometimes be a high-level description that ignores details of the implementation.[2] At a more detailed level, the description may include the instruction set architecture design, microarchitecture design, logic design, and implementation', 'Minor', 5, NULL, NULL, 'Approved', '2026-01-24 06:29:20', 'Proposed', NULL, NULL, NULL, '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Project management system', NULL, NULL, NULL, '2026-01-24 06:29:20', 'A Project Management System (PMS) is a structured, often software-based, framework used to plan, execute, monitor, and close projects efficiently. It centralizes communication, tracks tasks and progress in real-time, manages resources, and controls budgets to ensure project success. It enables teams to collaborate, reduce errors, and meet deadlines. ', 'Minor', 5, 5, NULL, 'Pending', '2026-01-24 06:29:20', 'Proposed', 3, 'abc', NULL, '', '', NULL, NULL, '3', 'cde', NULL, NULL, NULL, NULL, 'I', 'ghe', NULL, NULL, NULL, NULL, 'V', '', NULL, NULL, NULL, NULL, ''),
(3, 'Project 1', 'Team 1', 3, 'uploads/1769241171_WhatsApp Image 2026-01-23 at 10.12.30 PM.jpeg', '2026-01-24 07:52:51', 'A project description is a foundational document that provides a high-level overview of a project, explaining its purpose, goals, scope, key stakeholders, timeline, and expected outcomes/deliverables, acting as a strategic blueprint to align everyone involved and justify the investment, focusing on the \"what\" and \"why\" before the \"how\". It clarifies the problem being solved, sets realistic expectations, and serves as a guiding reference throughout the project lifecycle, ensuring clarity and preventing misunderstandings', 'Minor', 5, NULL, '', 'Pending', '2026-01-24 07:52:51', 'Proposed', NULL, 'bcd', '12345678AI', 'AI&DS', 'IV', 'bcd@gmail.com', '1234567890', NULL, 'cde', '23456789AI', 'AI&DS', 'cde@gmail.com', '1234568790', 'II', 'def', '34567890AI', 'AI&DS', 'def@gmail.com', '', 'IV', '', '', '', '', '', ''),
(4, 'Project 2', 'Team 2', 3, 'uploads/1769243875_ProJECt_A_Centralized_Project_Management_System.docx', '2026-01-24 08:37:55', 'A project description is a foundational document that provides a high-level overview of a project, explaining its purpose, goals, scope, key stakeholders, timeline, and expected outcomes/deliverables, acting as a strategic blueprint to align everyone involved and justify the investment, focusing on the \"what\" and \"why\" before the \"how\". It clarifies the problem being solved, sets realistic expectations, and serves as a guiding reference throughout the project lifecycle, ensuring clarity and preventing misunderstandings', 'Major', 5, 7, '', 'Approved', '2026-01-24 08:37:55', 'Proposed', NULL, 'xyz', '0987654321AI', 'AI&DS', 'IV', 'xyz@gmail.com', '0987654321', NULL, 'wxy', '9876543210AI', 'AI&DS', 'wxy@gmail.com', '9876543210', 'IV', 'vwx', '8765432109AI', 'AI&DS', 'vwx@gmail.com', '8765432109', 'II', '', '', '', '', '', ''),
(9, 'Project 3', 'Team 4', 2, 'uploads/1769452148_project architecture.png', '2026-01-26 18:29:08', 'In computer science and computer engineering, a computer architecture is the structure of a computer system made from component parts.[1] It can sometimes be a high-level description that ignores details of the implementation.[2] At a more detailed level, the description may include the instruction set architecture design, microarchitecture design, logic design, and implementation', 'Minor', 6, NULL, 'Dr X', 'Approved', '2026-01-26 18:29:08', 'Proposed', NULL, 'Ashwika Rai', '0201AI251012', 'AI&DS', 'II', 'ashwikarai22@gmail.com', '5317804328', NULL, 'Avika Singh', '3456789012AI', 'AI&DS', 'a@gamil.com', '4567890123', 'II', '', '', '', '', '', '', '', '', '', '', '', ''),
(10, 'Project 3', 'Team 4', 2, 'uploads/1769453768_project architecture.png', '2026-01-26 18:56:08', 'In computer science and computer engineering, a computer architecture is the structure of a computer system made from component parts.[1] It can sometimes be a high-level description that ignores details of the implementation.[2] At a more detailed level, the description may include the instruction set architecture design, microarchitecture design, logic design, and implementation', 'Minor', 5, 6, 'Dr X', 'Approved', '2026-01-26 18:56:08', 'Proposed', NULL, 'Ashwika Rai', '0201AI251012', 'AI&DS', 'II', 'ashwikarai22@gmail.com', '5317804328', NULL, 'Avika Singh', '3456789012AI', 'AI&DS', 'a@gamil.com', '4567890123', 'II', '', '', '', '', '', '', '', '', '', '', '', ''),
(11, 'Weather Forecast App Using API', 'Forecasters', 4, 'uploads/1769532440_Weather_Forecast_App_Proposal.docx', '2026-01-27 16:47:20', 'Problem Statement\r\nMany existing weather platforms are complex and overloaded with unnecessary features. Users often require a simple, fast, and accurate weather application that provides essential weather details instantly.\r\nThe challenge is to design a lightweight and user-friendly web application that fetches real-time weather data efficiently using APIs.\r\nObjectives\r\nThe main objectives of this project are:\r\nTo develop a web-based weather forecasting application\r\nTo fetch real-time weather data using a weather API\r\nTo display temperature, humidity, wind speed, and weather conditions\r\nTo provide an easy-to-use and responsive user interface\r\nTo enhance practical knowledge of API integration and frontend technologies\r\n', 'Minor', 5, 6, 'Dr Akriti Shukla', 'Approved', '2026-01-27 16:47:20', 'Proposed', NULL, 'Ashwika Rai', '0201AI251012', '', 'II', 'ashwikarai22@gmail.com', '6518054318', NULL, 'Meghna Prasad', '23875190641AI', '', 'meghna@gmail.com', '4589210547', '', 'Rahul Kumar', '2483965135AI', '', 'rahul@gmail.com', '3478295140', '', 'Prashant Singh', '934512861AI', '', 'prashant@gmail.com', '175234098', '');

-- --------------------------------------------------------

--
-- Table structure for table `project_status`
--

CREATE TABLE `project_status` (
  `status_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `milestone_title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Not Started','In Progress','Completed','On Hold') DEFAULT 'Not Started',
  `progress_percent` int(11) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_status`
--

INSERT INTO `project_status` (`status_id`, `project_id`, `milestone_title`, `description`, `status`, `progress_percent`, `updated_at`) VALUES
(10, 4, 'Project Initiation & Ideation', NULL, 'Not Started', 0, '2026-01-27 07:39:43'),
(11, 4, 'Literature Survey & Feasibility Study', NULL, 'Not Started', 0, '2026-01-27 07:39:43'),
(12, 4, 'SRS & Documentation (Synopsis)', NULL, 'Not Started', 0, '2026-01-27 07:39:43'),
(13, 4, 'System Design & Architecture', NULL, 'Not Started', 0, '2026-01-27 07:39:43'),
(14, 4, 'Prototype Development (POC)', NULL, 'Not Started', 0, '2026-01-27 07:39:43'),
(15, 4, 'Full-Stack Implementation', NULL, 'Not Started', 0, '2026-01-27 07:39:43'),
(16, 4, 'Testing & Validation', NULL, 'Not Started', 0, '2026-01-27 07:39:43'),
(17, 4, 'Final Deployment & Optimization', NULL, 'Not Started', 0, '2026-01-27 07:39:43'),
(18, 4, 'Project Report & Viva Preparation', NULL, 'Not Started', 0, '2026-01-27 07:39:43'),
(19, 9, 'Problem Identification & Topic Selection', 'Problem identification is the foundational, first step in the problem-solving process that involves recognizing, defining, and scoping a specific issue, roadblock, or opportunity. It requires analyzing data, consulting stakeholders, and identifying root causes to create a clear, actionable problem statement. ', 'In Progress', 20, '2026-01-27 08:08:05'),
(20, 9, 'Literature Review & Feasibility Study', NULL, 'Not Started', 0, '2026-01-27 08:08:05'),
(21, 9, 'System Requirements & Design', NULL, 'Not Started', 0, '2026-01-27 08:08:05'),
(22, 9, 'Implementation (Core Module)', NULL, 'Not Started', 0, '2026-01-27 08:08:05'),
(23, 9, 'Testing & Debugging', NULL, 'Not Started', 0, '2026-01-27 08:08:05'),
(24, 9, 'Documentation & Presentation', NULL, 'Not Started', 0, '2026-01-27 08:08:05'),
(25, 10, 'Problem Identification & Topic Selection', 'Problem identification is the foundational, first step in the problem-solving process that involves recognizing, defining, and scoping a specific issue, roadblock, or opportunity. It requires analyzing data, consulting stakeholders, and identifying root causes to create a clear, actionable problem statement. ', 'Completed', 100, '2026-01-27 08:54:38'),
(26, 10, 'Literature Review & Feasibility Study', '', 'In Progress', 20, '2026-01-27 08:54:38'),
(27, 10, 'System Requirements & Design', NULL, 'Not Started', 0, '2026-01-27 08:54:38'),
(28, 10, 'Implementation (Core Module)', NULL, 'Not Started', 0, '2026-01-27 08:54:38'),
(29, 10, 'Testing & Debugging', NULL, 'Not Started', 0, '2026-01-27 08:54:38'),
(30, 10, 'Documentation & Presentation', NULL, 'Not Started', 0, '2026-01-27 08:54:38');

-- --------------------------------------------------------

--
-- Table structure for table `project_tasks`
--

CREATE TABLE `project_tasks` (
  `task_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `assigned_to` varchar(50) DEFAULT NULL,
  `task_title` varchar(255) DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `due_date` date DEFAULT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `roll_no` varchar(12) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('Student','Faculty','Admin') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `roll_no`, `name`, `email`, `password`, `role`) VALUES
(5, '0201AI251012', 'Ashwika Rai', 'ashwikarai22@gmail.com', '$2y$10$TuKViebpFfCtr2VQdEg46OXjaJofiln/Jws./9CFW95pE/nEzmPe.', 'Student'),
(6, 'FA1234567890', 'Dr X', 'abc@gmail.com', '$2y$10$mV7.mtka/L5Ga6a6nooIZuNxAMlscde6o5hEbYyVOah4O1ANPJ17O', 'Faculty'),
(7, '34124qw678', 'abc', 'bce@gmail.com', '$2y$10$KXlvLC1VtJJx0opbk14aB.I8/FyX2fmsRPIHbDwqb7AI8zSIyyng6', 'Student'),
(8, 'AD1234567890', 'Dr XYZ', 'admin1@gmail.com', '$2y$10$A8cAgMg4bfsy4EjiLMC4xetMIwUa9DpUfkBa4HzuXeczWnut4DMw2', 'Admin'),
(10, '876543212AI', 'qwe', 'qwe@gmail.com', '$2y$10$WthFfKCYkjvj91a6kGvrbOaCnLfgZrcPJ0p1qI2gjFLyQDUcbMxfu', 'Student'),
(11, '1234AI123456', 'bcd', 'bcd@gmail.com', '$2y$10$7qVASSyTV.ngMhx0dFBwHO9gTQWmPhZ1uBSAt74.7EB13JTa1bayO', 'Student'),
(12, 'AD1234567895', 'Dr YZX', 'YZX@gmail.com', '$2y$10$7OMg5PIoQHO.4njkgOcD2uT/b.rB2tJhlahdYgdP0uBBaciavs5Sy', 'Admin'),
(14, '34124nh678', 'Dr Akriti Shukla', 'akritishukla@gmail.com', '$2y$10$VX2NYeRToGH9bTIiRRiB5eq097tzjrL2AY56wtxDK7BVNgBFZNC2O', 'Faculty');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calendar_events`
--
ALTER TABLE `calendar_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `forum_messages`
--
ALTER TABLE `forum_messages`
  ADD PRIMARY KEY (`msg_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `kanban`
--
ALTER TABLE `kanban`
  ADD PRIMARY KEY (`task_id`);

--
-- Indexes for table `mentors`
--
ALTER TABLE `mentors`
  ADD PRIMARY KEY (`mentor_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `fk_projects_leader_user` (`student_id`);

--
-- Indexes for table `project_status`
--
ALTER TABLE `project_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD PRIMARY KEY (`task_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `roll_no` (`roll_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calendar_events`
--
ALTER TABLE `calendar_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `forum_messages`
--
ALTER TABLE `forum_messages`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kanban`
--
ALTER TABLE `kanban`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mentors`
--
ALTER TABLE `mentors`
  MODIFY `mentor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `project_status`
--
ALTER TABLE `project_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `project_tasks`
--
ALTER TABLE `project_tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `forum_messages`
--
ALTER TABLE `forum_messages`
  ADD CONSTRAINT `forum_messages_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_leader_user` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
