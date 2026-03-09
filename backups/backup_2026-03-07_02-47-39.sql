-- Database Backup

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `acc_id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_name` varchar(100) NOT NULL,
  `account_no` varchar(50) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`acc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `accounts` VALUES("2","BOC Account","123456789","23456.00");
INSERT INTO `accounts` VALUES("3","HNB Account","987654321","2002.00");


DROP TABLE IF EXISTS `cashbook`;
CREATE TABLE `cashbook` (
  `cashid` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(50) DEFAULT NULL,
  `date` date NOT NULL,
  `income` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(10,2) DEFAULT 0.00,
  `acc_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`cashid`),
  KEY `invoice_no` (`invoice_no`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `cashbook` VALUES("10","11","2025-12-29","10500.00","10500.00",NULL);
INSERT INTO `cashbook` VALUES("11","12","2025-12-29","9500.00","20000.00",NULL);
INSERT INTO `cashbook` VALUES("14","online","2025-12-29","23456.00","43456.00","2");
INSERT INTO `cashbook` VALUES("15","13","2025-12-29","14500.00","57956.00",NULL);
INSERT INTO `cashbook` VALUES("16","14","2025-12-29","150.00","58106.00",NULL);
INSERT INTO `cashbook` VALUES("20","15","2025-12-29","10018611.00","10076717.00",NULL);
INSERT INTO `cashbook` VALUES("21","16","2025-12-29","3050.00","10079767.00",NULL);
INSERT INTO `cashbook` VALUES("22","17","2025-12-30","11500.00","10091267.00",NULL);
INSERT INTO `cashbook` VALUES("23","online","2025-12-30","2000.00","10093267.00","3");
INSERT INTO `cashbook` VALUES("24","online","2025-12-30","2.00","10093269.00","3");
INSERT INTO `cashbook` VALUES("25","18","2025-12-30","5000.00","10098269.00",NULL);
INSERT INTO `cashbook` VALUES("26","19","2025-12-30","14624.00","10112893.00",NULL);
INSERT INTO `cashbook` VALUES("27","20","2026-01-05","6477.00","10119370.00",NULL);
INSERT INTO `cashbook` VALUES("28","21","2026-01-05","5800.00","10125170.00",NULL);
INSERT INTO `cashbook` VALUES("29","22","2026-01-05","14900.00","10140070.00",NULL);
INSERT INTO `cashbook` VALUES("30","23","2026-01-10","9500.00","10149570.00",NULL);
INSERT INTO `cashbook` VALUES("31","24","2026-01-10","1650.00","10151220.00",NULL);
INSERT INTO `cashbook` VALUES("32","25","2026-01-11","16450.00","10167670.00",NULL);
INSERT INTO `cashbook` VALUES("33","26","2026-03-06","5400.00","10173070.00",NULL);
INSERT INTO `cashbook` VALUES("34","27","2026-03-06","9700.00","10182770.00",NULL);
INSERT INTO `cashbook` VALUES("35","28","2026-03-06","14700.00","10197470.00",NULL);
INSERT INTO `cashbook` VALUES("36","30","2026-03-06","6250.00","10203720.00",NULL);
INSERT INTO `cashbook` VALUES("37","29","2026-03-06","5356.00","10209076.00",NULL);
INSERT INTO `cashbook` VALUES("38","32","2026-03-06","9900.00","10218976.00",NULL);
INSERT INTO `cashbook` VALUES("39","31","2026-03-06","9500.00","10228476.00",NULL);
INSERT INTO `cashbook` VALUES("40","33","2026-03-06","5200.00","10233676.00",NULL);
INSERT INTO `cashbook` VALUES("41","35","2026-03-06","14500.00","10248176.00",NULL);
INSERT INTO `cashbook` VALUES("42","38","2026-03-06","5100.00","10253276.00",NULL);
INSERT INTO `cashbook` VALUES("43","39","2026-03-06","1050.00","10254326.00",NULL);
INSERT INTO `cashbook` VALUES("44","40","2026-03-06","1650.00","10255976.00",NULL);
INSERT INTO `cashbook` VALUES("45","41","2026-03-06","5000.00","10260976.00",NULL);
INSERT INTO `cashbook` VALUES("46","42","2026-03-06","799.00","10261775.00",NULL);
INSERT INTO `cashbook` VALUES("47","42","2026-03-06","799.00","10262574.00",NULL);
INSERT INTO `cashbook` VALUES("48","45","2026-03-06","24500.00","10287074.00",NULL);
INSERT INTO `cashbook` VALUES("49","46","2026-03-06","5000.00","10292074.00",NULL);
INSERT INTO `cashbook` VALUES("50","47","2026-03-06","1650.00","10293724.00",NULL);


DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `category` VALUES("1","Desktop Computers",NULL);
INSERT INTO `category` VALUES("2","Monitors",NULL);
INSERT INTO `category` VALUES("3","Desktop Computers",NULL);
INSERT INTO `category` VALUES("4","Laptops",NULL);
INSERT INTO `category` VALUES("5","Monitors",NULL);
INSERT INTO `category` VALUES("6","Keyboards",NULL);
INSERT INTO `category` VALUES("7","Mouse",NULL);
INSERT INTO `category` VALUES("8","Printers",NULL);
INSERT INTO `category` VALUES("9","Networking Devices",NULL);
INSERT INTO `category` VALUES("10","Hard Drives",NULL);
INSERT INTO `category` VALUES("11","RAM Modules",NULL);
INSERT INTO `category` VALUES("12","Graphic Cards",NULL);
INSERT INTO `category` VALUES("13","Motherboards",NULL);
INSERT INTO `category` VALUES("14","Power Supplies",NULL);
INSERT INTO `category` VALUES("15","Cables & Accessories",NULL);
INSERT INTO `category` VALUES("16","Software",NULL);


DROP TABLE IF EXISTS `customer`;
CREATE TABLE `customer` (
  `phone_number` varchar(15) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `customer` VALUES("0720417529","Chamodi Sandeepani","240/E","ana@gmail.com");
INSERT INTO `customer` VALUES("0741122153","saduni vindya","waliweriya","sadu@gmil.com");
INSERT INTO `customer` VALUES("0742524527","malindi","galle","mali@gamil.com");
INSERT INTO `customer` VALUES("0761817517","hshini umnda","waliweriya","hasiii@gmail.com");
INSERT INTO `customer` VALUES("0761817518","lavan abishek",NULL,NULL);
INSERT INTO `customer` VALUES("0765333721","anuththara","galdeniya","");
INSERT INTO `customer` VALUES("0765333722","anuththara",NULL,NULL);
INSERT INTO `customer` VALUES("0768456788","shiwardan","minu","shiwa@gmail.com");
INSERT INTO `customer` VALUES("0768483156","anuththra imanshi","galdeniya ,kappitiwaklana","anu@gmail.com");
INSERT INTO `customer` VALUES("0768483170","raniluuu","umandawa","ra@gmail.com");
INSERT INTO `customer` VALUES("07685947","pdmika","bchbjj vjhvhbv","padmi@gmail.com");
INSERT INTO `customer` VALUES("0768955775","maheee",NULL,NULL);
INSERT INTO `customer` VALUES("0786798654","amal perera","alwwa,galdeniya","amal@gamil.com");
INSERT INTO `customer` VALUES("07889565757575","dumindu",NULL,NULL);
INSERT INTO `customer` VALUES("08789959595","dammi","galdeniya","d@gmail.com");
INSERT INTO `customer` VALUES("089657883","anuththra","kapapirtiwalana",NULL);
INSERT INTO `customer` VALUES("089677333","vindya amarasekara","mathara","vindy@gmail.com");
INSERT INTO `customer` VALUES("0897655677","malindi","galdeniya",NULL);
INSERT INTO `customer` VALUES("0987588845","hshini umnda",NULL,NULL);


DROP TABLE IF EXISTS `invoice`;
CREATE TABLE `invoice` (
  `invoice_no` int(11) NOT NULL AUTO_INCREMENT,
  `job_no` varchar(20) DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `service_charge` decimal(10,2) DEFAULT 0.00,
  `parts_total` decimal(10,2) DEFAULT 0.00,
  `grand_total` decimal(10,2) DEFAULT 0.00,
  `items_json` text DEFAULT NULL,
  `payment_status` enum('Paid','Pending') DEFAULT 'Paid',
  PRIMARY KEY (`invoice_no`),
  KEY `job_no` (`job_no`),
  CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`job_no`) REFERENCES `job` (`job_no`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `invoice` VALUES("1","ORD-271039","2025-12-28","0.00","0.00","0.00","[]","Paid");
INSERT INTO `invoice` VALUES("2","ORD-271039","2025-12-29","0.00","98150.00","98150.00","[{\"code\":\"CMOS-BAT\",\"name\":\"CMOS Battery (CR2032)\",\"price\":\"150\",\"qty\":\"1\",\"sub\":150},{\"code\":\"MON-22\",\"name\":\"22 Inch LED Monitor\",\"price\":\"24500\",\"qty\":\"4\",\"sub\":98000}]","Paid");
INSERT INTO `invoice` VALUES("3","ORD-271039","2025-12-29","0.00","1850.00","1850.00","[{\"code\":\"KBD-USB\",\"name\":\"USB Standard Keyboard\",\"price\":\"1850\",\"qty\":\"1\",\"sub\":1850}]","Paid");
INSERT INTO `invoice` VALUES("4","ORD-271039","2025-12-29","0.00","7400.00","7400.00","[{\"code\":\"KBD-USB\",\"name\":\"USB Standard Keyboard\",\"price\":\"1850\",\"qty\":\"4\",\"sub\":7400}]","Paid");
INSERT INTO `invoice` VALUES("5","ORD-271039","2025-12-29","0.00","1850.00","1850.00","[{\"code\":\"KBD-USB\",\"name\":\"USB Standard Keyboard\",\"price\":\"1850\",\"qty\":\"1\",\"sub\":1850}]","Paid");
INSERT INTO `invoice` VALUES("6","ORD-271039","2025-12-29","0.00","73500.00","73500.00","[{\"code\":\"MON-22\",\"name\":\"22 Inch LED Monitor\",\"price\":\"24500\",\"qty\":\"3\",\"sub\":73500}]","Paid");
INSERT INTO `invoice` VALUES("7","ORD-271039","2025-12-29","0.00","24500.00","24500.00","[{\"code\":\"MON-22\",\"name\":\"22 Inch LED Monitor\",\"price\":\"24500\",\"qty\":\"1\",\"sub\":24500}]","Paid");
INSERT INTO `invoice` VALUES("8","ORD-271039","2025-12-29","0.00","8500.00","8500.00","[{\"code\":\"RAM-8GB-D4\",\"name\":\"8GB DDR4 RAM\",\"price\":\"8500\",\"qty\":\"1\",\"sub\":8500}]","Paid");
INSERT INTO `invoice` VALUES("9","ORD-271039","2025-12-29","500.00","850.00","1350.00","[{\"code\":\"PWR-CB-L\",\"name\":\"Laptop Power Cable\",\"price\":\"850\",\"qty\":\"1\",\"sub\":850}]","Paid");
INSERT INTO `invoice` VALUES("10","ORD-271039","2025-12-29","0.00","14500.00","14500.00","[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]","Paid");
INSERT INTO `invoice` VALUES("11","ORD-271039","2025-12-29","1000.00","9500.00","10500.00","[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]","Paid");
INSERT INTO `invoice` VALUES("12","ORD-271039","2025-12-29","0.00","9500.00","9500.00","[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]","Paid");
INSERT INTO `invoice` VALUES("13","ORD-271039","2025-12-29","0.00","14500.00","14500.00","[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]","Paid");
INSERT INTO `invoice` VALUES("14","ORD-271039","2025-12-29","0.00","150.00","150.00","[{\"code\":\"CMOS-BAT\",\"name\":\"CMOS Battery (CR2032)\",\"price\":\"150\",\"qty\":\"1\",\"sub\":150}]","Paid");
INSERT INTO `invoice` VALUES("15","ORD-271039","0000-00-00","10010111.00","8500.00","10018611.00","[{\"code\":\"RAM-8GB-D4\",\"name\":\"8GB DDR4 RAM\",\"price\":\"8500.00\",\"qty\":\"1\",\"sub\":8500}]","Paid");
INSERT INTO `invoice` VALUES("16","ORD-271038","0000-00-00","200.00","2850.00","3050.00","[{\"code\":\"TH-PASTE\",\"name\":\"Thermal Paste (Arctic Silver)\",\"price\":\"950\",\"qty\":\"3\",\"sub\":2850}]","Paid");
INSERT INTO `invoice` VALUES("17","ORD-271037","0000-00-00","2000.00","9500.00","11500.00","[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]","Paid");
INSERT INTO `invoice` VALUES("18","ORD-271042","0000-00-00","0.00","5000.00","5000.00","[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]","Paid");
INSERT INTO `invoice` VALUES("19","ORD-271043","0000-00-00","124.00","14500.00","14624.00","[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]","Paid");
INSERT INTO `invoice` VALUES("20","ORD-271042","0000-00-00","678.00","5799.00","6477.00","[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000},{\"code\":\"itm-900\",\"name\":\"HP Envy\",\"price\":\"799\",\"qty\":\"1\",\"sub\":799}]","Paid");
INSERT INTO `invoice` VALUES("21","ORD-271047","0000-00-00","600.00","5000.00","5800.00","[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]","Paid");
INSERT INTO `invoice` VALUES("22","ORD-271048","0000-00-00","200.00","14500.00","14900.00","[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]","Paid");
INSERT INTO `invoice` VALUES("23","ORD-271049","0000-00-00","0.00","9500.00","9500.00","[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]","Paid");
INSERT INTO `invoice` VALUES("24","ORD-271050","0000-00-00","0.00","1650.00","1650.00","[{\"code\":\"FAN-CPU\",\"name\":\"CPU Cooling Fan\",\"price\":\"1650\",\"qty\":\"1\",\"sub\":1650}]","Paid");
INSERT INTO `invoice` VALUES("25","ORD-271051","2026-01-11","1000.00","15450.00","16450.00","[{\"code\":\"TH-PASTE\",\"name\":\"Thermal Paste (Arctic Silver)\",\"price\":\"950\",\"qty\":\"1\",\"sub\":950},{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]","Paid");
INSERT INTO `invoice` VALUES("26","ORD-271061","2026-03-06","400.00","5000.00","5400.00","[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]","Paid");
INSERT INTO `invoice` VALUES("27","ORD-271054","2026-03-06","200.00","9500.00","9700.00","[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]","Paid");
INSERT INTO `invoice` VALUES("28","ORD-271062","2026-03-06","200.00","14500.00","14700.00","[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]","Paid");
INSERT INTO `invoice` VALUES("29","ORD-271060","2026-03-06","356.00","5000.00","5356.00","[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]","Paid");
INSERT INTO `invoice` VALUES("30","ORD-271063","2026-03-06","400.00","5850.00","6250.00","[{\"code\":\"PWR-CB-L\",\"name\":\"Laptop Power Cable\",\"price\":\"850\",\"qty\":\"1\",\"sub\":850},{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]","Paid");
INSERT INTO `invoice` VALUES("31","ORD-271064","2026-03-06","1000.00","8500.00","9500.00","[{\"code\":\"RAM-8GB-D4\",\"name\":\"8GB DDR4 RAM\",\"price\":\"8500\",\"qty\":\"1\",\"sub\":8500}]","Paid");
INSERT INTO `invoice` VALUES("32","ORD-271065","2026-03-06","400.00","9500.00","9900.00","[{\"code\":\"SSD-256GB\",\"name\":\"256GB NVMe SSD\",\"price\":\"9500\",\"qty\":\"1\",\"sub\":9500}]","Paid");
INSERT INTO `invoice` VALUES("33","ORD-271066","2026-03-06","100.00","5000.00","5200.00","[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]","Paid");
INSERT INTO `invoice` VALUES("34","ORD-271067","2026-03-06","100.00","1650.00","1750.00","[{\"code\":\"FAN-CPU\",\"name\":\"CPU Cooling Fan\",\"price\":\"1650\",\"qty\":\"1\",\"sub\":1650}]","");
INSERT INTO `invoice` VALUES("35","ORD-271068","2026-03-06","0.00","14500.00","14500.00","[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]","Paid");
INSERT INTO `invoice` VALUES("36","ORD-271069","2026-03-06","0.00","1650.00","1750.00","[{\"code\":\"FAN-CPU\",\"name\":\"CPU Cooling Fan\",\"price\":\"1650\",\"qty\":\"1\",\"sub\":1650}]","");
INSERT INTO `invoice` VALUES("37","ORD-271069","2026-03-06","0.00","5000.00","5000.00","[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]","");
INSERT INTO `invoice` VALUES("38","ORD-271067","2026-03-06","100.00","5000.00","5100.00","[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]","Paid");
INSERT INTO `invoice` VALUES("39","ORD-271069","2026-03-06","100.00","950.00","1050.00","[{\"code\":\"TH-PASTE\",\"name\":\"Thermal Paste (Arctic Silver)\",\"price\":\"950\",\"qty\":\"1\",\"sub\":950}]","Paid");
INSERT INTO `invoice` VALUES("40","ORD-271070","2026-03-06","0.00","1650.00","1650.00","[{\"code\":\"FAN-CPU\",\"name\":\"CPU Cooling Fan\",\"price\":\"1650\",\"qty\":\"1\",\"sub\":1650}]","Paid");
INSERT INTO `invoice` VALUES("41","ORD-271071","2026-03-06","0.00","5000.00","5000.00","[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]","Paid");
INSERT INTO `invoice` VALUES("42","ORD-271072","2026-03-06","0.00","799.00","799.00","[{\"code\":\"itm-900\",\"name\":\"HP Envy\",\"price\":\"799\",\"qty\":\"1\",\"sub\":799}]","Paid");
INSERT INTO `invoice` VALUES("43","ORD-271072","2026-03-06","0.00","8500.00","8500.00","[{\"code\":\"RAM-8GB-D4\",\"name\":\"8GB DDR4 RAM\",\"price\":\"8500\",\"qty\":\"1\",\"sub\":8500}]","Pending");
INSERT INTO `invoice` VALUES("44","ORD-271072","2026-03-06","0.00","950.00","950.00","[{\"code\":\"TH-PASTE\",\"name\":\"Thermal Paste (Arctic Silver)\",\"price\":\"950\",\"qty\":\"1\",\"sub\":950}]","Pending");
INSERT INTO `invoice` VALUES("45","ORD-271073","2026-03-06","100.00","24500.00","24500.00","[{\"code\":\"MON-22\",\"name\":\"22 Inch LED Monitor\",\"price\":\"24500\",\"qty\":\"1\",\"sub\":24500}]","Paid");
INSERT INTO `invoice` VALUES("46","ORD-271074","2026-03-06","100.00","5000.00","5000.00","[{\"code\":\"cms-12\",\"name\":\"Dell OptiPlex\",\"price\":\"5000\",\"qty\":\"1\",\"sub\":5000}]","Paid");
INSERT INTO `invoice` VALUES("47","ORD-271075","2026-03-06","200.00","1650.00","1650.00","[{\"code\":\"FAN-CPU\",\"name\":\"CPU Cooling Fan\",\"price\":\"1650\",\"qty\":\"1\",\"sub\":1650}]","Paid");
INSERT INTO `invoice` VALUES("48","ORD-271076","2025-10-06","200.00","1650.00","1850.00","[{\"code\":\"FAN-CPU\",\"name\":\"CPU Cooling Fan\",\"price\":\"1650\",\"qty\":\"1\",\"sub\":1650}]","Pending");
INSERT INTO `invoice` VALUES("49","ORD-271077","2026-03-06","200.00","14500.00","14700.00","[{\"code\":\"SSD-512GB\",\"name\":\"512GB SATA SSD\",\"price\":\"14500\",\"qty\":\"1\",\"sub\":14500}]","Pending");


DROP TABLE IF EXISTS `issue`;
CREATE TABLE `issue` (
  `issue_id` int(11) NOT NULL AUTO_INCREMENT,
  `issue_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`issue_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `issue` VALUES("1","repair",NULL);
INSERT INTO `issue` VALUES("2","keyboard air",NULL);


DROP TABLE IF EXISTS `job`;
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
  `technician_id` int(11) DEFAULT NULL,
  `estimated_cost` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`job_no`),
  KEY `phone_number` (`phone_number`),
  KEY `fk_job_technician` (`technician_id`),
  CONSTRAINT `fk_job_technician` FOREIGN KEY (`technician_id`) REFERENCES `technicians` (`technician_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `job_ibfk_1` FOREIGN KEY (`phone_number`) REFERENCES `customer` (`phone_number`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `job` VALUES("ORD-1612","089657883","2025-12-27","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,"0.00");
INSERT INTO `job` VALUES("ORD-271031","0761817517","2025-12-27","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"4","0.00");
INSERT INTO `job` VALUES("ORD-271032","0761817517","2025-12-28","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271033","0987588845","2025-12-28","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271034","0761817518","2025-12-28","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"9","0.00");
INSERT INTO `job` VALUES("ORD-271035","0768955775","2025-12-28","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"9","0.00");
INSERT INTO `job` VALUES("ORD-271036","07889565757575","2025-12-28","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271037","089677333","2025-12-28","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271038","0720417529","2025-12-28","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"9","0.00");
INSERT INTO `job` VALUES("ORD-271039","0720417529","2025-12-28","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271040","0761817517","2025-12-28","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271041","0761817517","2025-12-29","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271042","0768483170","2025-12-30","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271043","0768483170","2025-12-30","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271044","0761817517","2026-01-05","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271045","0765333721","2026-01-05","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271046","0761817517","2026-01-05","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271047","0761817517","2026-01-05","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271048","0761817517","2026-01-05","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271049","0761817517","2026-01-10","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271050","0761817517","2026-01-10","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271051","0761817517","2026-01-11","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271052","0786798654","2026-02-01","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271053","0761817517","2026-03-02","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271054","0761817517","2026-03-03","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271055","0761817517","2026-03-03","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"4","0.00");
INSERT INTO `job` VALUES("ORD-271056","0768483156","2026-03-03","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"11","0.00");
INSERT INTO `job` VALUES("ORD-271057","0768483156","2026-03-03","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271058","0761817517","2026-03-03","Pending",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271059","0768483156","2026-03-05","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271060","0742524527","2026-03-05","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"11","0.00");
INSERT INTO `job` VALUES("ORD-271061","0720417529","2026-03-05","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271062","0741122153","2026-03-05","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271063","0768483156","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","15000.00");
INSERT INTO `job` VALUES("ORD-271064","0742524527","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"11","12000.00");
INSERT INTO `job` VALUES("ORD-271065","0742524527","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","12555.00");
INSERT INTO `job` VALUES("ORD-271066","0768483156","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","5688.00");
INSERT INTO `job` VALUES("ORD-271067","0761817517","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"4","0.00");
INSERT INTO `job` VALUES("ORD-271068","0768483156","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271069","0742524527","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"11","0.00");
INSERT INTO `job` VALUES("ORD-271070","0768483156","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271071","0768483156","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271072","0768483156","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"10","0.00");
INSERT INTO `job` VALUES("ORD-271073","0768483156","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271074","0768483156","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271075","0768483156","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271076","0761817517","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"1","0.00");
INSERT INTO `job` VALUES("ORD-271077","0768483156","2026-03-06","Approved",NULL,NULL,NULL,NULL,NULL,NULL,NULL,"11","0.00");


DROP TABLE IF EXISTS `job_device`;
CREATE TABLE `job_device` (
  `job_device_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `destroy_notice_sent_date` datetime DEFAULT NULL,
  `issue_category` enum('Hardware','Software') DEFAULT 'Hardware',
  `final_status` varchar(50) DEFAULT 'Pending',
  `rent_warning_sent` int(11) DEFAULT 0,
  PRIMARY KEY (`job_device_id`),
  KEY `job_no` (`job_no`),
  CONSTRAINT `job_device_ibfk_1` FOREIGN KEY (`job_no`) REFERENCES `job` (`job_no`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `job_device` VALUES("26","ORD-1612","mouse","678",NULL,NULL,NULL,NULL,NULL,"not on","Pending",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("29","ORD-271032","printers",NULL,NULL,"Warranty","Abans","",NULL,"no power","Sent to Warranty",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("31","ORD-271034","Desktop",NULL,NULL,"",NULL,NULL,NULL,"Battery","Pending",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("32","ORD-271034","Laptop",NULL,NULL,"",NULL,NULL,NULL,"Display/LCD","Pending",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("33","ORD-271035","Printer",NULL,NULL,"Warranty","abans",NULL,NULL,"Power","Completed",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("34","ORD-271036","Printer",NULL,NULL,"Warranty",NULL,NULL,NULL,"Service","Pending",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("35","ORD-271037","Printer",NULL,NULL,"Warranty",NULL,NULL,NULL,"Power","billed",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("36","ORD-271038","Mobile",NULL,NULL,"No Warranty",NULL,"","","Software","billed",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("37","ORD-271039","Laptop",NULL,NULL,"Warranty",NULL,"","IMG_69511e013e37a_0.jpg","Charging","billed",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("38","ORD-271040","Laptop",NULL,NULL,"Warranty",NULL,"sim","IMG_6951aafa549d2_0.png","Software","Pending",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("41","ORD-271042","Mobile",NULL,NULL,"Warranty","singer","backcover","IMG_6953cb326638f_0.png","Power","billed","2025-09-01 00:00:00",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("42","ORD-271043","Mobile",NULL,NULL,"No Warranty",NULL,"","","Display","billed",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("43","ORD-271044","Mobile",NULL,NULL,"No Warranty",NULL,"back cover","","Software","Destroyed","2026-01-05 11:06:24",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("44","ORD-271045","Mobile",NULL,NULL,"No Warranty",NULL,"","","Display","Destroyed","2024-12-25 00:00:00","2026-01-05 12:18:53","Hardware","Pending","0");
INSERT INTO `job_device` VALUES("45","ORD-271046","Desktop",NULL,NULL,"No Warranty",NULL,"","","Service","Destroyed","2024-12-25 00:00:00","2025-12-25 00:00:00","Hardware","Pending","0");
INSERT INTO `job_device` VALUES("46","ORD-271047","Mobile",NULL,NULL,"No Warranty",NULL,"","","Power","billed","2025-09-05 00:00:00","0000-00-00 00:00:00","Hardware","Pending","0");
INSERT INTO `job_device` VALUES("48","ORD-271048","Mobile",NULL,NULL,"No Warranty",NULL,"","","Display","billed","2025-09-05 11:47:44",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("49","ORD-271049","Mobile",NULL,NULL,"No Warranty",NULL,"","","Display","billed","2026-01-10 21:41:20",NULL,"Software","Pending","0");
INSERT INTO `job_device` VALUES("50","ORD-271050","Mobile",NULL,NULL,"No Warranty",NULL,"","","Power","billed","2026-01-10 22:06:49",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("51","ORD-271051","Mobile",NULL,NULL,"No Warranty",NULL,"","","Power","billed","2026-01-11 10:28:09",NULL,"Software","Pending","0");
INSERT INTO `job_device` VALUES("52","ORD-271052","Moble",NULL,NULL,"Warranty",NULL,"back coner","","power","Pending",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("53","ORD-271053","Laptop",NULL,NULL,"Warranty",NULL,"mouse","IMG_69a5de8fbea4f_0.png","Display","Pending",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("54","ORD-271054","Laptop",NULL,NULL,"No Warranty",NULL,"","","Power","billed","2026-03-05 12:17:38",NULL,"Software","Pending","0");
INSERT INTO `job_device` VALUES("55","ORD-271055","Mobile",NULL,NULL,"Warranty","","back cover","IMG_69a6d1caa3626_0.png","new","Pending",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("56","ORD-271056","Printer",NULL,NULL,"Warranty","","","","new","Pending",NULL,NULL,"Software","Pending","0");
INSERT INTO `job_device` VALUES("57","ORD-271057","Printer",NULL,NULL,"Warranty",NULL,"bag","IMG_69a6d8badbc3b_0.png","repair","Pending",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("58","ORD-271058","Laptop",NULL,NULL,"Warranty",NULL,"laptop bag and mouse","IMG_69a6dace76e3b_0.png","keyboard air","Pending",NULL,NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("60","ORD-271060","Laptop",NULL,NULL,"No Warranty",NULL,"bag","IMG_69a9b6934f610_0.png","Service","billed","2026-03-05 22:36:53",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("61","ORD-271061","Laptop",NULL,NULL,"No Warranty",NULL,"mouse","IMG_69a9f6694a531_0.png","Service","billed","2026-03-05 21:58:32",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("62","ORD-271062","Laptop",NULL,NULL,"No Warranty",NULL,"bag","IMG_69a9f89775674_0.png","Display Damage","billed","2026-03-05 22:09:12",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("63","ORD-271063","Laptop",NULL,NULL,"No Warranty",NULL,"","","Display Damage","billed","2026-03-05 23:28:32",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("64","ORD-271064","Laptop",NULL,NULL,"No Warranty",NULL,"bag cover","IMG_69aa15a1dfe95_0.png","Service","billed","2026-03-05 23:49:57",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("65","ORD-271065","Laptop",NULL,NULL,"No Warranty",NULL,"","","No Power","billed","2026-03-06 00:00:46",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("66","ORD-271066","Printer",NULL,NULL,"No Warranty",NULL,"bag","IMG_69aac857e36b3_0.png","Display Damage","billed","2025-11-06 12:28:53",NULL,"Hardware","Pending","1");
INSERT INTO `job_device` VALUES("68","ORD-271068","Laptop",NULL,NULL,"No Warranty",NULL,"","","No Power","Completed","2026-03-06 12:55:37",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("70","ORD-271070","Laptop",NULL,NULL,"No Warranty",NULL,"","","No Power","billed","2026-03-06 13:32:01",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("71","ORD-271071","Printer",NULL,NULL,"No Warranty",NULL,"","","No Power","billed","2026-03-06 13:34:00",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("73","ORD-271073","Laptop",NULL,NULL,"No Warranty",NULL,"","","No Power","Completed","2026-03-06 14:09:51",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("74","ORD-271074","Laptop",NULL,NULL,"No Warranty",NULL,"","","repair","Completed","2025-11-06 14:11:29",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("75","ORD-271075","Printer",NULL,NULL,"No Warranty",NULL,"","","Display Damage","Completed","2025-11-06 14:13:40",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("76","ORD-271076","Printer",NULL,NULL,"No Warranty",NULL,"","","repair","Completed","2025-11-06 14:25:28",NULL,"Hardware","Pending","0");
INSERT INTO `job_device` VALUES("77","ORD-271077","Desktop",NULL,NULL,"No Warranty",NULL,"","","No Power","Completed","2026-03-06 14:48:57",NULL,"Hardware","Pending","0");


DROP TABLE IF EXISTS `job_device_issue`;
CREATE TABLE `job_device_issue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_device_id` int(11) DEFAULT NULL,
  `issue_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_device_id` (`job_device_id`),
  KEY `issue_id` (`issue_id`),
  CONSTRAINT `job_device_issue_ibfk_1` FOREIGN KEY (`job_device_id`) REFERENCES `job_device` (`job_device_id`) ON DELETE CASCADE,
  CONSTRAINT `job_device_issue_ibfk_2` FOREIGN KEY (`issue_id`) REFERENCES `issue` (`issue_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



DROP TABLE IF EXISTS `job_parts`;
CREATE TABLE `job_parts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_device_id` int(11) DEFAULT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_device_id` (`job_device_id`),
  KEY `item_code` (`item_code`),
  CONSTRAINT `job_parts_ibfk_1` FOREIGN KEY (`job_device_id`) REFERENCES `job_device` (`job_device_id`) ON DELETE CASCADE,
  CONSTRAINT `job_parts_ibfk_2` FOREIGN KEY (`item_code`) REFERENCES `stock` (`item_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



DROP TABLE IF EXISTS `payment`;
CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `invoice_no` (`invoice_no`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`invoice_no`) REFERENCES `invoice` (`invoice_no`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



DROP TABLE IF EXISTS `stock`;
CREATE TABLE `stock` (
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`item_code`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `stock` VALUES("CMOS-BAT","CMOS Battery (CR2032)",NULL,"96","150.00","In Stock");
INSERT INTO `stock` VALUES("cms-12","Dell OptiPlex","1","8","5000.00","In Stock");
INSERT INTO `stock` VALUES("FAN-CPU","CPU Cooling Fan",NULL,"6","1650.00",NULL);
INSERT INTO `stock` VALUES("itm-900","HP Envy","4","18","799.00","In Stock");
INSERT INTO `stock` VALUES("KBD-USB","USB Standard Keyboard",NULL,"0","1850.00","In Stock");
INSERT INTO `stock` VALUES("MON-22","22 Inch LED Monitor",NULL,"3","24500.00","In Stock");
INSERT INTO `stock` VALUES("MSE-OPT","Optical USB Mouse",NULL,"24","1200.00",NULL);
INSERT INTO `stock` VALUES("PWR-CB-L","Laptop Power Cable",NULL,"13","850.00",NULL);
INSERT INTO `stock` VALUES("RAM-8GB-D4","8GB DDR4 RAM",NULL,"46","8500.00",NULL);
INSERT INTO `stock` VALUES("SSD-256GB","256GB NVMe SSD",NULL,"24","9500.00",NULL);
INSERT INTO `stock` VALUES("SSD-512GB","512GB SATA SSD",NULL,"12","14500.00",NULL);
INSERT INTO `stock` VALUES("TH-PASTE","Thermal Paste (Arctic Silver)",NULL,"33","950.00",NULL);
INSERT INTO `stock` VALUES("ttm-90","Samsung 27-inch","2","34","789.00","In Stock");


DROP TABLE IF EXISTS `technicians`;
CREATE TABLE `technicians` (
  `technician_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`technician_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `technicians` VALUES("1","hasindu");
INSERT INTO `technicians` VALUES("10","panda");
INSERT INTO `technicians` VALUES("11","ramiru");
INSERT INTO `technicians` VALUES("9","sasintha");
INSERT INTO `technicians` VALUES("4","sauru");


