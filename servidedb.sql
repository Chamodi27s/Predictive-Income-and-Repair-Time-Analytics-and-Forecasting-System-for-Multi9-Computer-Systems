-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2026 at 05:10 PM
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
(2, 'BOC Account', '123456789', 25456.00),
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
(29, '22', '2026-01-05', 14900.00, 10140070.00, NULL),
(30, '23', '2026-01-10', 9500.00, 10149570.00, NULL),
(31, '24', '2026-01-10', 1650.00, 10151220.00, NULL),
(32, '25', '2026-01-11', 16450.00, 10167670.00, NULL),
(33, '26', '2026-03-06', 5400.00, 10173070.00, NULL),
(34, '27', '2026-03-06', 9700.00, 10182770.00, NULL),
(35, '28', '2026-03-06', 14700.00, 10197470.00, NULL),
(36, '30', '2026-03-06', 6250.00, 10203720.00, NULL),
(37, '29', '2026-03-06', 5356.00, 10209076.00, NULL),
(38, '32', '2026-03-06', 9900.00, 10218976.00, NULL),
(39, '31', '2026-03-06', 9500.00, 10228476.00, NULL),
(40, '33', '2026-03-06', 5200.00, 10233676.00, NULL),
(41, '35', '2026-03-06', 14500.00, 10248176.00, NULL),
(42, '38', '2026-03-06', 5100.00, 10253276.00, NULL),
(43, '39', '2026-03-06', 1050.00, 10254326.00, NULL),
(44, '40', '2026-03-06', 1650.00, 10255976.00, NULL),
(45, '41', '2026-03-06', 5000.00, 10260976.00, NULL),
(46, '42', '2026-03-06', 799.00, 10261775.00, NULL),
(47, '42', '2026-03-06', 799.00, 10262574.00, NULL),
(48, '45', '2026-03-06', 24500.00, 10287074.00, NULL),
(49, '46', '2026-03-06', 5000.00, 10292074.00, NULL),
(50, '47', '2026-03-06', 1650.00, 10293724.00, NULL),
(51, '50', '2026-03-07', 5200.00, 10298924.00, NULL),
(52, 'online', '2026-03-06', 2000.00, 10300924.00, 2),
(53, '49', '2026-03-07', 14700.00, 10315624.00, NULL),
(54, '52', '2026-03-07', 5400.00, 10321024.00, NULL),
(55, '53', '2026-03-10', 5399.00, 10326423.00, NULL),
(56, '48', '2026-04-03', 1850.00, 10328273.00, NULL),
(57, '66', '2026-04-03', 5700.00, 10333973.00, NULL),
(58, '61', '2026-04-03', 2650.00, 10336623.00, NULL),
(59, '60', '2026-04-03', 5000.00, 10341623.00, NULL),
(60, '69', '2026-04-03', 9800.00, 10351423.00, NULL),
(61, '67', '2026-08-11', 9200.00, 10360623.00, NULL),
(62, '69', '2026-08-11', 10300.00, 10370923.00, NULL);

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
('0741122153', 'saduni vindya', 'waliweriya', 'sadu@gmil.com'),
('0742524527', 'malindi', 'galle', 'mali@gamil.com'),
('0761817517', 'hashini umnda', 'Alawwa,Kappitiwalana', 'hashi@gmail.com'),
('0761817518', 'lavan abishek', NULL, NULL),
('0765333721', 'anuththara', 'galdeniya', ''),
('0765333722', 'anuththara', NULL, NULL),
('0768456788', 'shiwardan', 'minu', 'shiwa@gmail.com'),
('0768483156', 'anuththra imanshi Amarasingha', 'galdeniya ,kappitiwaklana', 'anu@gmail.com'),
('0768483170', 'raniluuu', 'umandawa', 'ra@gmail.com'),
('07685947', 'pdmika', 'bchbjj vjhvhbv', 'padmi@gmail.com'),
('0768955775', 'maheee', NULL, NULL),
('0786798654', 'amal perera', 'alwwa,galdeniya', 'amal@gamil.com'),
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
  `job_device_id` int(11) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `service_charge` decimal(10,2) DEFAULT 0.00,
  `parts_total` decimal(10,2) DEFAULT 0.00,
  `late_fee` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `balance_due` decimal(10,2) DEFAULT 0.00,
  `items_json` text DEFAULT NULL,
  `solution` text DEFAULT NULL,
  `payment_status` enum('Paid','Pending') DEFAULT 'Paid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`invoice_no`, `job_no`, `job_device_id`, `invoice_date`, `service_charge`, `parts_total`, `late_fee`, `grand_total`, `balance_due`, `items_json`, `solution`, `payment_status`) VALUES
