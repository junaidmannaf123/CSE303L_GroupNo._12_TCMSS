-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 21, 2025 at 05:12 PM
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
-- Database: `tccms`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblbreedingrecord`
--

CREATE TABLE `tblbreedingrecord` (
  `cbreedingid` varchar(8) NOT NULL,
  `nincubationperiod` decimal(10,0) DEFAULT NULL,
  `dstartdate` date DEFAULT NULL,
  `denddate` date DEFAULT NULL,
  `neggscount` decimal(10,0) DEFAULT NULL,
  `nhatchingservicerate` decimal(10,0) DEFAULT NULL,
  `cstaffid` varchar(8) DEFAULT NULL,
  `ctortoiseid` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblbreedingrecord`
--

INSERT INTO `tblbreedingrecord` (`cbreedingid`, `nincubationperiod`, `dstartdate`, `denddate`, `neggscount`, `nhatchingservicerate`, `cstaffid`, `ctortoiseid`) VALUES
('BR001', 65, '2025-03-01', '2025-05-05', 12, 75, 'SM003', '001'),
('BR002', 70, '2025-03-10', '2025-05-20', 10, 70, 'SM003', '002'),
('BR003', 68, '2025-03-18', '2025-05-25', 15, 80, 'SM003', '003'),
('BR004', 72, '2025-03-25', '2025-06-05', 18, 67, 'SM003', '004'),
('BR005', 66, '2025-04-02', '2025-06-07', 20, 80, 'SM003', '005'),
('BR006', 64, '2025-04-09', '2025-06-12', 14, 71, 'SM003', '006'),
('BR007', 69, '2025-04-15', '2025-06-23', 11, 82, 'SM003', '007'),
('BR008', 73, '2025-04-20', '2025-07-02', 17, 76, 'SM003', '008'),
('BR009', 67, '2025-04-27', '2025-07-03', 9, 56, 'SM003', '009'),
('BR010', 71, '2025-05-05', '2025-07-15', 16, 78, 'SM003', '010');

-- --------------------------------------------------------

--
-- Table structure for table `tbleggdetails`
--

