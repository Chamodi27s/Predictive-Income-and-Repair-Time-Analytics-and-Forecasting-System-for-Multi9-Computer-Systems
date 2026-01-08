-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 06:43 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `servidedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `acc_id` int(11) NOT NULL,
  `acc_name` varchar(100) NOT NULL,
  `account_no` varchar(50) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`acc_id`, `acc_name`, `account_no`, `balance`) VALUES
(2, 'BOC Account', '123456789', 23456.00),
(3, 'HNB Account', '987654321', 2002.00);

-- --------------------------------------------------------

--
-- Table structure for table `cashbook`
--

CREATE TABLE `cashbook` (
  `cashid` int(11) NOT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `date` date NOT NULL,
  `income` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) DEFAULT 0.00,
  `acc_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cashbook`
--

INSERT INTO `cashbook` (`cashid`, `invoice_no`, `date`, `income`, `balance`, `acc_id`) VALUES
(10, '11', '2025-12-29', 10500.00, 10500.00, NULL),
(11, '12', '2025-12-29', 9500.00, 20000.00, NULL),
(14, 'online', '2025-12-29', 23456.00, 43456.00, 2),
(15, '13', '2025-12-29', 14500.00, 57956.00, NULL),
(16, '14', '2025-12-29', 150.00, 58106.00, NULL),
(20, '15', '2025-12-29', 10018611.00, 10076717.00, NULL),
(21, '16', '2025-12-29', 3050.00, 10079767.00, NULL),
(22, '17', '2025-12-30', 11500.00, 10091267.00, NULL),
(23, 'online', '2025-12-30', 2000.00, 10093267.00, 3),
(24, 'online', '2025-12-30', 2.00, 10093269.00, 3),
(25, '18', '2025-12-30', 5000.00, 10098269.00, NULL),
(26, '19', '2025-12-30', 14624.00, 10112893.00, NULL),
(27, '20', '2026-01-05', 6477.00, 10119370.00, NULL),
(28, '21', '2026-01-05', 5800.00, 10125170.00, NULL),
(29, '22', '2026-01-05', 14900.00, 10140070.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`, `status`) VALUES
(1, 'Desktop Computers', NULL),
(2, 'Monitors', NULL),
(3, 'Desktop Computers', NULL),
(4, 'Laptops', NULL),
(5, 'Monitors', NULL),
(6, 'Keyboards', NULL),
(7, 'Mouse', NULL),
(8, 'Printers', NULL),
(9, 'Networking Devices', NULL),
(10, 'Hard Drives', NULL),
(11, 'RAM Modules', NULL),
(12, 'Graphic Cards', NULL),
(13, 'Motherboards', NULL),
(14, 'Power Supplies', NULL),
(15, 'Cables & Accessories', NULL),
(16, 'Software', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `phone_number` varchar(15) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`phone_number`, `customer_name`, `address`, `email`) VALUES
('0720417529', 'Chamodi Sandeepani', '240/E', 'ana@gmail.com'),
('0761817517', 'hshini umnda', 'waliweriya', 'hasiii@gmail.com'),
('0761817518', 'lavan abishek', NULL, NULL),
('0765333721', 'anuththara', 'galdeniya', ''),
('0765333722', 'anuththara', NULL, NULL),
('0768456788', 'shiwardan', 'minu', 'shiwa@gmail.com'),
('0768483156', 'anuththra imanshi', 'galdeniya ,kappitiwaklana', NULL),
('0768483170', 'raniluuu', 'umandawa', 'ra@gmail.com'),
('07685947', 'pdmika', 'bchbjj vjhvhbv', 'padmi@gmail.com'),
('0768955775', 'maheee', NULL, NULL),
('07889565757575', 'dumindu', NULL, NULL),
('08789959595', 'dammi', 'galdeniya', 'd@gmail.com'),
('089657883', 'anuththra', 'kapapirtiwalana', NULL),
('089677333', 'vindya amarasekara', 'mathara', 'vindy@gmail.com'),
('0897655677', 'malindi', 'galdeniya', NULL),
('0987588845', 'hshini umnda', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `invoice_no` int(11) NOT NULL,
  `job_no` varchar(20) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `service_charge` decimal(10,2) DEFAULT 0.00,
  `parts_total` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `items_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`invoice_no`, `job_no`, `invoice_date`, `service_charge`, `parts_total`, `grand_total`, `items_json`) VALUES
(1, 'ORD-271039', '2025-12-28', 0.00, 0.00, 0.00, '[]'),
(2, 'ORD-271039', '2025-12-29', 0.00, 98150.00, 98150.00, '[{\"code\":\"CMOS-BAT\",\"name\":\"CMOS Battery (CR2032)\",\"price\":\"150\",\"qty\":\"1\",\"sub\":150},{\"code\":\"MON-22\",\"name\":\"22 Inch LED Monitor\",\"price\":\"24500\",\"qty\":\"4\",\"sub\":98000}]'),
(3, 'ORD-271039', '2025-12-29', 0.00, 1850.00, 1850.00, '[{\"code\":\"KBD-USB\",\"name\":\"USB Standard Keyboard\",\"price\":\"1850\",\"qty\":\"1\",\"sub\":1850}]'),
(4, 'ORD-271039', '2025-12-29', 0.00, 7400.00, 7400.00, '[{\"code\":\"KBD-USB\",\"name\":\"USB Standard Keyboard\",\"price\":\"1850\",\"qty\":\"4\",\"sub\":7400}]'),
(5, 'ORD-271039', '2025-12-29', 0.00, 1850.00, 1850.00, '[{\"code\":\"KBD-USB\",\"name\":\"USB Standard Keyboard\",\"price\":\"1850\",\"qty\":\"1\",\"sub\":1850}]'),
(6, 'ORD-271039', '2025-12-29', 0.00, 73500.00, 73500.00, '[{\"code\":\"MON-22\",\"name\":\"22 Inch LED Monitor\",\"price\":\"24500\",\"qty\":\"3\",\"sub\":73500}]'),
(7, 'ORD-271039', '2025-12-29', 0.00, 24500.00, 24500.00, '[{\"code\":\"MON-22\",\"name\":\"22 Inch LED Monitor\",\"price\":\"24500\",\"qty\":\"1\",\"sub\":24500}]'),
(8, 'ORD-271039', '2025-12-29', 0.00, 8500.00, 8500.00, '[{\"code\":\"RAM-8GB-D4\",\"name\":\"8GB DDR4 RAM\",\"price\":\"8500\",\"qty\":\"1\",\"sub\":8500}]'),
(9, 'ORD-271039', '2025-12-29', 500.00, 850.00, 1350.00, '[{\"code\":\"PWR-CB-L\",\"name\":\"Laptop Power Cable\",\"price\":\"850\",\"qty\":\"1\",\"sub\":850}]'),
(10, 'ORD-271039', '2025-12-29', 0.00, 14500.00, 14500.00, '[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]'),
(11, 'ORD-271039', '2025-12-29', 1000.00, 9500.00, 10500.00, '[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]'),
(12, 'ORD-271039', '2025-12-29', 0.00, 9500.00, 9500.00, '[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]'),
(13, 'ORD-271039', '2025-12-29', 0.00, 14500.00, 14500.00, '[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]'),
(14, 'ORD-271039', '2025-12-29', 0.00, 150.00, 150.00, '[{\"code\":\"CMOS-BAT\",\"name\":\"CMOS Battery (CR2032)\",\"price\":\"150\",\"qty\":\"1\",\"sub\":150}]'),
(15, 'ORD-271039', '0000-00-00', 10010111.00, 8500.00, 10018611.00, '[{\"code\":\"RAM-8GB-D4\",\"name\":\"8GB DDR4 RAM\",\"price\":\"8500.00\",\"qty\":\"1\",\"sub\":8500}]'),
(16, 'ORD-271038', '0000-00-00', 200.00, 2850.00, 3050.00, '[{\"code\":\"TH-PASTE\",\"name\":\"Thermal Paste (Arctic Silver)\",\"price\":\"950\",\"qty\":\"3\",\"sub\":2850}]'),
(17, 'ORD-271037', '0000-00-00', 2000.00, 9500.00, 11500.00, '[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]'),
(18, 'ORD-271042', '0000-00-00', 0.00, 5000.00, 5000.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]'),
(19, 'ORD-271043', '0000-00-00', 124.00, 14500.00, 14624.00, '[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]'),
(20, 'ORD-271042', '0000-00-00', 678.00, 5799.00, 6477.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000},{\"code\":\"itm-900\",\"name\":\"HP Envy\",\"price\":\"799\",\"qty\":\"1\",\"sub\":799}]'),
(21, 'ORD-271047', '0000-00-00', 600.00, 5000.00, 5800.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]'),
(22, 'ORD-271048', '0000-00-00', 200.00, 14500.00, 14900.00, '[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]');

-- --------------------------------------------------------

--
-- Table structure for table `issue`
--

CREATE TABLE `issue` (
  `issue_id` int(11) NOT NULL,
  `issue_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job`
--

CREATE TABLE `job` (
  `job_no` varchar(20) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `job_date` date NOT NULL,
  `job_status` varchar(20) DEFAULT 'Pending',
  `item_category` varchar(100) DEFAULT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `problem_type` varchar(100) DEFAULT NULL,
  `problem_severity` varchar(50) DEFAULT NULL,
  `technician_experience_years` int(11) DEFAULT NULL,
  `workshop_workload` varchar(50) DEFAULT NULL,
  `actual_repair_time_days` int(11) DEFAULT NULL,
  `technician_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job`
--

INSERT INTO `job` (`job_no`, `phone_number`, `job_date`, `job_status`, `item_category`, `brand`, `problem_type`, `problem_severity`, `technician_experience_years`, `workshop_workload`, `actual_repair_time_days`, `technician_id`) VALUES
('ORD-1612', '089657883', '2025-12-27', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271031', '0761817517', '2025-12-27', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4),
('ORD-271032', '0761817517', '2025-12-28', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271033', '0987588845', '2025-12-28', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271034', '0761817518', '2025-12-28', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 9),
('ORD-271035', '0768955775', '2025-12-28', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 9),
('ORD-271036', '07889565757575', '2025-12-28', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271037', '089677333', '2025-12-28', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
('ORD-271038', '0720417529', '2025-12-28', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 9),
('ORD-271039', '0720417529', '2025-12-28', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
('ORD-271040', '0761817517', '2025-12-28', 'Pending', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271041', '0761817517', '2025-12-29', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271042', '0768483170', '2025-12-30', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271043', '0768483170', '2025-12-30', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
('ORD-271044', '0761817517', '2026-01-05', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
('ORD-271045', '0765333721', '2026-01-05', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271046', '0761817517', '2026-01-05', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
('ORD-271047', '0761817517', '2026-01-05', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 10),
('ORD-271048', '0761817517', '2026-01-05', 'Approved', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `job_device`
--

CREATE TABLE `job_device` (
  `job_device_id` int(11) NOT NULL,
  `job_no` varchar(20) DEFAULT NULL,
  `device_name` varchar(100) NOT NULL,
  `model` varchar(100) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `warranty_status` varchar(50) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `device_image` varchar(255) DEFAULT NULL,
  `issue_name` varchar(255) DEFAULT NULL,
  `device_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `completed_date` datetime DEFAULT NULL,
  `destroy_notice_sent_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_device`
--

INSERT INTO `job_device` (`job_device_id`, `job_no`, `device_name`, `model`, `serial_no`, `warranty_status`, `supplier_name`, `description`, `device_image`, `issue_name`, `device_status`, `completed_date`, `destroy_notice_sent_date`) VALUES
(26, 'ORD-1612', 'mouse', '678', NULL, NULL, NULL, NULL, NULL, 'not on', 'Pending', NULL, NULL),
(29, 'ORD-271032', 'printers', NULL, NULL, 'Warranty', 'Abans', '', NULL, 'no power', 'Sent to Warranty', NULL, NULL),
(31, 'ORD-271034', 'Desktop', NULL, NULL, '', NULL, NULL, NULL, 'Battery', 'Pending', NULL, NULL),
(32, 'ORD-271034', 'Laptop', NULL, NULL, '', NULL, NULL, NULL, 'Display/LCD', 'Pending', NULL, NULL),
(33, 'ORD-271035', 'Printer', NULL, NULL, 'Warranty', 'abans', NULL, NULL, 'Power', 'Completed', NULL, NULL),
(34, 'ORD-271036', 'Printer', NULL, NULL, 'Warranty', NULL, NULL, NULL, 'Service', 'Pending', NULL, NULL),
(35, 'ORD-271037', 'Printer', NULL, NULL, 'Warranty', NULL, NULL, NULL, 'Power', 'billed', NULL, NULL),
(36, 'ORD-271038', 'Mobile', NULL, NULL, 'No Warranty', NULL, '', '', 'Software', 'billed', NULL, NULL),
(37, 'ORD-271039', 'Laptop', NULL, NULL, 'Warranty', NULL, '', 'IMG_69511e013e37a_0.jpg', 'Charging', 'billed', NULL, NULL),
(38, 'ORD-271040', 'Laptop', NULL, NULL, 'Warranty', NULL, 'sim', 'IMG_6951aafa549d2_0.png', 'Software', 'Pending', NULL, NULL),
(41, 'ORD-271042', 'Mobile', NULL, NULL, 'Warranty', 'singer', 'backcover', 'IMG_6953cb326638f_0.png', 'Power', 'billed', '2025-09-01 00:00:00', NULL),
(42, 'ORD-271043', 'Mobile', NULL, NULL, 'No Warranty', NULL, '', '', 'Display', 'billed', NULL, NULL),
(43, 'ORD-271044', 'Mobile', NULL, NULL, 'No Warranty', NULL, 'back cover', '', 'Software', 'Destroyed', '2026-01-05 11:06:24', NULL),
(44, 'ORD-271045', 'Mobile', NULL, NULL, 'No Warranty', NULL, '', '', 'Display', 'Destroyed', '2024-12-25 00:00:00', '2026-01-05 12:18:53'),
(45, 'ORD-271046', 'Desktop', NULL, NULL, 'No Warranty', NULL, '', '', 'Service', 'Destroyed', '2024-12-25 00:00:00', '2025-12-25 00:00:00'),
(46, 'ORD-271047', 'Mobile', NULL, NULL, 'No Warranty', NULL, '', '', 'Power', 'billed', '2025-09-05 00:00:00', '0000-00-00 00:00:00'),
(48, 'ORD-271048', 'Mobile', NULL, NULL, 'No Warranty', NULL, '', '', 'Display', 'billed', '2025-09-05 11:47:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `job_device_issue`
--

CREATE TABLE `job_device_issue` (
  `id` int(11) NOT NULL,
  `job_device_id` int(11) DEFAULT NULL,
  `issue_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_parts`
--

CREATE TABLE `job_parts` (
  `id` int(11) NOT NULL,
  `job_device_id` int(11) DEFAULT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `invoice_no` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock`
--

INSERT INTO `stock` (`item_code`, `item_name`, `category_id`, `quantity`, `unit_price`, `status`) VALUES
('CMOS-BAT', 'CMOS Battery (CR2032)', NULL, 96, 150.00, 'In Stock'),
('cms-12', 'Dell OptiPlex', 1, 16, 5000.00, 'In Stock'),
('FAN-CPU', 'CPU Cooling Fan', NULL, 12, 1650.00, NULL),
('itm-900', 'HP Envy', 4, 20, 799.00, 'In Stock'),
('KBD-USB', 'USB Standard Keyboard', NULL, 0, 1850.00, 'In Stock'),
('MON-22', '22 Inch LED Monitor', NULL, 4, 24500.00, 'In Stock'),
('MSE-OPT', 'Optical USB Mouse', NULL, 24, 1200.00, NULL),
('PWR-CB-L', 'Laptop Power Cable', NULL, 14, 850.00, NULL),
('RAM-8GB-D4', '8GB DDR4 RAM', NULL, 48, 8500.00, NULL),
('SSD-256GB', '256GB NVMe SSD', NULL, 27, 9500.00, NULL),
('SSD-512GB', '512GB SATA SSD', NULL, 16, 14500.00, NULL),
('TH-PASTE', 'Thermal Paste (Arctic Silver)', NULL, 37, 950.00, NULL),
('ttm-90', 'Samsung 27-inch', 2, 34, 789.00, 'In Stock');

-- --------------------------------------------------------

--
-- Table structure for table `technicians`
--

CREATE TABLE `technicians` (
  `technician_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `technicians`
--

INSERT INTO `technicians` (`technician_id`, `name`) VALUES
(1, 'hasindu'),
(10, 'panda'),
(9, 'sasintha'),
(4, 'sauru');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`acc_id`);

--
-- Indexes for table `cashbook`
--
ALTER TABLE `cashbook`
  ADD PRIMARY KEY (`cashid`),
  ADD KEY `invoice_no` (`invoice_no`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`phone_number`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoice_no`),
  ADD KEY `job_no` (`job_no`);

--
-- Indexes for table `issue`
--
ALTER TABLE `issue`
  ADD PRIMARY KEY (`issue_id`);

--
-- Indexes for table `job`
--
ALTER TABLE `job`
  ADD PRIMARY KEY (`job_no`),
  ADD KEY `phone_number` (`phone_number`),
  ADD KEY `fk_job_technician` (`technician_id`);

--
-- Indexes for table `job_device`
--
ALTER TABLE `job_device`
  ADD PRIMARY KEY (`job_device_id`),
  ADD KEY `job_no` (`job_no`);

--
-- Indexes for table `job_device_issue`
--
ALTER TABLE `job_device_issue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_device_id` (`job_device_id`),
  ADD KEY `issue_id` (`issue_id`);

--
-- Indexes for table `job_parts`
--
ALTER TABLE `job_parts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_device_id` (`job_device_id`),
  ADD KEY `item_code` (`item_code`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `invoice_no` (`invoice_no`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`item_code`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `technicians`
--
ALTER TABLE `technicians`
  ADD PRIMARY KEY (`technician_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `acc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cashbook`
--
ALTER TABLE `cashbook`
  MODIFY `cashid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoice_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `issue`
--
ALTER TABLE `issue`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_device`
--
ALTER TABLE `job_device`
  MODIFY `job_device_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `job_device_issue`
--
ALTER TABLE `job_device_issue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_parts`
--
ALTER TABLE `job_parts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `technicians`
--
ALTER TABLE `technicians`
  MODIFY `technician_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`job_no`) REFERENCES `job` (`job_no`) ON DELETE CASCADE;

--
-- Constraints for table `job`
--
ALTER TABLE `job`
  ADD CONSTRAINT `fk_job_technician` FOREIGN KEY (`technician_id`) REFERENCES `technicians` (`technician_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `job_ibfk_1` FOREIGN KEY (`phone_number`) REFERENCES `customer` (`phone_number`) ON DELETE CASCADE;

--
-- Constraints for table `job_device`
--
ALTER TABLE `job_device`
  ADD CONSTRAINT `job_device_ibfk_1` FOREIGN KEY (`job_no`) REFERENCES `job` (`job_no`) ON DELETE CASCADE;

--
-- Constraints for table `job_device_issue`
--
ALTER TABLE `job_device_issue`
  ADD CONSTRAINT `job_device_issue_ibfk_1` FOREIGN KEY (`job_device_id`) REFERENCES `job_device` (`job_device_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_device_issue_ibfk_2` FOREIGN KEY (`issue_id`) REFERENCES `issue` (`issue_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_parts`
--
ALTER TABLE `job_parts`
  ADD CONSTRAINT `job_parts_ibfk_1` FOREIGN KEY (`job_device_id`) REFERENCES `job_device` (`job_device_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_parts_ibfk_2` FOREIGN KEY (`item_code`) REFERENCES `stock` (`item_code`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`invoice_no`) REFERENCES `invoice` (`invoice_no`) ON DELETE CASCADE;

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