(16, 'ORD-271038', NULL, '0000-00-00', 200.00, 2850.00, 0.00, 3050.00, 0.00, '[{\"code\":\"TH-PASTE\",\"name\":\"Thermal Paste (Arctic Silver)\",\"price\":\"950\",\"qty\":\"3\",\"sub\":2850}]', NULL, 'Paid'),
(17, 'ORD-271037', NULL, '0000-00-00', 2000.00, 9500.00, 0.00, 11500.00, 0.00, '[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]', NULL, 'Paid'),
(18, 'ORD-271042', NULL, '0000-00-00', 0.00, 5000.00, 0.00, 5000.00, 0.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]', NULL, 'Paid'),
(19, 'ORD-271043', NULL, '0000-00-00', 124.00, 14500.00, 0.00, 14624.00, 0.00, '[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]', NULL, 'Paid'),
(20, 'ORD-271042', NULL, '0000-00-00', 678.00, 5799.00, 0.00, 6477.00, 0.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000},{\"code\":\"itm-900\",\"name\":\"HP Envy\",\"price\":\"799\",\"qty\":\"1\",\"sub\":799}]', NULL, 'Paid'),
(26, 'ORD-271061', NULL, '2026-03-06', 400.00, 5000.00, 0.00, 5400.00, 0.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]', NULL, 'Paid'),
(28, 'ORD-271062', NULL, '2026-03-06', 200.00, 14500.00, 0.00, 14700.00, 0.00, '[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]', NULL, 'Paid'),
(29, 'ORD-271060', NULL, '2026-03-06', 356.00, 5000.00, 0.00, 5356.00, 0.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]', NULL, 'Paid'),
(30, 'ORD-271063', NULL, '2026-03-06', 400.00, 5850.00, 0.00, 6250.00, 0.00, '[{\"code\":\"PWR-CB-L\",\"name\":\"Laptop Power Cable\",\"price\":\"850\",\"qty\":\"1\",\"sub\":850},{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]', NULL, 'Paid'),
(31, 'ORD-271064', NULL, '2026-03-06', 1000.00, 8500.00, 0.00, 9500.00, 0.00, '[{\"code\":\"RAM-8GB-D4\",\"name\":\"8GB DDR4 RAM\",\"price\":\"8500\",\"qty\":\"1\",\"sub\":8500}]', NULL, 'Paid'),
(32, 'ORD-271065', NULL, '2026-03-06', 400.00, 9500.00, 0.00, 9900.00, 0.00, '[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]', NULL, 'Paid'),
(33, 'ORD-271066', NULL, '2026-03-06', 100.00, 5000.00, 0.00, 5200.00, 0.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]', NULL, 'Paid'),
(35, 'ORD-271068', NULL, '2026-03-06', 0.00, 14500.00, 0.00, 14500.00, 0.00, '[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]', NULL, 'Paid'),
(39, 'ORD-271069', NULL, '2026-03-06', 100.00, 950.00, 0.00, 1050.00, 0.00, '[{\"code\":\"TH-PASTE\",\"name\":\"Thermal Paste (Arctic Silver)\",\"price\":\"950\",\"qty\":\"1\",\"sub\":950}]', NULL, 'Paid'),
(40, 'ORD-271070', NULL, '2026-03-06', 0.00, 1650.00, 0.00, 1650.00, 0.00, '[{\"code\":\"FAN-CPU\",\"name\":\"CPU Cooling Fan\",\"price\":\"1650\",\"qty\":\"1\",\"sub\":1650}]', NULL, 'Paid'),
(41, 'ORD-271071', NULL, '2026-03-06', 0.00, 5000.00, 0.00, 5000.00, 0.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]', NULL, 'Paid'),
(43, 'ORD-271072', NULL, '2026-03-06', 0.00, 8500.00, 0.00, 8500.00, 0.00, '[{\"code\":\"RAM-8GB-D4\",\"name\":\"8GB DDR4 RAM\",\"price\":\"8500\",\"qty\":\"1\",\"sub\":8500}]', NULL, 'Pending'),
(45, 'ORD-271073', NULL, '2026-03-06', 100.00, 24500.00, 0.00, 24500.00, 0.00, '[{\"code\":\"MON-22\",\"name\":\"22 Inch LED Monitor\",\"price\":\"24500\",\"qty\":\"1\",\"sub\":24500}]', NULL, 'Paid'),
(46, 'ORD-271074', NULL, '2026-03-06', 100.00, 5000.00, 0.00, 5000.00, 0.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]', NULL, 'Paid'),
(47, 'ORD-271075', NULL, '2026-03-06', 200.00, 1650.00, 0.00, 1650.00, 0.00, '[{\"code\":\"FAN-CPU\",\"name\":\"CPU Cooling Fan\",\"price\":\"1650\",\"qty\":\"1\",\"sub\":1650}]', NULL, 'Paid'),
(49, 'ORD-271077', NULL, '2026-03-06', 200.00, 14500.00, 0.00, 14700.00, 0.00, '[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]', NULL, 'Paid'),
(50, 'ORD-271079', NULL, '2026-03-07', 200.00, 5000.00, 0.00, 5200.00, 0.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]', NULL, 'Paid'),
(52, 'ORD-271080', NULL, '2026-03-07', 400.00, 5000.00, 0.00, 5400.00, 0.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]', NULL, 'Paid'),
(61, 'ORD-271084', NULL, '2026-04-02', 1000.00, 1650.00, 900.00, 3550.00, 3550.00, '[{\"code\":\"FAN-CPU\",\"name\":\"CPU Cooling Fan\",\"price\":\"1650\",\"qty\":\"1\",\"sub\":1650}]', NULL, 'Paid'),
(66, 'ORD-271086', NULL, '2026-04-03', 400.00, 5000.00, 300.00, 5700.00, 0.00, '[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]', NULL, 'Paid'),
(67, 'ORD-271095', NULL, '2026-08-11', 700.00, 8500.00, 0.00, 9200.00, 0.00, '[{\"code\":\"RAM-8GB-D4\",\"name\":\"8GB DDR4 RAM\",\"price\":\"8500\",\"qty\":\"1\",\"sub\":8500}]', NULL, 'Paid'),
(68, 'ORD-271098', NULL, '2026-08-11', 400.00, 950.00, 0.00, 1350.00, 850.00, '[{\"code\":\"TH-PASTE\",\"name\":\"Thermal Paste (Arctic Silver)\",\"price\":\"950\",\"qty\":\"1\",\"sub\":950}]', NULL, 'Pending'),
(69, 'ORD-271083', NULL, '2026-08-11', 500.00, 9500.00, 300.00, 10300.00, 0.00, '[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]', NULL, 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `issue`
--

CREATE TABLE `issue` (
  `issue_id` int(11) NOT NULL,
  `issue_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issue`
--

INSERT INTO `issue` (`issue_id`, `issue_name`, `description`) VALUES
(1, 'repair', NULL),
(2, 'keyboard air', NULL),
(3, 'key board', NULL),
(4, 'motherdoard problem', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `job`
--

CREATE TABLE `job` (
  `job_no` varchar(20) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `job_date` date NOT NULL,
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

INSERT INTO `job` (`job_no`, `phone_number`, `job_date`, `item_category`, `brand`, `problem_type`, `problem_severity`, `technician_experience_years`, `workshop_workload`, `actual_repair_time_days`, `technician_id`) VALUES
('ORD-1612', '089657883', '2025-12-27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271033', '0987588845', '2025-12-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271034', '0761817518', '2025-12-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 9),
('ORD-271035', '0768955775', '2025-12-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 9),
('ORD-271036', '07889565757575', '2025-12-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271037', '089677333', '2025-12-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271038', '0720417529', '2025-12-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 9),
('ORD-271039', '0720417529', '2025-12-28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271042', '0768483170', '2025-12-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271043', '0768483170', '2025-12-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271045', '0765333721', '2026-01-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271052', '0786798654', '2026-02-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271056', '0768483156', '2026-03-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11),
('ORD-271057', '0768483156', '2026-03-03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271059', '0768483156', '2026-03-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271060', '0742524527', '2026-03-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11),
('ORD-271061', '0720417529', '2026-03-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271062', '0741122153', '2026-03-05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271063', '0768483156', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271064', '0742524527', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11),
('ORD-271065', '0742524527', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271066', '0768483156', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271068', '0768483156', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271069', '0742524527', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11),
('ORD-271070', '0768483156', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271071', '0768483156', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271072', '0768483156', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271073', '0768483156', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271074', '0768483156', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271075', '0768483156', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271077', '0768483156', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11),
('ORD-271078', '0741122153', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271079', '0741122153', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271080', '0741122153', '2026-03-06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 11),
('ORD-271082', '0741122153', '2026-03-07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271083', '0768483156', '2026-03-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271084', '0768483156', '2026-03-09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271085', '0768483156', '2026-04-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271086', '0768483156', '2026-04-01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
('ORD-271090', '0768483156', '2026-08-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 9),
('ORD-271091', '0761817517', '2026-08-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 4),
('ORD-271092', '0761817517', '2026-08-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271093', '0768483156', '2026-08-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271094', '0761817517', '2026-08-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 9),
('ORD-271095', '0761817517', '2026-08-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271096', '0761817517', '2026-08-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 9),
('ORD-271097', '0761817517', '2026-08-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271098', '0761817517', '2026-08-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1),
('ORD-271099', '0761817517', '2026-08-11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);

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
  `another_note` text DEFAULT NULL,
  `device_image` varchar(255) DEFAULT NULL,
  `issue_name` varchar(255) DEFAULT NULL,
  `device_status` varchar(50) NOT NULL DEFAULT 'Pending',
  `completed_date` datetime DEFAULT NULL,
  `destroy_notice_sent_date` datetime DEFAULT NULL,
  `issue_category` enum('Hardware','Software') DEFAULT 'Hardware',
  `solution` text DEFAULT NULL,
  `final_status` varchar(50) DEFAULT 'Pending',
  `rent_warning_sent` int(11) DEFAULT 0,
  `parts_json` text DEFAULT NULL,
  `last_sms_sent_date` date DEFAULT NULL,
  `item_model` varchar(255) DEFAULT NULL,
  `repair_path` varchar(255) DEFAULT 'Carry-In',
  `job_status` varchar(50) DEFAULT 'Pending',
  `estimated_cost` decimal(10,2) DEFAULT 0.00,
  `advance_paid` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_device`
--

INSERT INTO `job_device` (`job_device_id`, `job_no`, `device_name`, `model`, `serial_no`, `warranty_status`, `supplier_name`, `description`, `another_note`, `device_image`, `issue_name`, `device_status`, `completed_date`, `destroy_notice_sent_date`, `issue_category`, `solution`, `final_status`, `rent_warning_sent`, `parts_json`, `last_sms_sent_date`, `item_model`, `repair_path`, `job_status`, `estimated_cost`, `advance_paid`) VALUES
(26, 'ORD-1612', 'mouse', '678', NULL, NULL, NULL, NULL, NULL, NULL, 'not on', 'Pending', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Pending', 0.00, 0.00),
(31, 'ORD-271034', 'Desktop', NULL, NULL, '', NULL, NULL, NULL, NULL, 'Battery', 'Pending', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Pending', 0.00, 0.00),
(32, 'ORD-271034', 'Laptop', NULL, NULL, '', NULL, NULL, NULL, NULL, 'Display/LCD', 'Pending', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Pending', 0.00, 0.00),
(33, 'ORD-271035', 'Printer', NULL, NULL, 'Warranty', 'abans', NULL, NULL, NULL, 'Power', 'Completed', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Pending', 0.00, 0.00),
(34, 'ORD-271036', 'Printer', NULL, NULL, 'Warranty', NULL, NULL, NULL, NULL, 'Service', 'Pending', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Pending', 0.00, 0.00),
(35, 'ORD-271037', 'Printer', NULL, NULL, 'Warranty', NULL, NULL, NULL, NULL, 'Power', 'billed', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(36, 'ORD-271038', 'Mobile', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'Software', 'billed', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(37, 'ORD-271039', 'Laptop', NULL, NULL, 'Warranty', NULL, '', NULL, 'IMG_69511e013e37a_0.jpg', 'Charging', 'billed', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(41, 'ORD-271042', 'Mobile', NULL, NULL, 'Warranty', 'singer', 'backcover', NULL, 'IMG_6953cb326638f_0.png', 'Power', 'billed', '2025-09-01 00:00:00', NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(42, 'ORD-271043', 'Mobile', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'Display', 'billed', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(44, 'ORD-271045', 'Mobile', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'Display', 'Destroyed', '2024-12-25 00:00:00', '2026-01-05 12:18:53', 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(52, 'ORD-271052', 'Moble', NULL, NULL, 'Warranty', NULL, 'back coner', NULL, '', 'power', 'Pending', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Pending', 0.00, 0.00),
(56, 'ORD-271056', 'Printer', NULL, NULL, 'Warranty', '', '', NULL, '', 'new', 'Pending', NULL, NULL, 'Software', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Pending', 0.00, 0.00),
(57, 'ORD-271057', 'Printer', NULL, NULL, 'Warranty', '', 'bag', NULL, 'IMG_69a6d8badbc3b_0.png', 'repair', 'Pending', NULL, NULL, 'Software', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Pending', 0.00, 0.00),
(60, 'ORD-271060', 'Laptop', NULL, NULL, 'No Warranty', NULL, 'bag', NULL, 'IMG_69a9b6934f610_0.png', 'Service', 'billed', '2026-03-05 22:36:53', NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(61, 'ORD-271061', 'Laptop', NULL, NULL, 'No Warranty', NULL, 'mouse', NULL, 'IMG_69a9f6694a531_0.png', 'Service', 'billed', '2026-03-05 21:58:32', NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(62, 'ORD-271062', 'Laptop', NULL, NULL, 'No Warranty', NULL, 'bag', NULL, 'IMG_69a9f89775674_0.png', 'Display Damage', 'billed', '2026-03-05 22:09:12', NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(63, 'ORD-271063', 'Laptop', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'Display Damage', 'billed', '2026-03-05 23:28:32', NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 15000.00, 0.00),
(64, 'ORD-271064', 'Laptop', NULL, NULL, 'No Warranty', NULL, 'bag cover', NULL, 'IMG_69aa15a1dfe95_0.png', 'Service', 'billed', '2026-03-05 23:49:57', NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 12000.00, 0.00),
(65, 'ORD-271065', 'Laptop', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'No Power', 'billed', '2026-03-06 00:00:46', NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 12555.00, 0.00),
(66, 'ORD-271066', 'Printer', NULL, NULL, 'No Warranty', NULL, 'bag', NULL, 'IMG_69aac857e36b3_0.png', 'Display Damage', 'billed', '2025-11-06 12:28:53', NULL, 'Hardware', NULL, 'Pending', 1, NULL, NULL, NULL, 'Carry-In', 'Approved', 5688.00, 0.00),
(68, 'ORD-271068', 'Laptop', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'No Power', 'Completed', '2026-03-06 12:55:37', NULL, 'Hardware', NULL, 'Pending', 0, NULL, '2026-05-16', NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(70, 'ORD-271070', 'Laptop', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'No Power', 'billed', '2026-03-06 13:32:01', NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(71, 'ORD-271071', 'Printer', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'No Power', 'billed', '2026-03-06 13:34:00', NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(73, 'ORD-271073', 'Laptop', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'No Power', 'Completed', '2026-03-06 14:09:51', NULL, 'Hardware', NULL, 'Pending', 0, NULL, '2026-05-16', NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(74, 'ORD-271074', 'Laptop', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'repair', 'Completed', '2025-11-06 14:11:29', NULL, 'Hardware', NULL, 'Pending', 1, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(75, 'ORD-271075', 'Printer', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'Display Damage', 'Completed', '2025-11-06 14:13:40', NULL, 'Hardware', NULL, 'Pending', 1, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(77, 'ORD-271077', 'Desktop', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'No Power', 'Completed', '2026-03-06 14:48:57', NULL, 'Hardware', NULL, 'Pending', 0, NULL, '2026-05-16', NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(78, 'ORD-271078', 'Laptop', NULL, NULL, 'No Warranty', NULL, 'laptop bag', NULL, 'IMG_69ab3058175c5_0.png', 'No Power', 'Pending', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 200.00, 50.00),
(79, 'ORD-271079', 'Laptop', NULL, NULL, 'No Warranty', NULL, 'lap bag', NULL, 'IMG_69ab3106c684b_0.png', 'No Power', 'Destroyed', '2025-03-06 19:58:23', '2026-06-11 00:00:00', 'Hardware', NULL, 'Pending', 1, NULL, NULL, NULL, 'Carry-In', 'Approved', 12490.00, 0.00),
(80, 'ORD-271080', 'Laptop', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'No Power', 'Destroyed', '2025-03-06 20:13:28', '2026-03-06 20:14:56', 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(82, 'ORD-271082', 'Laptop', NULL, NULL, 'No Warranty', NULL, 'bag', NULL, 'IMG_69ac88063ffe8_0.png', 'Display Damage', 'Completed', '2026-03-31 22:41:32', NULL, 'Hardware', '', 'Pending', 0, NULL, '2026-08-11', NULL, 'Carry-In', 'Approved', 12000.00, 0.00),
(83, 'ORD-271083', 'Printer', NULL, NULL, 'No Warranty', NULL, '', NULL, '', 'No Power', 'billed', '2026-03-09 19:40:47', NULL, 'Hardware', '', 'Pending', 0, 'ram-10000 battery-2000', '2026-04-02', NULL, 'Carry-In', 'Approved', 12000.00, 0.00),
(84, 'ORD-271084', 'Laptop', NULL, NULL, 'No Warranty', NULL, '', NULL, '', '', 'Returned', '2025-09-01 00:00:00', NULL, 'Hardware', '', 'Pending', 0, NULL, '2026-04-02', NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(85, 'ORD-271085', 'Printer', NULL, NULL, 'No Warranty', NULL, 'dddddddddd', '', '', 'key board', 'Destroyed', '2025-06-11 21:54:42', '2026-06-11 00:00:00', 'Hardware', '', 'Pending', 0, 'ram-4000.disply 1000', '2026-06-11', NULL, 'Carry-In', 'Approved', 5000.00, 1000.00),
(86, 'ORD-271086', 'Desktop', NULL, NULL, 'No Warranty', NULL, '', '', '', 'key board', 'billed', '2026-09-02 13:59:36', NULL, 'Hardware', '', 'Pending', 0, 'ram -5900', '2026-04-02', NULL, 'Carry-In', 'Approved', 5900.00, 4000.00),
(89, 'ORD-271090', 'Desktop PC', NULL, NULL, 'Warranty', 'singar', 'mouse', '', 'IMG_6a7ac154d561a_0.jpg', 'motherdoard problem', 'Sent to Warranty', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Pending', 0.00, 0.00),
(90, 'ORD-271091', 'Desktop PC', NULL, NULL, 'No', NULL, 'mouse', '', 'IMG_6a7ac2305c45d_0.jpg', 'No Power', 'Pending', NULL, NULL, 'Software', NULL, 'Pending', 0, 'ram-7000', NULL, NULL, 'Carry-In', 'Approved', 7000.00, 2000.00),
(91, 'ORD-271092', 'Desktop PC', NULL, NULL, 'No', NULL, 'mouse', '', 'IMG_6a7ac25ea2455_0.jpg', 'key board', 'Pending', NULL, NULL, 'Software', NULL, 'Pending', 0, 'ram-70000', NULL, NULL, 'Carry-In', 'Approved', 7000.00, 2000.00),
(92, 'ORD-271093', 'Desktop PC', NULL, NULL, 'No', NULL, 'mouse,bag', '', 'IMG_6a7ac2c53e5a6_0.jpg', 'Service', 'Pending', NULL, NULL, 'Software', NULL, 'Pending', 0, 'ram-7000', NULL, NULL, 'Carry-In', 'Approved', 0.00, 3000.00),
(93, 'ORD-271094', 'Desktop PC', NULL, NULL, 'No', NULL, 'mouse', '', 'IMG_6a7ac308de5a5_0.jpg', 'Display Damage', 'Pending', NULL, NULL, 'Hardware', NULL, 'Pending', 0, 'ram -7000', NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(94, 'ORD-271094', 'Laptop', NULL, NULL, 'No', NULL, 'lap bag and mouse', '', 'IMG_6a7ac308dfa44_1.jpg', 'repair', 'Pending', NULL, NULL, '', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 0.00),
(95, 'ORD-271095', 'Desktop PC', NULL, NULL, 'No', NULL, 'mouse', '', 'IMG_6a7acde4f1b93_0.jpg', 'No Power', 'billed', NULL, NULL, '', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 3000.00),
(96, 'ORD-271095', 'Laptop', NULL, NULL, 'No', NULL, 'lap bag and mouse', '', 'IMG_6a7acde4f388a_1.jpeg', 'Display Damage', 'billed', '2026-08-11 14:35:16', NULL, '', '', 'Pending', 0, 'ram1-7000,ssd1-8900', '2026-08-11', NULL, 'Carry-In', 'Approved', 0.00, 3000.00),
(97, 'ORD-271096', 'Desktop PC', NULL, NULL, 'No', NULL, 'mouse', '', 'IMG_6a7ae69040fe0_0.jpg', 'Display Damage', 'Completed', '2025-08-08 20:35:25', NULL, 'Hardware', '', 'Pending', 0, 'ram -5000', '2026-08-11', NULL, 'Carry-In', 'Approved', 5000.00, 4000.00),
(98, 'ORD-271097', 'Desktop PC', NULL, NULL, 'No', NULL, 'mousse', '', '', 'Display Damage', 'Pending', NULL, NULL, 'Hardware', NULL, 'Pending', 0, 'ram-4000', NULL, NULL, 'Carry-In', 'Approved', 4000.00, 1000.00),
(99, 'ORD-271097', 'Printer', NULL, NULL, 'No', NULL, 'mui', '', '', 'Service', 'Completed', '2025-08-07 19:28:42', NULL, 'Hardware', 'service problem', 'Pending', 0, NULL, '2026-08-11', NULL, 'Carry-In', 'Approved', 4000.00, 1000.00),
(100, 'ORD-271098', 'Desktop PC', NULL, NULL, 'No', NULL, '', '', '', 'keyboard air', 'In Progress', NULL, NULL, 'Hardware', '', 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 4000.00, 500.00),
(101, 'ORD-271098', 'Desktop PC', NULL, NULL, 'No', NULL, '', '', '', 'Display Damage', 'In Progress', '2026-08-11 17:25:35', NULL, 'Hardware', '', 'Pending', 0, 'ram: 4000', '2026-08-11', NULL, 'Carry-In', 'Approved', 4000.00, 500.00),
(102, 'ORD-271099', 'Desktop PC', NULL, NULL, 'No', NULL, 'mouse', '', 'IMG_6a7b0f637df36_0.jpg', 'Display Damage', 'Pending', NULL, NULL, 'Hardware', NULL, 'Pending', 0, NULL, NULL, NULL, 'Carry-In', 'Approved', 0.00, 2000.00),
(103, 'ORD-271099', 'Printer', NULL, NULL, 'No', NULL, '', '', '', 'repair', 'Completed', '2026-04-11 19:25:24', NULL, 'Hardware', 'dust', 'Pending', 0, 'ram-6000', '2026-08-11', NULL, 'Carry-In', 'Approved', 6000.00, 300.00);

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
-- Table structure for table `login_users`
--

CREATE TABLE `login_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(10) DEFAULT NULL,
  `token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_users`
--

INSERT INTO `login_users` (`id`, `username`, `email`, `password`, `reset_token`, `token_expiry`) VALUES
(1, 'multi9', 'vibuddha2025@gmail.com', 'multi912#', NULL, NULL);

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
-- Table structure for table `sms_history`
--

CREATE TABLE `sms_history` (
  `sms_id` int(11) NOT NULL,
  `job_device_id` int(11) DEFAULT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms_history`
--

INSERT INTO `sms_history` (`sms_id`, `job_device_id`, `phone_number`, `message`, `sent_at`, `status`) VALUES
(2, 85, '94768483156', 'Multi9: Your Printer (Job #ORD-271085) is now In Progress.', '2026-04-01 11:37:30', 'Success'),
(3, 85, '94768483156', 'Multi9: Your Printer (Job #ORD-271085) is now Completed.', '2026-04-01 11:38:00', 'Success'),
(4, 85, '94768483156', 'Hi anuththra imanshi, your Printer (Job #ORD-271085) is ready at Multi9. Please collect it soon.', '2026-04-01 11:38:00', 'Sent (Auto)'),
(5, 82, '94741122153', 'Hi saduni vindya, your Laptop (Job #ORD-271082) is ready at Multi9. Please collect it soon.', '2026-04-02 11:58:56', 'Sent (Auto)'),
(7, 68, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271068) is ready at Multi9. Please collect it soon.', '2026-04-02 11:58:56', 'Sent (Auto)'),
(8, 73, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271073) is ready at Multi9. Please collect it soon.', '2026-04-02 11:58:56', 'Sent (Auto)'),
(9, 77, '94768483156', 'Hi anuththra imanshi, your Desktop (Job #ORD-271077) is ready at Multi9. Please collect it soon.', '2026-04-02 11:58:56', 'Sent (Auto)'),
(10, 83, '94768483156', 'Hi anuththra imanshi, your Printer (Job #ORD-271083) is ready at Multi9. Please collect it soon.', '2026-04-02 11:58:56', 'Sent (Auto)'),
(11, 84, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271084) is ready at Multi9. Please collect it soon.', '2026-04-02 11:58:56', 'Sent (Auto)'),
(12, 86, '94768483156', 'Multi9: Your Desktop (Job #ORD-271086) is now Completed.', '2026-04-02 13:59:39', 'Success'),
(13, 86, '94768483156', 'Hi anuththra imanshi, your Desktop (Job #ORD-271086) is ready at Multi9. Please collect it soon.', '2026-04-02 13:59:40', 'Sent (Auto)'),
(14, 83, '94768483156', 'Multi9: Your Printer (Job #ORD-271083) is now Returned.', '2026-04-02 20:35:17', 'Success'),
(15, 84, '94768483156', 'Hi anuththra imanshi, your Laptop ready for 214 days. Current rent: Rs. 500.', '2026-04-02 22:06:17', 'Success'),
(19, 82, '94741122153', 'Hi saduni vindya, your Laptop (Job #ORD-271082) is ready at Multi9. Please collect it soon.', '2026-04-08 18:35:35', 'Sent (Auto)'),
(21, 68, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271068) is ready at Multi9. Please collect it soon.', '2026-04-08 18:35:35', 'Sent (Auto)'),
(22, 73, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271073) is ready at Multi9. Please collect it soon.', '2026-04-08 18:35:35', 'Sent (Auto)'),
(23, 77, '94768483156', 'Hi anuththra imanshi, your Desktop (Job #ORD-271077) is ready at Multi9. Please collect it soon.', '2026-04-08 18:35:35', 'Sent (Auto)'),
(24, 82, '94741122153', 'Hi saduni vindya, your Laptop (Job #ORD-271082) is ready at Multi9. Please collect it soon.', '2026-04-20 16:38:22', 'Sent (Auto)'),
(26, 68, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271068) is ready at Multi9. Please collect it soon.', '2026-04-20 16:38:22', 'Sent (Auto)'),
(27, 73, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271073) is ready at Multi9. Please collect it soon.', '2026-04-20 16:38:22', 'Sent (Auto)'),
(28, 77, '94768483156', 'Hi anuththra imanshi, your Desktop (Job #ORD-271077) is ready at Multi9. Please collect it soon.', '2026-04-20 16:38:22', 'Sent (Auto)'),
(30, 84, '94768483156', 'Multi9: Your Laptop (Job #ORD-271084) is now Returned.', '2026-04-20 16:50:25', 'Success'),
(31, 85, '94768483156', 'Multi9: Your Printer (Job #ORD-271085) is now Returned.', '2026-04-20 16:50:43', 'Success'),
(32, 82, '94741122153', 'Hi saduni vindya, your Laptop (Job #ORD-271082) is ready at Multi9. Please collect it soon.', '2026-05-03 00:46:39', 'Sent (Auto)'),
(34, 68, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271068) is ready at Multi9. Please collect it soon.', '2026-05-03 00:46:39', 'Sent (Auto)'),
(35, 73, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271073) is ready at Multi9. Please collect it soon.', '2026-05-03 00:46:39', 'Sent (Auto)'),
(36, 77, '94768483156', 'Hi anuththra imanshi, your Desktop (Job #ORD-271077) is ready at Multi9. Please collect it soon.', '2026-05-03 00:46:39', 'Sent (Auto)'),
(37, 82, '94741122153', 'Hi saduni vindya, your Laptop (Job #ORD-271082) is ready at Multi9. Please collect it soon.', '2026-05-06 02:25:34', 'Sent (Auto)'),
(39, 68, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271068) is ready at Multi9. Please collect it soon.', '2026-05-06 02:25:34', 'Sent (Auto)'),
(40, 73, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271073) is ready at Multi9. Please collect it soon.', '2026-05-06 02:25:34', 'Sent (Auto)'),
(41, 77, '94768483156', 'Hi anuththra imanshi, your Desktop (Job #ORD-271077) is ready at Multi9. Please collect it soon.', '2026-05-06 02:25:34', 'Sent (Auto)'),
(42, 82, '94741122153', 'Hi saduni vindya, your Laptop (Job #ORD-271082) is ready at Multi9. Please collect it soon.', '2026-05-16 16:31:59', 'Sent (Auto)'),
(44, 68, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271068) is ready at Multi9. Please collect it soon.', '2026-05-16 16:31:59', 'Sent (Auto)'),
(45, 73, '94768483156', 'Hi anuththra imanshi, your Laptop (Job #ORD-271073) is ready at Multi9. Please collect it soon.', '2026-05-16 16:31:59', 'Sent (Auto)'),
(46, 77, '94768483156', 'Hi anuththra imanshi, your Desktop (Job #ORD-271077) is ready at Multi9. Please collect it soon.', '2026-05-16 16:31:59', 'Sent (Auto)'),
(47, 82, '94741122153', 'Hi saduni vindya, your Laptop (Job #ORD-271082) is ready at Multi9. Please collect it soon.', '2026-06-11 20:49:31', 'Sent (Auto)'),
(49, 79, '94741122153', 'Dear saduni vindya, your device Laptop (Job #ORD-271079) has been kept for over 1 year. If not collected, it will be destroyed within 7 days. - Multi9', '2026-06-11 21:19:06', 'Sent (1 Year Notice)'),
(51, 85, '94768483156', 'Multi9: Your Printer (Job #ORD-271085) is now Completed.', '2026-06-11 21:54:46', 'Success'),
(52, 85, '94768483156', 'Hi anuththra imanshi, your Printer (Job #ORD-271085) is ready at Multi9. Please collect it soon.', '2026-06-11 21:54:46', 'Sent (Auto)'),
(53, 85, '94768483156', 'Dear anuththra imanshi, your device Printer (Job #ORD-271085) has been kept for over 1 year. If not collected, it will be destroyed within 7 days. - Multi9', '2026-06-11 21:57:01', 'Sent (1 Year Notice)'),
(54, 96, '94761817517', 'Multi9: Your Laptop (Job #ORD-271095) is now In Progress.', '2026-08-11 14:35:04', 'Success'),
(55, 96, '94761817517', 'Multi9: Your Laptop (Job #ORD-271095) is now Completed.', '2026-08-11 14:35:19', 'Success'),
(56, 96, '94761817517', 'Hi hashini umnda, your Laptop (Job #ORD-271095) is ready at Multi9. Please collect it soon.', '2026-08-11 14:35:19', 'Sent (Auto)'),
(57, 101, '94761817517', 'Multi9: Your Desktop PC (Job #ORD-271098) is now Completed.', '2026-08-11 17:25:37', 'Success'),
(58, 101, '94761817517', 'Hi hashini umnda, your Desktop PC (Job #ORD-271098) is ready at Multi9. Please collect it soon.', '2026-08-11 17:25:38', 'Sent (Auto)'),
(59, 82, '94741122153', 'Hi saduni vindya, your Laptop ready for 132 days. Current rent: Rs. 200.', '2026-08-11 17:30:20', 'Success'),
(60, 103, '94761817517', 'Multi9: Your Printer (Job #ORD-271099) is now Completed.', '2026-08-11 19:25:27', 'Success'),
(61, 103, '94761817517', 'Hi hashini umnda, your Printer (Job #ORD-271099) is ready at Multi9. Please collect it soon.', '2026-08-11 19:25:27', 'Sent (Auto)'),
(62, 99, '94761817517', 'Multi9: Your Printer (Job #ORD-271097) is now Completed.', '2026-08-11 19:28:45', 'Success'),
(63, 99, '94761817517', 'Hi hashini umnda, your Printer (Job #ORD-271097) is ready at Multi9. Please collect it soon.', '2026-08-11 19:28:45', 'Sent (Auto)'),
(64, 99, '94761817517', 'Hi hashini umnda, your Printer ready for 369 days. Current rent: Rs. 1000.', '2026-08-11 19:58:25', 'Failed'),
(65, 99, '94761817517', 'Hi hashini umnda, your Printer ready for 369 days. Current rent: Rs. 1000.', '2026-08-11 19:58:35', 'Failed'),
(66, 103, '94761817517', 'Hi hashini umnda, your Printer ready for 122 days. Current rent: Rs. 200.', '2026-08-11 19:58:47', 'Failed'),
(67, 103, '94761817517', 'Hi hashini umnda, your Printer ready for 122 days. Current rent: Rs. 200.', '2026-08-11 19:58:58', 'Failed'),
(68, 101, '94761817517', 'Multi9: Your Desktop PC (Job #ORD-271098) is now In Progress.', '2026-08-11 20:00:06', 'Failed'),
(69, 82, '94741122153', 'Hi saduni vindya, your Laptop ready for 132 days. Current rent: Rs. 200.', '2026-08-11 20:00:15', 'Failed'),
(70, 100, '94761817517', 'Multi9: Your Desktop PC (Job #ORD-271098) is now In Progress.', '2026-08-11 20:01:18', 'Failed'),
(71, 103, '94761817517', 'Hi hashini umnda, your Printer ready for 122 days. Current rent: Rs. 200.', '2026-08-11 20:11:03', 'Success'),
(72, 99, '94761817517', 'Hi hashini umnda, your Printer ready for 369 days. Current rent: Rs. 1000.', '2026-08-11 20:12:07', 'Success'),
(73, 99, '94761817517', 'Hi hashini umnda, your Printer ready for 369 days. Current rent: Rs. 1000.', '2026-08-11 20:22:36', 'Success'),
(74, 99, '94761817517', 'Hi hashini umnda, your Printer ready for 369 days. Current rent: Rs. 1000.', '2026-08-11 20:25:11', 'Success'),
(75, 99, '94761817517', 'Hi hashini umnda, your Printer ready for 369 days. Current rent: Rs. 1000.', '2026-08-11 20:31:23', 'Success'),
(76, 97, '94761817517', 'Multi9: Your Desktop PC (Job #ORD-271096) is now Completed.', '2026-08-11 20:35:29', 'Success'),
(77, 97, '94761817517', 'Hi hashini umnda, your Desktop PC (Job #ORD-271096) is ready at Multi9. Please collect it soon.', '2026-08-11 20:35:29', 'Sent (Auto)');

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
('cms-12', 'Dell OptiPlex', 1, 5, 5000.00, 'In Stock'),
('FAN-CPU', 'CPU Cooling Fan', NULL, 1, 1650.00, NULL),
('itm-900', 'HP Envy', 4, 18, 799.00, 'In Stock'),
('KBD-USB', 'USB Standard Keyboard', NULL, 0, 1850.00, 'In Stock'),
('MON-22', '22 Inch LED Monitor', NULL, 3, 24500.00, 'In Stock'),
('MSE-OPT', 'Optical USB Mouse', NULL, 24, 1200.00, NULL),
('PWR-CB-L', 'Laptop Power Cable', NULL, 11, 850.00, NULL),
('RAM-8GB-D4', '8GB DDR4 RAM', NULL, 46, 8500.00, NULL),
('SSD-256GB', '256GB NVMe SSD', NULL, 23, 9500.00, NULL),
('SSD-512GB', '512GB SATA SSD', NULL, 10, 14500.00, NULL),
('TH-PASTE', 'Thermal Paste (Arctic Silver)', NULL, 31, 950.00, NULL),
('ttm-90', 'Samsung 27-inch', 2, 34, 789.00, 'In Stock');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `shop_name` varchar(255) NOT NULL,
  `shop_address` text DEFAULT NULL,
  `shop_phone` varchar(20) DEFAULT NULL,
  `shop_email` varchar(100) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'Rs.',
  `job_prefix` varchar(20) DEFAULT 'ORD-',
  `next_job_no` int(11) DEFAULT 1000,
  `storage_limit` int(11) DEFAULT 0,
  `monthly_fee` decimal(10,2) DEFAULT 0.00,
  `disposal_limit` int(11) DEFAULT 0,
  `sms_api_key` text DEFAULT NULL,
  `invoice_prefix` varchar(20) DEFAULT 'INV-',
  `next_invoice_no` int(11) DEFAULT 1000,
  `invoice_terms` text DEFAULT NULL,
  `admin_password` varchar(255) DEFAULT 'admin123'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `shop_name`, `shop_address`, `shop_phone`, `shop_email`, `currency`, `job_prefix`, `next_job_no`, `storage_limit`, `monthly_fee`, `disposal_limit`, `sms_api_key`, `invoice_prefix`, `next_invoice_no`, `invoice_terms`, `admin_password`) VALUES
(1, 'Multi9 Computer Systems', 'No 123, Main Street, Colombo', '0112345678', 'info@multi9.com', 'Rs.', 'ORD-', 271100, 3, 500.00, 12, '391|gyFVyQXSWNywx289bNDJdCkdKcOVRcPqyiUQzXzb', 'INV-', 50, '', 'multi912#');

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
(11, 'ramiru'),
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
-- Indexes for table `login_users`
--
ALTER TABLE `login_users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `invoice_no` (`invoice_no`);

--
-- Indexes for table `sms_history`
--
ALTER TABLE `sms_history`
  ADD PRIMARY KEY (`sms_id`),
  ADD KEY `job_device_id` (`job_device_id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`item_code`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `cashid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoice_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `issue`
--
ALTER TABLE `issue`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `job_device`
--
ALTER TABLE `job_device`
  MODIFY `job_device_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

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
-- AUTO_INCREMENT for table `login_users`
--
ALTER TABLE `login_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sms_history`
--
ALTER TABLE `sms_history`
  MODIFY `sms_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `technicians`
--
ALTER TABLE `technicians`
  MODIFY `technician_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
-- Constraints for table `sms_history`
--
ALTER TABLE `sms_history`
  ADD CONSTRAINT `sms_history_ibfk_1` FOREIGN KEY (`job_device_id`) REFERENCES `job_device` (`job_device_id`);

--
-- Constraints for table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