CREATE TABLE `tbleggdetails` (
  `ceggid` varchar(8) NOT NULL,
  `nweight` decimal(10,0) DEFAULT NULL,
  `nlength` decimal(10,0) DEFAULT NULL,
  `nwidth` decimal(10,0) DEFAULT NULL,
  `ceggcondition` varchar(40) DEFAULT NULL,
  `cincubatorid` varchar(8) DEFAULT NULL,
  `cbreedingid` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbleggdetails`
--

INSERT INTO `tbleggdetails` (`ceggid`, `nweight`, `nlength`, `nwidth`, `ceggcondition`, `cincubatorid`, `cbreedingid`) VALUES
('E001', 52, 38, 35, 'Normal', 'INC001', 'BR001'),
('E002', 49, 38, 34, 'Normal', 'INC001', 'BR001'),
('E003', 48, 56, 38, 'Cracked', 'INC002', 'BR002'),
('E004', 55, 56, 40, 'Normal', 'INC002', 'BR002'),
('E005', 50, 55, 39, 'Normal', 'INC001', 'BR001'),
('E006', 47, 53, 37, 'Soft Shell', 'INC002', 'BR001'),
('E007', 54, 57, 41, 'Normal', 'INC003', 'BR003'),
('E008', 49, 52, 36, 'Normal', 'INC004', 'BR003'),
('E009', 53, 58, 40, 'Thin Shell', 'INC001', 'BR002'),
('E010', 51, 55, 39, 'Normal', 'INC002', 'BR002'),
('E011', 46, 54, 37, 'Soft Shell', 'INC001', 'BR004'),
('E012', 55, 59, 42, 'Normal', 'INC003', 'BR004'),
('E013', 52, 57, 38, 'Normal', 'INC004', 'BR005'),
('E014', 48, 53, 37, 'Cracked', 'INC001', 'BR005'),
('E015', 50, 56, 39, 'Normal', 'INC002', 'BR001');

-- --------------------------------------------------------

--
-- Table structure for table `tblenclosure`
--

CREATE TABLE `tblenclosure` (
  `cenclosureid` varchar(8) NOT NULL,
  `cenclosuretype` varchar(10) DEFAULT NULL,
  `clocation` varchar(30) DEFAULT NULL,
  `csize` varchar(255) DEFAULT NULL,
  `cstatus` varchar(20) DEFAULT NULL,
  `ncapacity` decimal(10,0) DEFAULT NULL,
  `cmaintenanceschedule` varchar(120) DEFAULT NULL,
  `chabitattype` varchar(40) DEFAULT NULL,
  `cstaffid` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblenclosure`
--

INSERT INTO `tblenclosure` (`cenclosureid`, `cenclosuretype`, `clocation`, `csize`, `cstatus`, `ncapacity`, `cmaintenanceschedule`, `chabitattype`, `cstaffid`) VALUES
('EN-1', 'Outdoor', 'North Zone', '50x40m', 'Active', 12, 'Weekly cleaning on Sundays', 'Tropical Forest', 'SM002'),
('EN-2', 'Outdoor', 'East Wing', '60x45m', 'Active', 15, 'Bi-weekly cleaning on Mondays', 'Grassland', 'SM002'),
('LAB', 'Indoor', 'Research Block A', '20x15m', 'Under Maintenance', 6, 'Monthly disinfection', 'Controlled Lab', 'SM002');

-- --------------------------------------------------------

--
-- Table structure for table `tblenvironmentaldata`
--

CREATE TABLE `tblenvironmentaldata` (
  `cenvironmentaldataid` varchar(8) NOT NULL,
  `ntemperature` decimal(10,0) DEFAULT NULL,
  `nhumidity` decimal(10,0) DEFAULT NULL,
  `cstatus` varchar(20) DEFAULT NULL,
  `cwaterquality` varchar(40) DEFAULT NULL,
  `dtimestamp` datetime DEFAULT NULL,
  `cincubatorid` varchar(8) DEFAULT NULL,
  `cenclosureid` varchar(8) DEFAULT NULL,
  `cstaffid` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblenvironmentaldata`
--

INSERT INTO `tblenvironmentaldata` (`cenvironmentaldataid`, `ntemperature`, `nhumidity`, `cstatus`, `cwaterquality`, `dtimestamp`, `cincubatorid`, `cenclosureid`, `cstaffid`) VALUES
('ED001', 29, 75, 'Stable', 'Good', '2025-08-15 09:30:00', 'INC001', 'EN-1', 'SM006'),
('ED002', 31, 70, 'Warning', 'Moderate', '2025-08-15 10:00:00', 'INC003', 'EN-2', 'SM006'),
('ED003', 27, 80, 'Stable', 'Excellent', '2025-08-15 11:15:00', 'INC002', 'LAB', 'SM006'),
('ED004', 33, 65, 'Critical', 'Poor', '2025-08-16 14:45:00', 'INC001', 'EN-1', 'SM006'),
('ED005', 28, 78, 'Stable', 'Good', '2025-08-16 15:30:00', 'INC003', 'EN-2', 'SM006'),
('ED006', 30, 72, 'Warning', 'Moderate', '2025-08-17 09:20:00', 'INC004', 'EN-1', 'SM006'),
('ED007', 26, 85, 'Stable', 'Excellent', '2025-08-17 12:10:00', 'INC002', 'LAB', 'SM006'),
('ED008', 34, 60, 'Critical', 'Contaminated', '2025-08-18 13:40:00', 'INC001', 'EN-2', 'SM006');

-- --------------------------------------------------------

--
-- Table structure for table `tblfeedingschedule`
--

CREATE TABLE `tblfeedingschedule` (
  `cfeedingid` varchar(8) NOT NULL,
  `ddate` date DEFAULT NULL,
  `dtime` time DEFAULT NULL,
  `cdietnotes` varchar(255) DEFAULT NULL,
  `cstaffid` varchar(8) DEFAULT NULL,
  `cenclosureid` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblfeedingschedule`
--

INSERT INTO `tblfeedingschedule` (`cfeedingid`, `ddate`, `dtime`, `cdietnotes`, `cstaffid`, `cenclosureid`) VALUES
('FD001', '2025-08-10', '09:00:00', 'Leafy greens and vegetables provided', 'SM002', 'EN-1'),
('FD002', '2025-08-10', '15:00:00', 'Fruit supplements and hydration check', 'SM002', 'EN-2'),
('FD003', '2025-08-11', '09:30:00', 'Protein pellet feed for juveniles', 'SM006', 'LAB'),
('FD004', '2025-08-11', '16:00:00', 'Mixed diet – greens, fruits, and supplements', 'SM002', 'EN-1'),
('FD005', '2025-08-12', '10:00:00', 'Vitamin-enriched greens for weak tortoises', 'SM004', 'EN-2'),
('FD006', '2025-08-12', '15:30:00', 'Hydration therapy and fruit slices', 'SM002', 'EN-1'),
('FD007', '2025-08-13', '09:15:00', 'Fresh mushrooms and leafy mix', 'SM006', 'LAB'),
('FD008', '2025-08-13', '14:45:00', 'Supplement pellets with calcium powder', 'SM002', 'EN-2'),
('FD009', '2025-01-14', '08:50:00', 'Water Spinach, Bottle Gourd, Pumpkin', 'SM002', 'EN-1'),
('FD010', '2025-01-14', '09:05:00', 'Water Spinach, Bottle Gourd, Pumpkin', 'SM002', 'EN-2'),
('FD011', '2025-01-14', '09:15:00', 'Water Spinach, Pumpkin', 'SM002', 'LAB'),
('FD012', '2025-01-15', '09:30:00', 'Water Spinach, Pumpkin', 'SM002', 'LAB'),
('FD013', '2025-01-16', '08:40:00', 'Water Spinach, Pumpkin', 'SM002', 'LAB'),
('FD014', '2025-01-17', '09:00:00', 'Water Spinach, Gourd', 'SM002', 'LAB'),
('FD015', '2025-01-18', '09:00:00', 'Pumpkin, Gourd, Spinach', 'SM002', 'EN-1'),
('FD016', '2025-01-18', '09:20:00', 'Pumpkin, Gourd, Spinach', 'SM002', 'EN-2'),
('FD017', '2025-01-18', '09:30:00', 'Pumpkin, Gourd, Spinach', 'SM002', 'LAB'),
('FD018', '2025-01-19', '09:15:00', 'Pumpkin, Spinach', 'SM002', 'LAB'),
('FD019', '2025-01-20', '09:10:00', 'Pumpkin, Spinach', 'SM002', 'LAB'),
('FD020', '2025-01-21', '08:10:00', 'Gourd, Pumpkin, Spinach', 'SM002', 'LAB'),
('FD021', '2025-01-22', '08:40:00', 'Pumpkin, Papaya, Spinach', 'SM002', 'EN-1'),
('FD022', '2025-01-22', '08:50:00', 'Pumpkin, Papaya, Spinach', 'SM002', 'EN-2'),
('FD023', '2025-01-22', '09:15:00', 'Pumpkin, Papaya, Spinach', 'SM002', 'LAB'),
('FD024', '2025-01-23', '09:30:00', 'Spinach, Pumpkin', 'SM002', 'LAB'),
('FD025', '2025-01-24', '10:30:00', 'Red Spinach, Pumpkin', 'SM002', 'LAB'),
('FD026', '2025-01-25', '10:30:00', 'Red Spinach, Carrot', 'SM002', 'LAB'),
('FD027', '2025-01-27', '09:20:00', 'Spinach, Papaya, Carrot', 'SM002', 'EN-1'),
('FD028', '2025-01-27', '09:40:00', 'Spinach, Papaya, Carrot', 'SM002', 'EN-2'),
('FD029', '2025-01-27', '09:50:00', 'Spinach, Papaya, Carrot', 'SM002', 'LAB'),
('FD030', '2025-01-28', '09:25:00', 'Pumpkin, Red Spinach, Papaya', 'SM002', 'LAB');

-- --------------------------------------------------------

--
-- Table structure for table `tblincubationresult`
--

CREATE TABLE `tblincubationresult` (
  `cincubatorid` varchar(8) NOT NULL,
  `cbreedingid` varchar(8) DEFAULT NULL,
  `ngiveneggno` decimal(10,0) DEFAULT NULL,
  `nhatchedeggno` decimal(10,0) DEFAULT NULL,
  `nsuccessrate` decimal(10,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblincubationresult`
--

INSERT INTO `tblincubationresult` (`cincubatorid`, `cbreedingid`, `ngiveneggno`, `nhatchedeggno`, `nsuccessrate`) VALUES
('INC001', 'BR001', 12, 9, 75),
('INC002', 'BR002', 10, 7, 70),
('INC003', 'BR003', 15, 12, 80),
('INC004', 'BR004', 18, 12, 67),
('INC005', 'BR005', 20, 16, 80),
('INC006', 'BR006', 14, 10, 71),
('INC007', 'BR007', 11, 9, 82),
('INC008', 'BR008', 17, 13, 76),
('INC009', 'BR009', 9, 5, 56),
('INC010', 'BR010', 16, 12, 75);

-- --------------------------------------------------------

--
-- Table structure for table `tblincubator`
--

CREATE TABLE `tblincubator` (
  `cincubatorid` varchar(8) NOT NULL,
  `cincubator_type` varchar(20) DEFAULT NULL,
  `cstatus` varchar(10) DEFAULT NULL,
  `ncapacity` decimal(10,0) DEFAULT NULL,
  `cmaintenanceschedule` varchar(15) DEFAULT NULL,
  `coptimumhumidityrange` varchar(30) DEFAULT NULL,
  `coptimumtemperaturerange` varchar(30) DEFAULT NULL,
  `cstaffid` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblincubator`
--

INSERT INTO `tblincubator` (`cincubatorid`, `cincubator_type`, `cstatus`, `ncapacity`, `cmaintenanceschedule`, `coptimumhumidityrange`, `coptimumtemperaturerange`, `cstaffid`) VALUES
('INC001', 'Standard', 'Active', 50, 'Weekly', '70-80%', '28-32°C', 'SM006'),
('INC002', 'Research', 'Active', 30, 'Bi-weekly', '75-85%', '27-31°C', 'SM006'),
('INC003', 'Medical', 'Inactive', 20, 'Monthly', '65-75%', '26-30°C', 'SM006'),
('INC004', 'Large-Capacity', 'Active', 80, 'Weekly', '70-85%', '28-33°C', 'SM006');

-- --------------------------------------------------------

--
-- Table structure for table `tblinventory`
--

CREATE TABLE `tblinventory` (
  `cinventoryid` varchar(8) NOT NULL,
  `citemname` varchar(15) DEFAULT NULL,
  `ntotalquantity` decimal(10,0) DEFAULT NULL,
  `dlastrestockdate` date DEFAULT NULL,
  `cstatus` varchar(10) DEFAULT NULL,
  `cstoragelocation` varchar(70) DEFAULT NULL,
  `cproductid` varchar(8) DEFAULT NULL,
  `cstaffid` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblinventory`
--

INSERT INTO `tblinventory` (`cinventoryid`, `citemname`, `ntotalquantity`, `dlastrestockdate`, `cstatus`, `cstoragelocation`, `cproductid`, `cstaffid`) VALUES
('INV001', 'Leafy Greens', 120, '2025-07-15', 'Available', 'Storage Room A - Shelf 1', 'P001', 'SM005'),
('INV002', 'Fruit Mix', 85, '2025-07-20', 'Available', 'Storage Room A - Shelf 2', 'P002', 'SM005'),
('INV003', 'Calcium Powder', 45, '2025-06-30', 'Low', 'Storage Room B - Shelf 3', 'P003', 'SM005'),
('INV004', 'Vitamin D3', 38, '2025-07-10', 'Available', 'Medical Store - Cabinet 1', 'P004', 'SM005'),
('INV005', 'Heat Lamp', 20, '2025-05-25', 'Low', 'Equipment Store - Rack A', 'P005', 'SM005'),
('INV006', 'Substrate Pack', 55, '2025-07-18', 'Available', 'Storage Room C - Shelf 2', 'P006', 'SM005'),
('INV007', 'Disinfectant', 30, '2025-06-28', 'Available', 'Medical Store - Cabinet 2', 'P007', 'SM005'),
('INV008', 'Water Filter', 95, '2025-07-12', 'Available', 'Equipment Store - Rack B', 'P008', 'SM005'),
('INV009', 'Pellet Feed', 70, '2025-07-05', 'Low', 'Storage Room A - Shelf 3', 'P009', 'SM005'),
('INV010', 'Herbal Kit', 18, '2025-07-22', 'Available', 'Medical Store - Cabinet 3', 'P010', 'SM005');

-- --------------------------------------------------------

--
-- Table structure for table `tblmedicalrecords`
--

CREATE TABLE `tblmedicalrecords` (
  `crecordid` varchar(8) NOT NULL,
  `drecordingdate` date DEFAULT NULL,
  `cdiagnosis` varchar(255) DEFAULT NULL,
  `ctreatment` varchar(255) DEFAULT NULL,
  `ctype` varchar(40) DEFAULT NULL,
  `ddate` date DEFAULT NULL,
  `cvaccinationstatus` varchar(30) DEFAULT NULL,
  `dcheckdate` date DEFAULT NULL,
  `dchecktime` time DEFAULT NULL,
  `cstaffid` varchar(8) DEFAULT NULL,
  `ctortoiseid` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblmedicalrecords`
--

INSERT INTO `tblmedicalrecords` (`crecordid`, `drecordingdate`, `cdiagnosis`, `ctreatment`, `ctype`, `ddate`, `cvaccinationstatus`, `dcheckdate`, `dchecktime`, `cstaffid`, `ctortoiseid`) VALUES
('MR001', '2025-05-10', 'Respiratory Infection', 'Antibiotics (Enrofloxacin)', 'Treatment', '2025-05-11', 'Up-to-date', '2025-05-10', '10:30:00', 'SM004', '001'),
('MR002', '2025-05-12', 'Routine Health Check', 'No treatment required', 'Checkup', '2025-05-12', 'Pending', '2025-05-12', '09:45:00', 'SM004', '002'),
('MR003', '2025-05-15', 'Shell Damage (minor crack)', 'Wound cleaning & Resin seal', 'Treatment', '2025-05-15', 'Up-to-date', '2025-05-15', '11:45:00', 'SM004', '005'),
('MR004', '2025-05-18', 'Vitamin Deficiency', 'Calcium + Vitamin D3 Supplement', 'Treatment', '2025-05-18', 'Up-to-date', '2025-05-18', '14:15:00', 'SM004', '003'),
('MR005', '2025-05-20', 'Routine Health Check', 'Weight monitoring', 'Checkup', '2025-05-20', 'Pending', '2025-05-20', '10:20:00', 'SM004', '006'),
('MR006', '2025-05-22', 'Parasitic Infection', 'Deworming Medication', 'Treatment', '2025-05-22', 'Up-to-date', '2025-05-22', '15:30:00', 'SM004', '008'),
('MR007', '2025-05-25', 'Shell Rot (fungal)', 'Topical Antifungal Cream', 'Treatment', '2025-05-25', 'Up-to-date', '2025-05-25', '11:00:00', 'SM004', '009'),
('MR008', '2025-05-27', 'Lethargy & Poor Appetite', 'Hydration Therapy', 'Treatment', '2025-05-27', 'Up-to-date', '2025-05-27', '16:45:00', 'SM004', '010'),
('MR009', '2025-05-30', 'Routine Health Check', 'No treatment required', 'Checkup', '2025-05-30', 'Pending', '2025-05-30', '09:00:00', 'SM004', '011'),
('MR010', '2025-06-01', 'Annual Vaccination', 'Administered Reptile Vaccine', 'Vaccination', '2025-06-01', 'Up-to-date', '2025-06-01', '13:20:00', 'SM004', '012'),
('MR011', '2025-06-02', 'Eye Infection', 'Ophthalmic Antibiotic Drops', 'Treatment', '2025-06-02', 'Up-to-date', '2025-06-02', '12:40:00', 'SM004', '013'),
('MR012', '2025-06-03', 'Minor Shell Injury', 'Shell disinfectant & bandage', 'Treatment', '2025-06-03', 'Up-to-date', '2025-06-03', '15:00:00', 'SM004', '014'),
('MR013', '2025-06-04', 'Routine Checkup', 'Growth and weight monitoring', 'Checkup', '2025-06-04', 'Pending', '2025-06-04', '10:30:00', 'SM004', '015'),
('MR014', '2025-06-05', 'Respiratory Infection', 'Nebulization + Antibiotics', 'Treatment', '2025-06-05', 'Up-to-date', '2025-06-05', '11:15:00', 'SM004', '016'),
('MR015', '2025-06-06', 'Vitamin Deficiency', 'Vitamin Supplement (A+D3)', 'Treatment', '2025-06-06', 'Up-to-date', '2025-06-06', '14:00:00', 'SM004', '017'),
('MR016', '2025-06-07', 'Routine Health Check', 'No treatment required', 'Checkup', '2025-06-07', 'Pending', '2025-06-07', '09:25:00', 'SM004', '018'),
('MR017', '2025-06-08', 'Shell Rot (fungal)', 'Antifungal Wash', 'Treatment', '2025-06-08', 'Up-to-date', '2025-06-08', '13:45:00', 'SM004', '019'),
('MR018', '2025-06-09', 'Annual Vaccination', 'Administered Reptile Vaccine', 'Vaccination', '2025-06-09', 'Up-to-date', '2025-06-09', '11:50:00', 'SM004', '020'),
('MR019', '2025-06-10', 'Eye Irritation', 'Saline wash + Antibiotic drops', 'Treatment', '2025-06-10', 'Up-to-date', '2025-06-10', '12:30:00', 'SM004', '021'),
('MR020', '2025-06-11', 'Routine Health Check', 'No treatment required', 'Checkup', '2025-06-11', 'Pending', '2025-06-11', '10:15:00', 'SM004', '022'),
('MR021', '2025-06-12', 'Respiratory Congestion', 'Supportive Therapy', 'Treatment', '2025-06-12', 'Up-to-date', '2025-06-12', '14:10:00', 'SM004', '023'),
('MR022', '2025-06-13', 'Parasitic Infection', 'Deworming Medicine', 'Treatment', '2025-06-13', 'Up-to-date', '2025-06-13', '11:20:00', 'SM004', '024'),
('MR023', '2025-06-14', 'Routine Checkup', 'Growth measurement', 'Checkup', '2025-06-14', 'Pending', '2025-06-14', '09:40:00', 'SM004', '025'),
('MR024', '2025-06-15', 'Shell Damage (surface scratch)', 'Antiseptic wash & bandage', 'Treatment', '2025-06-15', 'Up-to-date', '2025-06-15', '16:00:00', 'SM004', '007'),
('MR025', '2025-06-16', 'Vitamin Deficiency', 'Multivitamin Supplement', 'Treatment', '2025-06-16', 'Up-to-date', '2025-06-16', '13:10:00', 'SM004', '004'),
('MR026', '2025-06-17', 'Dehydration', 'Subcutaneous Fluids & Hydration Therapy', 'Treatment', '2025-06-17', 'Up-to-date', '2025-06-17', '10:40:00', 'SM004', '002'),
('MR027', '2025-06-18', 'Eye Swelling', 'Ophthalmic Ointment', 'Treatment', '2025-06-18', 'Up-to-date', '2025-06-18', '12:15:00', 'SM004', '006'),
('MR028', '2025-06-19', 'Routine Health Check', 'No treatment required', 'Checkup', '2025-06-19', 'Pending', '2025-06-19', '09:25:00', 'SM004', '015'),
('MR029', '2025-06-20', 'Shell Rot (bacterial)', 'Topical Antibiotic Wash', 'Treatment', '2025-06-20', 'Up-to-date', '2025-06-20', '14:20:00', 'SM004', '018'),
('MR030', '2025-06-21', 'Annual Vaccination', 'Reptile Vaccine administered', 'Vaccination', '2025-06-21', 'Up-to-date', '2025-06-21', '11:50:00', 'SM004', '025');

-- --------------------------------------------------------

--
-- Table structure for table `tblproduct`
--

CREATE TABLE `tblproduct` (
  `cproductid` varchar(8) NOT NULL,
  `cproductname` varchar(40) DEFAULT NULL,
  `nquantity` decimal(10,0) DEFAULT NULL,
  `dproductiondate` date DEFAULT NULL,
  `dexpiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblproduct`
--

INSERT INTO `tblproduct` (`cproductid`, `cproductname`, `nquantity`, `dproductiondate`, `dexpiry_date`) VALUES
('P001', 'Leafy Greens Mix', 120, '2025-05-10', '2025-08-10'),
('P002', 'Fruit Supplement', 80, '2025-04-22', '2025-09-22'),
('P003', 'Calcium Powder', 50, '2025-03-15', '2026-03-15'),
('P004', 'Vitamin D3 Drops', 40, '2025-05-01', '2026-05-01'),
('P005', 'Heat Lamp Bulb', 25, '2025-01-12', '2027-01-12'),
('P006', 'Substrate Pack', 60, '2025-02-20', '2027-02-20'),
('P007', 'Medical Disinfectant', 35, '2025-03-05', '2026-03-05'),
('P008', 'Water Filter Cartridge', 100, '2025-04-01', '2026-04-01'),
('P009', 'Protein Pellet Feed', 90, '2025-05-15', '2025-10-15'),
('P010', 'Herbal Medicine Kit', 20, '2025-02-01', '2026-02-01');

-- --------------------------------------------------------

--
-- Table structure for table `tblproductquality`
--

CREATE TABLE `tblproductquality` (
  `cinventoryid` varchar(8) NOT NULL,
  `cfeedingid` varchar(8) NOT NULL,
  `nquantityused` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblproductquality`
--

INSERT INTO `tblproductquality` (`cinventoryid`, `cfeedingid`, `nquantityused`) VALUES
('INV001', 'FD001', 5),
('INV002', 'FD002', 8),
('INV003', 'FD003', 3),
('INV004', 'FD004', 6),
('INV005', 'FD005', 2),
('INV006', 'FD006', 7),
('INV007', 'FD007', 4),
('INV008', 'FD008', 10),
('INV009', 'FD009', 5),
('INV010', 'FD010', 6);

-- --------------------------------------------------------

--
-- Table structure for table `tblspecies`
--

CREATE TABLE `tblspecies` (
  `cspeciesid` varchar(8) NOT NULL,
  `ccommonname` varchar(80) DEFAULT NULL,
  `cscientificname` varchar(120) DEFAULT NULL,
  `naveragelifespan` decimal(10,0) DEFAULT NULL,
  `cdietaryrequirements` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblspecies`
--

INSERT INTO `tblspecies` (`cspeciesid`, `ccommonname`, `cscientificname`, `naveragelifespan`, `cdietaryrequirements`) VALUES
('S1', 'Asian Giant Tortoise', 'Manouria emys', 60, 'Herbivorous – fruits, leaves, grasses, mushrooms'),
('S2', 'Arakan Forest Turtle', 'Heosemys depressa', 50, 'Omnivorous – leaves, fruits, insects, worms'),
('S3', 'Elongated Tortoise', 'Indotestudo elongata', 40, 'Herbivorous – leafy greens, fruits, fungi'),
('S4', 'Keeled Box Turtle', 'Cuora mouhotii', 35, 'Omnivorous – plants, berries, invertebrates, carrion');

-- --------------------------------------------------------

--
-- Table structure for table `tblstaffmember`
--

CREATE TABLE `tblstaffmember` (
  `cstaffid` varchar(8) NOT NULL,
  `cname` varchar(15) DEFAULT NULL,
  `cemail` varchar(15) DEFAULT NULL,
  `cphone` varchar(20) DEFAULT NULL,
  `cstreet` varchar(35) DEFAULT NULL,
  `chousenumber` varchar(10) DEFAULT NULL,
  `ccity` varchar(30) DEFAULT NULL,
  `czip` varchar(5) DEFAULT NULL,
  `crole_type` varchar(40) DEFAULT NULL,
  `cshift` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblstaffmember`
--

INSERT INTO `tblstaffmember` (`cstaffid`, `cname`, `cemail`, `cphone`, `cstreet`, `chousenumber`, `ccity`, `czip`, `crole_type`, `cshift`) VALUES
('SM001', 'Rahim Khan', 'rahimk@tcc.org', '+8801712345678', 'Green Road', '12A', 'Dhaka', '1205', 'Manager', 'Morning'),
('SM002', 'Anika Sultana', 'anika@tcc.org', '+8801811223344', 'Banani Lane', '22B', 'Dhaka', '1213', 'Tortoise Caretaker', 'Evening'),
('SM003', 'Jamil Hossain', 'jamil@tcc.org', '+8801911456789', 'College Road', '5C', 'Chattogram', '4000', 'Breeding Specialist', 'Morning'),
('SM004', 'Farhana Rahman', 'farhana@tcc.org', '+8801719988776', 'Station Road', '9D', 'Sylhet', '3100', 'Veterinarian', 'Night'),
('SM005', 'Tanvir Alam', 'tanvir@tcc.org', '+8801511223344', 'Main Street', '34E', 'Rajshahi', '6200', 'Inventory Manager', 'Morning'),
('SM006', 'Mehedi Hasan', 'mehedi@tcc.org', '+8801612334455', 'Park Avenue', '18F', 'Khulna', '9100', 'Environment Monitor', 'Evening');

-- --------------------------------------------------------

--
-- Table structure for table `tbltortoise`
--

CREATE TABLE `tbltortoise` (
  `ctortoiseid` varchar(8) NOT NULL,
  `cname` varchar(10) DEFAULT NULL,
  `nage` decimal(10,0) DEFAULT NULL,
  `cgender` varchar(10) DEFAULT NULL,
  `cenclosureid` varchar(8) DEFAULT NULL,
  `cspeciesid` varchar(8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbltortoise`
--

INSERT INTO `tbltortoise` (`ctortoiseid`, `cname`, `nage`, `cgender`, `cenclosureid`, `cspeciesid`) VALUES
('001', 'Shella', 69, 'juvenile', 'EN-1', 'S3'),
('002', 'Boulder', 75, 'juvenile', 'EN-1', 'S3'),
('003', 'Mossy', 5, 'male', 'Lab', 'S1'),
('004', 'Pebble', 76, 'juvenile', 'EN-1', 'S2'),
('005', 'Spike', 33, 'male', 'EN-2', 'S4'),
('006', 'Hazel', 44, 'female', 'EN-1', 'S4'),
('007', 'Drift', 52, 'male', 'Lab', 'S2'),
('008', 'Clover', 14, 'juvenile', 'EN-2', 'S1'),
('009', 'Terra', 68, 'female', 'EN-1', 'S3'),
('010', 'Stone', 23, 'male', 'EN-2', 'S4'),
('011', 'River', 57, 'juvenile', 'Lab', 'S2'),
('012', 'Olive', 19, 'female', 'EN-1', 'S1'),
('013', 'Quartz', 11, 'male', 'EN-2', 'S3'),
('014', 'Lime', 42, 'juvenile', 'EN-1', 'S4'),
('015', 'Amber', 38, 'female', 'EN-2', 'S2'),
('016', 'Flint', 25, 'male', 'Lab', 'S1'),
('017', 'Ivy', 61, 'female', 'EN-1', 'S3'),
('018', 'Rocky', 36, 'juvenile', 'EN-2', 'S4'),
('019', 'Ash', 47, 'male', 'EN-1', 'S2'),
('020', 'Willow', 29, 'female', 'Lab', 'S1'),
('021', 'Dusty', 71, 'juvenile', 'EN-2', 'S4'),
('022', 'Coral', 55, 'female', 'EN-1', 'S2'),
('023', 'Marble', 40, 'male', 'Lab', 'S3'),
('024', 'Nova', 12, 'juvenile', 'EN-2', 'S1'),
('025', 'Sage', 64, 'female', 'EN-1', 'S4'),
('026', 'Luna', 8, 'juvenile', 'EN-2', 'S1'),
('027', 'Orion', 42, 'male', 'LAB', 'S2'),
('028', 'Misty', 25, 'female', 'EN-1', 'S3'),
('029', 'Shadow', 60, 'male', 'EN-2', 'S4'),
('030', 'Coralina', 15, 'juvenile', 'EN-1', 'S1');

-- --------------------------------------------------------

--
-- Table structure for table `tbltortoisemeasurement`
--

CREATE TABLE `tbltortoisemeasurement` (
  `cmeasurementid` varchar(8) NOT NULL,
  `ncarapace_length` decimal(10,0) DEFAULT NULL,
  `nplastron_length` decimal(10,0) DEFAULT NULL,
  `nwidth` decimal(10,0) DEFAULT NULL,
  `nlength` decimal(10,0) DEFAULT NULL,
  `cnotes` varchar(255) DEFAULT NULL,
  `ctortoiseid` varchar(8) DEFAULT NULL,
  `nweight` decimal(10,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbltortoisemeasurement`
--

INSERT INTO `tbltortoisemeasurement` (`cmeasurementid`, `ncarapace_length`, `nplastron_length`, `nwidth`, `nlength`, `cnotes`, `ctortoiseid`, `nweight`) VALUES
('138', NULL, NULL, NULL, NULL, 'Deceased', NULL, NULL),
('139', 92, 90, 82, 45, NULL, NULL, 178),
('140', 128, 130, 110, 53, NULL, NULL, 413),
('141', 122, 120, 110, 53, NULL, NULL, 407),
('142', 140, 142, 130, 52, '-', NULL, 580),
('143', 132, 133, 122, 55, '↓ weight', NULL, 483),
('144', 125, 124, 112, 53, NULL, NULL, 422),
('145', 134, 132, 115, 56, NULL, NULL, 509),
('146', NULL, NULL, NULL, NULL, 'Deceased', NULL, NULL),
('147', 155, 155, 138, 64, NULL, NULL, 794),
('148', 130, 132, 118, 53, NULL, NULL, 485),
('149', 124, 126, 112, 50, NULL, NULL, 420),
('150', 115, 120, 100, 50, NULL, NULL, 323),
('151', 148, 142, 130, 60, NULL, NULL, 606),
('152', 128, 130, 120, 53, '↓ weight', NULL, 422),
('153', 130, 132, 114, 55, NULL, NULL, 490),
('154', 123, 120, 108, 56, NULL, NULL, 428),
('155', 148, 145, 128, 58, NULL, NULL, 597),
('156', 128, 123, 114, 54, '↓ weight', NULL, 422),
('157', 140, 138, 120, 55, NULL, NULL, 500),
('158', 130, 130, 112, 55, NULL, NULL, 494),
('159', 132, 130, 112, 56, NULL, NULL, 477),
('160', 130, 132, 110, 54, NULL, NULL, 458),
('161', 130, 128, 115, 58, NULL, NULL, 476),
('163', 135, 138, 120, 54, NULL, NULL, 533),
('164', 126, 130, 112, 58, NULL, NULL, 437),
('165', 136, 134, 112, 55, NULL, NULL, 510),
('166', 110, 172, 104, 48, NULL, NULL, 306),
('167', 112, 114, 108, 50, NULL, NULL, 334),
('168', 130, 130, 120, 57, '↓ weight', NULL, 473);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblbreedingrecord`
--
ALTER TABLE `tblbreedingrecord`
  ADD PRIMARY KEY (`cbreedingid`),
  ADD KEY `cstaffid` (`cstaffid`),
  ADD KEY `ctortoiseid` (`ctortoiseid`);

--
-- Indexes for table `tbleggdetails`
--
ALTER TABLE `tbleggdetails`
  ADD PRIMARY KEY (`ceggid`),
  ADD KEY `cincubatorid` (`cincubatorid`),
  ADD KEY `cbreedingid` (`cbreedingid`);

--
-- Indexes for table `tblenclosure`
--
ALTER TABLE `tblenclosure`
  ADD PRIMARY KEY (`cenclosureid`),
  ADD KEY `cstaffid` (`cstaffid`);

--
-- Indexes for table `tblenvironmentaldata`
--
ALTER TABLE `tblenvironmentaldata`
  ADD PRIMARY KEY (`cenvironmentaldataid`),
  ADD KEY `cincubatorid` (`cincubatorid`),
  ADD KEY `cenclosureid` (`cenclosureid`),
  ADD KEY `cstaffid` (`cstaffid`);

--
-- Indexes for table `tblfeedingschedule`
--
ALTER TABLE `tblfeedingschedule`
  ADD PRIMARY KEY (`cfeedingid`),
  ADD KEY `cstaffid` (`cstaffid`),
  ADD KEY `cenclosureid` (`cenclosureid`);

--
-- Indexes for table `tblincubationresult`
--
ALTER TABLE `tblincubationresult`
  ADD PRIMARY KEY (`cincubatorid`),
  ADD KEY `cbreedingid` (`cbreedingid`);

--
-- Indexes for table `tblincubator`
--
ALTER TABLE `tblincubator`
  ADD PRIMARY KEY (`cincubatorid`),
  ADD KEY `cstaffid` (`cstaffid`);

--
-- Indexes for table `tblinventory`
--
ALTER TABLE `tblinventory`
  ADD PRIMARY KEY (`cinventoryid`),
  ADD KEY `cproductid` (`cproductid`),
  ADD KEY `cstaffid` (`cstaffid`);

--
-- Indexes for table `tblmedicalrecords`
--
ALTER TABLE `tblmedicalrecords`
  ADD PRIMARY KEY (`crecordid`),
  ADD KEY `cstaffid` (`cstaffid`),
  ADD KEY `ctortoiseid` (`ctortoiseid`);

--
-- Indexes for table `tblproduct`
--
ALTER TABLE `tblproduct`
  ADD PRIMARY KEY (`cproductid`);

--
-- Indexes for table `tblproductquality`
--
ALTER TABLE `tblproductquality`
  ADD PRIMARY KEY (`cinventoryid`,`cfeedingid`),
  ADD KEY `cfeedingid` (`cfeedingid`);

--
-- Indexes for table `tblspecies`
--
ALTER TABLE `tblspecies`
  ADD PRIMARY KEY (`cspeciesid`);

--
-- Indexes for table `tblstaffmember`
--
ALTER TABLE `tblstaffmember`
  ADD PRIMARY KEY (`cstaffid`);

--
-- Indexes for table `tbltortoise`
--
ALTER TABLE `tbltortoise`
  ADD PRIMARY KEY (`ctortoiseid`),
  ADD KEY `cenclosureid` (`cenclosureid`),
  ADD KEY `cspeciesid` (`cspeciesid`);

--
-- Indexes for table `tbltortoisemeasurement`
--
ALTER TABLE `tbltortoisemeasurement`
  ADD PRIMARY KEY (`cmeasurementid`),
  ADD KEY `ctortoiseid` (`ctortoiseid`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblbreedingrecord`
--
ALTER TABLE `tblbreedingrecord`
  ADD CONSTRAINT `tblbreedingrecord_ibfk_1` FOREIGN KEY (`cstaffid`) REFERENCES `tblstaffmember` (`cstaffid`),
  ADD CONSTRAINT `tblbreedingrecord_ibfk_2` FOREIGN KEY (`ctortoiseid`) REFERENCES `tbltortoise` (`ctortoiseid`);

--
-- Constraints for table `tbleggdetails`
--
ALTER TABLE `tbleggdetails`
  ADD CONSTRAINT `tbleggdetails_ibfk_1` FOREIGN KEY (`cincubatorid`) REFERENCES `tblincubator` (`cincubatorid`),
  ADD CONSTRAINT `tbleggdetails_ibfk_2` FOREIGN KEY (`cbreedingid`) REFERENCES `tblbreedingrecord` (`cbreedingid`);

--
-- Constraints for table `tblenclosure`
--
ALTER TABLE `tblenclosure`
  ADD CONSTRAINT `tblenclosure_ibfk_1` FOREIGN KEY (`cstaffid`) REFERENCES `tblstaffmember` (`cstaffid`);

--
-- Constraints for table `tblenvironmentaldata`
--
ALTER TABLE `tblenvironmentaldata`
  ADD CONSTRAINT `tblenvironmentaldata_ibfk_1` FOREIGN KEY (`cincubatorid`) REFERENCES `tblincubator` (`cincubatorid`),
  ADD CONSTRAINT `tblenvironmentaldata_ibfk_2` FOREIGN KEY (`cenclosureid`) REFERENCES `tblenclosure` (`cenclosureid`),
  ADD CONSTRAINT `tblenvironmentaldata_ibfk_3` FOREIGN KEY (`cstaffid`) REFERENCES `tblstaffmember` (`cstaffid`);

--
-- Constraints for table `tblfeedingschedule`
--
ALTER TABLE `tblfeedingschedule`
  ADD CONSTRAINT `tblfeedingschedule_ibfk_1` FOREIGN KEY (`cstaffid`) REFERENCES `tblstaffmember` (`cstaffid`),
  ADD CONSTRAINT `tblfeedingschedule_ibfk_2` FOREIGN KEY (`cenclosureid`) REFERENCES `tblenclosure` (`cenclosureid`);

--
-- Constraints for table `tblincubationresult`
--
ALTER TABLE `tblincubationresult`
  ADD CONSTRAINT `tblincubationresult_ibfk_1` FOREIGN KEY (`cbreedingid`) REFERENCES `tblbreedingrecord` (`cbreedingid`);

--
-- Constraints for table `tblincubator`
--
ALTER TABLE `tblincubator`
  ADD CONSTRAINT `tblincubator_ibfk_1` FOREIGN KEY (`cstaffid`) REFERENCES `tblstaffmember` (`cstaffid`);

--
-- Constraints for table `tblinventory`
--
ALTER TABLE `tblinventory`
  ADD CONSTRAINT `tblinventory_ibfk_1` FOREIGN KEY (`cproductid`) REFERENCES `tblproduct` (`cproductid`),
  ADD CONSTRAINT `tblinventory_ibfk_2` FOREIGN KEY (`cstaffid`) REFERENCES `tblstaffmember` (`cstaffid`);

--
-- Constraints for table `tblmedicalrecords`
--
ALTER TABLE `tblmedicalrecords`
  ADD CONSTRAINT `tblmedicalrecords_ibfk_1` FOREIGN KEY (`cstaffid`) REFERENCES `tblstaffmember` (`cstaffid`),
  ADD CONSTRAINT `tblmedicalrecords_ibfk_2` FOREIGN KEY (`ctortoiseid`) REFERENCES `tbltortoise` (`ctortoiseid`);

--
-- Constraints for table `tblproductquality`
--
ALTER TABLE `tblproductquality`
  ADD CONSTRAINT `tblproductquality_ibfk_1` FOREIGN KEY (`cinventoryid`) REFERENCES `tblinventory` (`cinventoryid`),
  ADD CONSTRAINT `tblproductquality_ibfk_2` FOREIGN KEY (`cfeedingid`) REFERENCES `tblfeedingschedule` (`cfeedingid`);

--
-- Constraints for table `tbltortoise`
--
ALTER TABLE `tbltortoise`
  ADD CONSTRAINT `tbltortoise_ibfk_1` FOREIGN KEY (`cenclosureid`) REFERENCES `tblenclosure` (`cenclosureid`),
  ADD CONSTRAINT `tbltortoise_ibfk_2` FOREIGN KEY (`cspeciesid`) REFERENCES `tblspecies` (`cspeciesid`);

--
-- Constraints for table `tbltortoisemeasurement`
--
ALTER TABLE `tbltortoisemeasurement`
  ADD CONSTRAINT `tbltortoisemeasurement_ibfk_1` FOREIGN KEY (`ctortoiseid`) REFERENCES `tbltortoise` (`ctortoiseid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
