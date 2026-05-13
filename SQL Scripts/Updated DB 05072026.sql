-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: usep_epark
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `audit_payment_log`
--

DROP TABLE IF EXISTS `audit_payment_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_payment_log` (
  `audit_id` int NOT NULL AUTO_INCREMENT,
  `log_id` int NOT NULL,
  `payment_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` varchar(50) NOT NULL,
  `recorded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  KEY `idx_audit_log_id` (`log_id`),
  KEY `idx_audit_payment_id` (`payment_id`),
  KEY `idx_audit_recorded_at` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_payment_log`
--

LOCK TABLES `audit_payment_log` WRITE;
/*!40000 ALTER TABLE `audit_payment_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_payment_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entry_exit_logs`
--

DROP TABLE IF EXISTS `entry_exit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entry_exit_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `reservation_id` int DEFAULT NULL,
  `vehicle_id` int NOT NULL,
  `slot_id` int NOT NULL,
  `time_in` datetime NOT NULL,
  `time_out` datetime DEFAULT NULL,
  `total_duration` decimal(10,2) DEFAULT NULL,
  `parking_fee` decimal(10,2) DEFAULT NULL,
  `log_status` enum('in','out','denied') NOT NULL DEFAULT 'in',
  PRIMARY KEY (`log_id`),
  KEY `idx_logs_vehicle_id` (`vehicle_id`),
  KEY `idx_logs_slot_id` (`slot_id`),
  KEY `idx_logs_reservation_id` (`reservation_id`),
  KEY `idx_logs_log_status` (`log_status`),
  KEY `idx_logs_time_in` (`time_in`),
  KEY `idx_logs_time_out` (`time_out`),
  KEY `idx_logs_status_time_in` (`log_status`,`time_in`),
  CONSTRAINT `entry_exit_logs_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`reservation_id`) ON DELETE SET NULL,
  CONSTRAINT `entry_exit_logs_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicle` (`vehicle_id`) ON DELETE CASCADE,
  CONSTRAINT `entry_exit_logs_ibfk_3` FOREIGN KEY (`slot_id`) REFERENCES `parking_slots` (`slot_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entry_exit_logs`
--

LOCK TABLES `entry_exit_logs` WRITE;
/*!40000 ALTER TABLE `entry_exit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `entry_exit_logs` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_validate_parking_fee` BEFORE INSERT ON `entry_exit_logs` FOR EACH ROW BEGIN
    IF NEW.parking_fee IS NOT NULL AND NEW.parking_fee < 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Parking fee cannot be negative';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_update_slot_on_entry` AFTER INSERT ON `entry_exit_logs` FOR EACH ROW BEGIN
    IF NEW.log_status = 'in' THEN
        UPDATE parking_slots
        SET status = 'occupied'
        WHERE slot_id = NEW.slot_id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_log_entry` AFTER INSERT ON `entry_exit_logs` FOR EACH ROW BEGIN
    IF NEW.log_status = 'in' THEN
        INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
        VALUES (
            NULL,
            'VEHICLE_ENTERED',
            'entry_exit_logs',
            NEW.log_id,
            NULL,
            JSON_OBJECT(
                'vehicle_id',     NEW.vehicle_id,
                'slot_id',        NEW.slot_id,
                'time_in',        NEW.time_in,
                'reservation_id', NEW.reservation_id
            )
        );
    ELSEIF NEW.log_status = 'denied' THEN
        INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
        VALUES (
            NULL,
            'VEHICLE_DENIED',
            'entry_exit_logs',
            NEW.log_id,
            NULL,
            JSON_OBJECT(
                'vehicle_id', NEW.vehicle_id,
                'slot_id',    NEW.slot_id,
                'time_in',    NEW.time_in
            )
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_calculate_parking_fee` BEFORE UPDATE ON `entry_exit_logs` FOR EACH ROW BEGIN
    IF NEW.log_status = 'out' AND OLD.log_status = 'in' THEN
        -- Duration in hours (decimal)
        SET NEW.total_duration = TIMESTAMPDIFF(MINUTE, OLD.time_in, NEW.time_out) / 60.0;
        -- Fee: ₱50 per hour, rounded to 2 decimal places
        SET NEW.parking_fee    = ROUND(NEW.total_duration * 50, 2);
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_update_slot_on_exit` AFTER UPDATE ON `entry_exit_logs` FOR EACH ROW BEGIN
    IF NEW.log_status = 'out' AND OLD.log_status = 'in' THEN
        UPDATE parking_slots
        SET status = 'available'
        WHERE slot_id = NEW.slot_id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_log_exit` AFTER UPDATE ON `entry_exit_logs` FOR EACH ROW BEGIN
    IF OLD.log_status = 'in' AND NEW.log_status = 'out' THEN
        INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
        VALUES (
            NULL,
            'VEHICLE_EXITED',
            'entry_exit_logs',
            NEW.log_id,
            JSON_OBJECT(
                'log_status', OLD.log_status,
                'time_out',   NULL
            ),
            JSON_OBJECT(
                'log_status',     NEW.log_status,
                'time_out',       NEW.time_out,
                'total_duration', NEW.total_duration,
                'parking_fee',    NEW.parking_fee
            )
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `parking_slots`
--

DROP TABLE IF EXISTS `parking_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parking_slots` (
  `slot_id` int NOT NULL AUTO_INCREMENT,
  `slot_number` varchar(10) NOT NULL DEFAULT '',
  `status` enum('available','occupied','reserved','maintenance') NOT NULL DEFAULT 'available',
  `location_area` varchar(50) NOT NULL,
  PRIMARY KEY (`slot_id`),
  UNIQUE KEY `slot_number` (`slot_number`),
  KEY `idx_slots_status` (`status`),
  KEY `idx_slots_location_area` (`location_area`),
  KEY `idx_slots_area_status` (`location_area`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `parking_slots`
--

LOCK TABLES `parking_slots` WRITE;
/*!40000 ALTER TABLE `parking_slots` DISABLE KEYS */;
INSERT INTO `parking_slots` VALUES (4,'A-01','available','A'),(5,'A-02','available','A'),(6,'A-03','reserved','A'),(7,'A-04','maintenance','A'),(8,'A-05','reserved','A'),(9,'A-06','available','A'),(10,'A-07','available','A'),(11,'A-08','available','A'),(12,'A-09','available','A'),(13,'A-10','available','A'),(14,'B-01','available','B'),(15,'B-02','available','B'),(16,'B-03','available','B'),(17,'B-04','reserved','B'),(18,'B-05','available','B'),(19,'B-06','available','B'),(20,'B-07','available','B'),(21,'B-08','available','B'),(22,'B-09','available','B'),(23,'B-10','available','B'),(24,'C-01','available','C'),(25,'C-02','available','C'),(26,'C-03','available','C'),(27,'C-04','available','C'),(28,'C-05','available','C'),(29,'C-06','available','C'),(30,'C-07','available','C'),(31,'C-08','available','C'),(32,'C-09','available','C'),(33,'C-10','available','C'),(34,'A-11','available','A');
/*!40000 ALTER TABLE `parking_slots` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_slot_status_change` AFTER UPDATE ON `parking_slots` FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
        VALUES (
            NULL,
            CONCAT('SLOT_', UPPER(NEW.status)),   -- SLOT_OCCUPIED, SLOT_AVAILABLE, etc.
            'parking_slots',
            NEW.slot_id,
            JSON_OBJECT('status', OLD.status, 'slot_number', OLD.slot_number),
            JSON_OBJECT('status', NEW.status, 'slot_number', NEW.slot_number)
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `log_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `method` varchar(50) NOT NULL,
  `receipt_number` varchar(100) NOT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `idx_payments_log_id` (`log_id`),
  KEY `idx_payments_payment_date` (`payment_date`),
  KEY `idx_payments_method` (`method`),
  KEY `idx_payments_date_method` (`payment_date`,`method`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`log_id`) REFERENCES `entry_exit_logs` (`log_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_log_payment_creation` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    INSERT INTO audit_payment_log (log_id, payment_id, amount, method, recorded_at)
    VALUES (NEW.log_id, NEW.payment_id, NEW.amount, NEW.method, NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_payment_insert` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
    VALUES (
        NULL,
        'PAYMENT_RECORDED',
        'payments',
        NEW.payment_id,
        NULL,
        JSON_OBJECT(
            'log_id',         NEW.log_id,
            'amount',         NEW.amount,
            'method',         NEW.method,
            'receipt_number', NEW.receipt_number,
            'payment_date',   NEW.payment_date
        )
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservations` (
  `reservation_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `slot_id` int NOT NULL,
  `time_reserved` datetime NOT NULL,
  `reservation_expiry` datetime NOT NULL,
  `status` enum('active','expired','cancelled','completed') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`reservation_id`),
  KEY `idx_reservations_user_id` (`user_id`),
  KEY `idx_reservations_slot_id` (`slot_id`),
  KEY `idx_reservations_status` (`status`),
  KEY `idx_reservations_time_reserved` (`time_reserved`),
  KEY `idx_reservations_expiry` (`reservation_expiry`),
  KEY `idx_reservations_status_expiry` (`status`,`reservation_expiry`),
  CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`slot_id`) REFERENCES `parking_slots` (`slot_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservations`
--

LOCK TABLES `reservations` WRITE;
/*!40000 ALTER TABLE `reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `reservations` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_check_slot_availability_on_reservation` BEFORE INSERT ON `reservations` FOR EACH ROW BEGIN
    DECLARE slot_status VARCHAR(20);
    SELECT status INTO slot_status
    FROM parking_slots
    WHERE slot_id = NEW.slot_id;

    IF slot_status != 'available' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Selected parking slot is not available for reservation';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_update_slot_on_reservation` AFTER INSERT ON `reservations` FOR EACH ROW BEGIN
    UPDATE parking_slots
    SET status = 'reserved'
    WHERE slot_id = NEW.slot_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_reservation_insert` AFTER INSERT ON `reservations` FOR EACH ROW BEGIN
    INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
    VALUES (
        NEW.user_id,
        'RESERVATION_CREATED',
        'reservations',
        NEW.reservation_id,
        NULL,
        JSON_OBJECT(
            'slot_id',            NEW.slot_id,
            'time_reserved',      NEW.time_reserved,
            'reservation_expiry', NEW.reservation_expiry,
            'status',             NEW.status
        )
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_release_slot_on_cancelled_reservation` AFTER UPDATE ON `reservations` FOR EACH ROW BEGIN
    IF (NEW.status = 'cancelled' OR NEW.status = 'expired')
       AND OLD.status NOT IN ('cancelled', 'expired') THEN
        UPDATE parking_slots
        SET status = 'available'
        WHERE slot_id = NEW.slot_id
          AND status  = 'reserved';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_reservation_update` AFTER UPDATE ON `reservations` FOR EACH ROW BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
        VALUES (
            NEW.user_id,
            CONCAT('RESERVATION_', UPPER(NEW.status)),  -- RESERVATION_CANCELLED, etc.
            'reservations',
            NEW.reservation_id,
            JSON_OBJECT('status', OLD.status),
            JSON_OBJECT('status', NEW.status)
        );
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `role` enum('customer','staff','admin','guard') NOT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `user_code` varchar(20) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `birthdate` date DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `user_code` (`user_code`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`),
  KEY `idx_users_last_login` (`last_login`),
  KEY `idx_users_created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Izaac','Newton','izaacnewton@gmail.com','09985259075','customer',NULL,'$2y$12$dBd/QRkvDCJLsmWshxVpxu/f40qIFapNh4JRqe0W3rDhR5Rvc8rXe','active','CUS-2026-0001','2026-04-30 20:59:39','2026-03-19 20:35:11',NULL,NULL,NULL),(2,'Albert','Einstein','alberteinstein@gmail.com','09630726210','admin',NULL,'$2y$12$QPCT3bEA92YFLQ0MTVy8jeqUNAel.fauQUbaKznPPuzF7QsJ.n72u','active','ADM-2026-0001','2026-05-06 19:51:53','2026-03-19 20:35:57',NULL,NULL,NULL),(3,'Marie','Curie','mariecurie@gmail.com','09087726210','guard',NULL,'$2y$12$vvCz1GD.jQsOCUhJOUeW8.iflW/Op59njP7HN11DD.um8ekdXr/pa','active','GRD-2026-0001',NULL,'2026-04-27 21:55:53',NULL,NULL,NULL),(4,'Michael','Faraday','michaelfaraday@gmail.com','09087733409','customer',NULL,'$2y$12$IsaQQwlj1OVGl6ttQep.LuwyJ.1gunVH5qGbpoPzN9VLNvpZh2lmi','active','CUS-2026-0002',NULL,'2026-05-05 20:14:48',NULL,NULL,NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_validate_email_format` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Invalid email format';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_validate_contact_number` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NOT (NEW.contact_number REGEXP '^[0-9]{10,20}$'
         OR NEW.contact_number REGEXP '^\+[0-9]{10,20}$') THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Invalid contact number format. Use 10-20 digits or +country code format';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_generate_user_code` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
    IF NEW.user_code IS NULL THEN
        SET NEW.user_code = CONCAT('USR', DATE_FORMAT(NOW(), '%Y%m%d'), LPAD(FLOOR(RAND() * 10000), 4, '0'));
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_user_insert` AFTER INSERT ON `users` FOR EACH ROW BEGIN
    INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
    VALUES (
        NEW.user_id,
        'USER_REGISTERED',
        'users',
        NEW.user_id,
        NULL,
        JSON_OBJECT(
            'firstname',      NEW.firstname,
            'lastname',       NEW.lastname,
            'email',          NEW.email,
            'role',           NEW.role,
            'status',         NEW.status,
            'user_code',      NEW.user_code
        )
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_update_last_login` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.last_login IS NOT NULL AND (
        OLD.last_login IS NULL OR NEW.last_login != OLD.last_login
    ) THEN
        SET NEW.last_login = NOW();
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_user_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    DECLARE v_action VARCHAR(100);

    -- Determine the most significant change for the action label
    IF OLD.status != NEW.status THEN
        SET v_action = CONCAT('USER_', UPPER(NEW.status));   -- USER_SUSPENDED / USER_ACTIVE
    ELSEIF OLD.role != NEW.role THEN
        SET v_action = 'USER_ROLE_CHANGED';
    ELSEIF OLD.email != NEW.email THEN
        SET v_action = 'USER_EMAIL_CHANGED';
    ELSEIF OLD.password_hash != NEW.password_hash THEN
        SET v_action = 'USER_PASSWORD_CHANGED';
    ELSE
        SET v_action = 'USER_PROFILE_UPDATED';
    END IF;

    INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
    VALUES (
        NEW.user_id,
        v_action,
        'users',
        NEW.user_id,
        JSON_OBJECT(
            'firstname',  OLD.firstname,
            'lastname',   OLD.lastname,
            'email',      OLD.email,
            'role',       OLD.role,
            'status',     OLD.status
        ),
        JSON_OBJECT(
            'firstname',  NEW.firstname,
            'lastname',   NEW.lastname,
            'email',      NEW.email,
            'role',       NEW.role,
            'status',     NEW.status
        )
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_prevent_user_deletion_with_active_reservations` BEFORE DELETE ON `users` FOR EACH ROW BEGIN
    IF EXISTS (
        SELECT 1 FROM reservations
        WHERE user_id = OLD.user_id
          AND status  = 'active'
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Cannot delete user with active reservations';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_user_delete` AFTER DELETE ON `users` FOR EACH ROW BEGIN
    INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
    VALUES (
        NULL,
        'USER_DELETED',
        'users',
        OLD.user_id,
        JSON_OBJECT(
            'firstname',  OLD.firstname,
            'lastname',   OLD.lastname,
            'email',      OLD.email,
            'role',       OLD.role,
            'user_code',  OLD.user_code
        ),
        NULL
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `vehicle`
--

DROP TABLE IF EXISTS `vehicle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vehicle` (
  `vehicle_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  PRIMARY KEY (`vehicle_id`),
  UNIQUE KEY `idx_vehicle_user_plate` (`user_id`,`plate_number`),
  KEY `idx_vehicle_user_id` (`user_id`),
  KEY `idx_vehicle_plate_number` (`plate_number`),
  KEY `idx_vehicle_type` (`vehicle_type`),
  CONSTRAINT `vehicle_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vehicle`
--

LOCK TABLES `vehicle` WRITE;
/*!40000 ALTER TABLE `vehicle` DISABLE KEYS */;
INSERT INTO `vehicle` VALUES (1,2,'LMN 433','car'),(2,4,'ABC 241','motorcycle');
/*!40000 ALTER TABLE `vehicle` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_prevent_duplicate_vehicles` BEFORE INSERT ON `vehicle` FOR EACH ROW BEGIN
    IF EXISTS (
        SELECT 1 FROM vehicle
        WHERE user_id      = NEW.user_id
          AND plate_number = NEW.plate_number
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: This vehicle is already registered for this user';
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_vehicle_insert` AFTER INSERT ON `vehicle` FOR EACH ROW BEGIN
    INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
    VALUES (
        NEW.user_id,
        'VEHICLE_REGISTERED',
        'vehicle',
        NEW.vehicle_id,
        NULL,
        JSON_OBJECT(
            'plate_number', NEW.plate_number,
            'vehicle_type', NEW.vehicle_type,
            'user_id',      NEW.user_id
        )
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_audit_vehicle_delete` AFTER DELETE ON `vehicle` FOR EACH ROW BEGIN
    INSERT INTO system_audit_log (user_id, action, target_table, target_id, old_value, new_value)
    VALUES (
        OLD.user_id,
        'VEHICLE_REMOVED',
        'vehicle',
        OLD.vehicle_id,
        JSON_OBJECT(
            'plate_number', OLD.plate_number,
            'vehicle_type', OLD.vehicle_type,
            'user_id',      OLD.user_id
        ),
        NULL
    );
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Temporary view structure for view `view_all_slots_status`
--

DROP TABLE IF EXISTS `view_all_slots_status`;
/*!50001 DROP VIEW IF EXISTS `view_all_slots_status`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_all_slots_status` AS SELECT 
 1 AS `slot_id`,
 1 AS `slot_number`,
 1 AS `location_area`,
 1 AS `status`,
 1 AS `plate_number`,
 1 AS `occupant_name`,
 1 AS `time_in`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_current_month_summary`
--

DROP TABLE IF EXISTS `view_current_month_summary`;
/*!50001 DROP VIEW IF EXISTS `view_current_month_summary`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_current_month_summary` AS SELECT 
 1 AS `total_entries`,
 1 AS `revenue`,
 1 AS `avg_duration`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_current_month_top_vehicles`
--

DROP TABLE IF EXISTS `view_current_month_top_vehicles`;
/*!50001 DROP VIEW IF EXISTS `view_current_month_top_vehicles`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_current_month_top_vehicles` AS SELECT 
 1 AS `plate_number`,
 1 AS `owner`,
 1 AS `vehicle_type`,
 1 AS `total_entries`,
 1 AS `total_hours`,
 1 AS `total_fee`,
 1 AS `avg_duration`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_daily_revenue`
--

DROP TABLE IF EXISTS `view_daily_revenue`;
/*!50001 DROP VIEW IF EXISTS `view_daily_revenue`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_daily_revenue` AS SELECT 
 1 AS `payment_date`,
 1 AS `total_transactions`,
 1 AS `unique_users`,
 1 AS `total_revenue`,
 1 AS `average_transaction`,
 1 AS `minimum_payment`,
 1 AS `maximum_payment`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_logs_list`
--

DROP TABLE IF EXISTS `view_logs_list`;
/*!50001 DROP VIEW IF EXISTS `view_logs_list`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_logs_list` AS SELECT 
 1 AS `log_id`,
 1 AS `log_status`,
 1 AS `time_in`,
 1 AS `time_out`,
 1 AS `total_duration`,
 1 AS `parking_fee`,
 1 AS `vehicle_id`,
 1 AS `plate_number`,
 1 AS `vehicle_type`,
 1 AS `user_id`,
 1 AS `user_code`,
 1 AS `owner_name`,
 1 AS `contact_number`,
 1 AS `slot_id`,
 1 AS `slot_number`,
 1 AS `location_area`,
 1 AS `payment_id`,
 1 AS `payment_amount`,
 1 AS `payment_date`,
 1 AS `payment_method`,
 1 AS `receipt_number`,
 1 AS `reservation_id`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_logs_today_stats`
--

DROP TABLE IF EXISTS `view_logs_today_stats`;
/*!50001 DROP VIEW IF EXISTS `view_logs_today_stats`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_logs_today_stats` AS SELECT 
 1 AS `today_entries`,
 1 AS `today_exits`,
 1 AS `today_denied`,
 1 AS `today_revenue`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_monthly_parking_stats`
--

DROP TABLE IF EXISTS `view_monthly_parking_stats`;
/*!50001 DROP VIEW IF EXISTS `view_monthly_parking_stats`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_monthly_parking_stats` AS SELECT 
 1 AS `year`,
 1 AS `month`,
 1 AS `total_parking_sessions`,
 1 AS `unique_vehicles`,
 1 AS `unique_users`,
 1 AS `total_parking_fees`,
 1 AS `avg_parking_duration`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_monthly_revenue_trend`
--

DROP TABLE IF EXISTS `view_monthly_revenue_trend`;
/*!50001 DROP VIEW IF EXISTS `view_monthly_revenue_trend`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_monthly_revenue_trend` AS SELECT 
 1 AS `year`,
 1 AS `month`,
 1 AS `month_label`,
 1 AS `yearmonth`,
 1 AS `total_revenue`,
 1 AS `total_transactions`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_reservation_stats_today`
--

DROP TABLE IF EXISTS `view_reservation_stats_today`;
/*!50001 DROP VIEW IF EXISTS `view_reservation_stats_today`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_reservation_stats_today` AS SELECT 
 1 AS `total_today`,
 1 AS `pending`,
 1 AS `approved`,
 1 AS `cancelled`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_reservations_full`
--

DROP TABLE IF EXISTS `view_reservations_full`;
/*!50001 DROP VIEW IF EXISTS `view_reservations_full`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_reservations_full` AS SELECT 
 1 AS `reservation_id`,
 1 AS `ref_number`,
 1 AS `user_id`,
 1 AS `user_code`,
 1 AS `firstname`,
 1 AS `lastname`,
 1 AS `full_name`,
 1 AS `email`,
 1 AS `contact_number`,
 1 AS `user_role`,
 1 AS `profile_picture`,
 1 AS `vehicle_id`,
 1 AS `plate_number`,
 1 AS `vehicle_type`,
 1 AS `slot_id`,
 1 AS `slot_number`,
 1 AS `location_area`,
 1 AS `time_reserved`,
 1 AS `reservation_expiry`,
 1 AS `duration_minutes`,
 1 AS `minutes_until_expiry`,
 1 AS `status`,
 1 AS `date_label`,
 1 AS `time_label`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_slot_availability`
--

DROP TABLE IF EXISTS `view_slot_availability`;
/*!50001 DROP VIEW IF EXISTS `view_slot_availability`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_slot_availability` AS SELECT 
 1 AS `location_area`,
 1 AS `total_slots`,
 1 AS `available_slots`,
 1 AS `occupied_slots`,
 1 AS `reserved_slots`,
 1 AS `availability_percentage`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_staff_activity`
--

DROP TABLE IF EXISTS `view_staff_activity`;
/*!50001 DROP VIEW IF EXISTS `view_staff_activity`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_staff_activity` AS SELECT 
 1 AS `user_id`,
 1 AS `user_code`,
 1 AS `firstname`,
 1 AS `lastname`,
 1 AS `email`,
 1 AS `role`,
 1 AS `status`,
 1 AS `last_login`,
 1 AS `days_since_last_login`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_user_statistics`
--

DROP TABLE IF EXISTS `view_user_statistics`;
/*!50001 DROP VIEW IF EXISTS `view_user_statistics`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_user_statistics` AS SELECT 
 1 AS `user_id`,
 1 AS `user_code`,
 1 AS `firstname`,
 1 AS `lastname`,
 1 AS `email`,
 1 AS `role`,
 1 AS `total_vehicles`,
 1 AS `total_reservations`,
 1 AS `total_parking_sessions`,
 1 AS `total_amount_paid`,
 1 AS `last_parking_date`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_users`
--

DROP TABLE IF EXISTS `view_users`;
/*!50001 DROP VIEW IF EXISTS `view_users`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_users` AS SELECT 
 1 AS `user_id`,
 1 AS `user_code`,
 1 AS `firstname`,
 1 AS `lastname`,
 1 AS `email`,
 1 AS `contact_number`,
 1 AS `role`,
 1 AS `status`,
 1 AS `last_login`,
 1 AS `created_at`,
 1 AS `vehicle_count`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_vehicle_list`
--

DROP TABLE IF EXISTS `view_vehicle_list`;
/*!50001 DROP VIEW IF EXISTS `view_vehicle_list`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_vehicle_list` AS SELECT 
 1 AS `vehicle_id`,
 1 AS `plate_number`,
 1 AS `vehicle_type`,
 1 AS `user_id`,
 1 AS `user_code`,
 1 AS `firstname`,
 1 AS `lastname`,
 1 AS `role`,
 1 AS `parking_status`,
 1 AS `slot_number`,
 1 AS `last_seen`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `view_vehicle_stats`
--

DROP TABLE IF EXISTS `view_vehicle_stats`;
/*!50001 DROP VIEW IF EXISTS `view_vehicle_stats`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `view_vehicle_stats` AS SELECT 
 1 AS `total`,
 1 AS `inside`,
 1 AS `cars`,
 1 AS `motorcycles`*/;
SET character_set_client = @saved_cs_client;

--
-- Dumping events for database 'usep_epark'
--
/*!50106 SET @save_time_zone= @@TIME_ZONE */ ;
/*!50106 DROP EVENT IF EXISTS `evt_cleanup_expired_reservations` */;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `evt_cleanup_expired_reservations` ON SCHEDULE EVERY 1 DAY STARTS '2026-05-08 02:00:00' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM reservations
    WHERE status IN ('expired', 'cancelled')
      AND reservation_expiry < NOW() - INTERVAL 30 DAY */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `evt_expire_reservations` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `evt_expire_reservations` ON SCHEDULE EVERY 1 MINUTE STARTS '2026-05-07 12:44:17' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE reservations
    SET status = 'expired'
    WHERE status = 'active'
      AND NOW() > reservation_expiry */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `evt_log_daily_summary` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `evt_log_daily_summary` ON SCHEDULE EVERY 1 DAY STARTS '2026-05-08 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO INSERT INTO system_daily_logs (
        log_date,
        total_entries,
        total_exits,
        total_denied,
        unique_vehicles,
        unique_users,
        total_revenue,
        avg_duration_hours,
        available_slots,
        occupied_slots,
        reserved_slots
    )
    SELECT
        CURDATE() - INTERVAL 1 DAY,
        COUNT(eel.log_id),
        SUM(CASE WHEN eel.log_status = 'out'    THEN 1 ELSE 0 END),
        SUM(CASE WHEN eel.log_status = 'denied' THEN 1 ELSE 0 END),
        COUNT(DISTINCT eel.vehicle_id),
        COUNT(DISTINCT v.user_id),
        COALESCE(SUM(eel.parking_fee), 0),
        COALESCE(AVG(eel.total_duration), 0),
        SUM(CASE WHEN ps.status = 'available'  THEN 1 ELSE 0 END),
        SUM(CASE WHEN ps.status = 'occupied'   THEN 1 ELSE 0 END),
        SUM(CASE WHEN ps.status = 'reserved'   THEN 1 ELSE 0 END)
    FROM entry_exit_logs eel
    JOIN vehicle       v   ON eel.vehicle_id = v.vehicle_id
    CROSS JOIN (SELECT COUNT(*) AS cnt FROM parking_slots) total
    LEFT JOIN parking_slots ps ON 1 = 1
    WHERE DATE(eel.time_in) = CURDATE() - INTERVAL 1 DAY
    GROUP BY DATE(eel.time_in) */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
/*!50106 DROP EVENT IF EXISTS `evt_reset_stale_occupied_slots` */;;
DELIMITER ;;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;;
/*!50003 SET character_set_client  = utf8mb4 */ ;;
/*!50003 SET character_set_results = utf8mb4 */ ;;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;;
/*!50003 SET @saved_time_zone      = @@time_zone */ ;;
/*!50003 SET time_zone             = 'SYSTEM' */ ;;
/*!50106 CREATE*/ /*!50117 DEFINER=`root`@`localhost`*/ /*!50106 EVENT `evt_reset_stale_occupied_slots` ON SCHEDULE EVERY 1 HOUR STARTS '2026-05-07 12:45:00' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE parking_slots
    SET status = 'available'
    WHERE status = 'occupied'
      AND slot_id NOT IN (
          SELECT DISTINCT slot_id
          FROM entry_exit_logs
          WHERE log_status = 'in'
      ) */ ;;
/*!50003 SET time_zone             = @saved_time_zone */ ;;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;;
/*!50003 SET character_set_client  = @saved_cs_client */ ;;
/*!50003 SET character_set_results = @saved_cs_results */ ;;
/*!50003 SET collation_connection  = @saved_col_connection */ ;;
DELIMITER ;
/*!50106 SET TIME_ZONE= @save_time_zone */ ;

--
-- Dumping routines for database 'usep_epark'
--
/*!50003 DROP FUNCTION IF EXISTS `fn_is_slot_available` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_is_slot_available`(
    p_slot_id INT
) RETURNS tinyint(1)
    READS SQL DATA
BEGIN
    DECLARE v_status VARCHAR(20);

    SELECT status INTO v_status
    FROM   parking_slots
    WHERE  slot_id = p_slot_id;

    RETURN IF(v_status = 'available', 1, 0);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `fn_user_has_active_reservation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_user_has_active_reservation`(
    p_user_id INT
) RETURNS tinyint(1)
    READS SQL DATA
BEGIN
    DECLARE v_count INT DEFAULT 0;

    SELECT COUNT(*) INTO v_count
    FROM   reservations
    WHERE  user_id = p_user_id
      AND  status  = 'active';

    RETURN IF(v_count > 0, 1, 0);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_cancel_reservation` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_cancel_reservation`(
    IN  p_reservation_id INT,
    IN  p_user_id        INT,
    OUT p_message        VARCHAR(255)
)
BEGIN
    DECLARE v_status  VARCHAR(20);
    DECLARE v_user_id INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: Cancellation failed.';
    END;

    SELECT status, user_id
    INTO   v_status, v_user_id
    FROM   reservations
    WHERE  reservation_id = p_reservation_id;

    IF v_status IS NULL THEN
        SET p_message = 'ERROR: Reservation not found.';

    ELSEIF v_user_id != p_user_id THEN
        SET p_message = 'ERROR: You are not authorized to cancel this reservation.';

    ELSEIF v_status != 'active' THEN
        SET p_message = CONCAT('ERROR: Reservation cannot be cancelled. Current status: ', v_status, '.');

    ELSE
        START TRANSACTION;
        UPDATE reservations
        SET    status = 'cancelled'
        WHERE  reservation_id = p_reservation_id;
        COMMIT;
        -- Slot is released by trg_release_slot_on_cancelled_reservation
        SET p_message = 'SUCCESS: Reservation cancelled successfully.';
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_delete_user` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_delete_user`(
    IN  p_user_id INT,
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: User deletion failed.';
    END;

    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
        SET p_message = 'ERROR: User not found.';

    ELSEIF fn_user_has_active_reservation(p_user_id) = 1 THEN
        SET p_message = 'ERROR: Cannot delete user with active reservations.';

    ELSE
        START TRANSACTION;
        DELETE FROM users WHERE user_id = p_user_id;
        COMMIT;
        SET p_message = 'SUCCESS: User deleted successfully.';
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_get_daily_report` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_daily_report`(
    IN p_date_from DATE,
    IN p_date_to   DATE
)
BEGIN
    SELECT
        DATE(eel.time_in)               AS report_date,
        COUNT(eel.log_id)               AS total_entries,
        SUM(CASE WHEN eel.log_status = 'out'    THEN 1 ELSE 0 END) AS total_exits,
        SUM(CASE WHEN eel.log_status = 'denied' THEN 1 ELSE 0 END) AS total_denied,
        COUNT(DISTINCT eel.vehicle_id)  AS unique_vehicles,
        COUNT(DISTINCT v.user_id)       AS unique_users,
        COALESCE(SUM(eel.parking_fee),  0) AS total_fees_collected,
        COALESCE(AVG(eel.total_duration), 0) AS avg_duration_hours
    FROM entry_exit_logs eel
    JOIN vehicle v ON eel.vehicle_id = v.vehicle_id
    WHERE DATE(eel.time_in) BETWEEN p_date_from AND p_date_to
    GROUP BY DATE(eel.time_in)
    ORDER BY report_date ASC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_get_revenue_summary` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_revenue_summary`(
    IN p_date_from DATE,
    IN p_date_to   DATE
)
BEGIN
    SELECT
        COUNT(DISTINCT p.payment_id)                AS total_transactions,
        COUNT(DISTINCT eel.log_id)                  AS total_sessions,
        COUNT(DISTINCT v.user_id)                   AS unique_users,
        COALESCE(SUM(p.amount),         0)          AS total_revenue,
        COALESCE(AVG(p.amount),         0)          AS avg_payment,
        COALESCE(AVG(eel.total_duration), 0)        AS avg_duration_hours,
        MIN(p.payment_date)                         AS first_transaction,
        MAX(p.payment_date)                         AS last_transaction
    FROM payments p
    JOIN entry_exit_logs eel ON p.log_id      = eel.log_id
    JOIN vehicle         v   ON eel.vehicle_id = v.vehicle_id
    WHERE DATE(p.payment_date) BETWEEN p_date_from AND p_date_to;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_get_vehicle_activity_report` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_vehicle_activity_report`(
    IN p_date_from DATE,
    IN p_date_to   DATE
)
BEGIN
    -- ────────────────────────────────────────────────────────────────────
    -- Validate date inputs
    -- ────────────────────────────────────────────────────────────────────
    IF p_date_from IS NULL OR p_date_to IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: Date parameters cannot be NULL';
    END IF;
    
    IF p_date_from > p_date_to THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'ERROR: date_from must be before or equal to date_to';
    END IF;
    
    -- ────────────────────────────────────────────────────────────────────
    -- Main Query: Vehicle Activity Report
    -- ────────────────────────────────────────────────────────────────────
    SELECT
        -- Vehicle & Owner Info
        v.vehicle_id,
        v.plate_number,
        v.vehicle_type,
        
        -- User Info
        u.user_id,
        u.user_code,
        CONCAT(u.firstname, ' ', u.lastname) AS owner_name,
        u.email,
        u.contact_number,
        u.role AS user_role,
        
        -- Parking Activity
        COUNT(eel.log_id)                           AS total_entries,
        SUM(CASE WHEN eel.log_status = 'out' THEN 1 ELSE 0 END)     AS total_exits,
        SUM(CASE WHEN eel.log_status = 'in' THEN 1 ELSE 0 END)      AS currently_inside,
        
        -- Duration Metrics
        COALESCE(SUM(eel.total_duration), 0)        AS total_hours,
        COALESCE(AVG(eel.total_duration), 0)        AS avg_duration_hours,
        COALESCE(MIN(eel.total_duration), 0)        AS min_duration_hours,
        COALESCE(MAX(eel.total_duration), 0)        AS max_duration_hours,
        
        -- Financial Metrics
        COALESCE(SUM(eel.parking_fee), 0)           AS total_fees_paid,
        COALESCE(AVG(eel.parking_fee), 0)           AS avg_fee_per_session,
        COALESCE(MIN(eel.parking_fee), 0)           AS min_fee_per_session,
        COALESCE(MAX(eel.parking_fee), 0)           AS max_fee_per_session,
        
        -- Reservation Stats
        COUNT(DISTINCT r.reservation_id)            AS total_reservations,
        SUM(CASE WHEN r.status = 'active' THEN 1 ELSE 0 END)       AS active_reservations,
        SUM(CASE WHEN r.status = 'completed' THEN 1 ELSE 0 END)    AS completed_reservations,
        SUM(CASE WHEN r.status = 'cancelled' THEN 1 ELSE 0 END)    AS cancelled_reservations,
        
        -- Temporal Info
        MIN(eel.time_in)                            AS first_entry,
        MAX(eel.time_in)                            AS last_entry,
        MAX(eel.time_out)                           AS last_exit,
        
        -- Days active
        COUNT(DISTINCT DATE(eel.time_in))          AS days_active
        
    FROM vehicle v
    JOIN users u ON v.user_id = u.user_id
    LEFT JOIN entry_exit_logs eel 
        ON eel.vehicle_id = v.vehicle_id 
        AND DATE(eel.time_in) BETWEEN p_date_from AND p_date_to
    LEFT JOIN reservations r 
        ON r.user_id = v.user_id 
        AND DATE(r.time_reserved) BETWEEN p_date_from AND p_date_to
    
    GROUP BY 
        v.vehicle_id, 
        v.plate_number, 
        v.vehicle_type,
        u.user_id,
        u.user_code,
        u.firstname,
        u.lastname,
        u.email,
        u.contact_number,
        u.role
    
    ORDER BY 
        total_entries DESC,
        total_fees_paid DESC;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_register_user` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_register_user`(
    IN  p_firstname       VARCHAR(50),
    IN  p_lastname        VARCHAR(50),
    IN  p_email           VARCHAR(100),
    IN  p_contact_number  VARCHAR(20),
    IN  p_role            ENUM('customer','staff','admin'),
    IN  p_password_hash   VARCHAR(255),
    IN  p_birthdate       DATE,
    IN  p_gender          VARCHAR(50),
    OUT p_user_id         INT,
    OUT p_message         VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_user_id = NULL;
        SET p_message = 'ERROR: Registration failed due to a database error.';
    END;

    -- Check for duplicate email
    IF EXISTS (SELECT 1 FROM users WHERE email = p_email) THEN
        SET p_user_id = NULL;
        SET p_message = 'ERROR: Email address is already registered.';
    ELSE
        START TRANSACTION;

        INSERT INTO users (
            firstname, lastname, email, contact_number,
            role, password_hash, birthdate, gender
        ) VALUES (
            p_firstname, p_lastname, p_email, p_contact_number,
            p_role, p_password_hash, p_birthdate, p_gender
        );

        SET p_user_id = LAST_INSERT_ID();
        SET p_message = 'SUCCESS: User registered successfully.';

        COMMIT;
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_register_vehicle` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_register_vehicle`(
    IN  p_user_id      INT,
    IN  p_plate_number VARCHAR(20),
    IN  p_vehicle_type VARCHAR(50),
    OUT p_vehicle_id   INT,
    OUT p_message      VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_vehicle_id = NULL;
        SET p_message    = 'ERROR: Vehicle registration failed.';
    END;

    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
        SET p_vehicle_id = NULL;
        SET p_message    = 'ERROR: User not found.';

    ELSEIF EXISTS (
        SELECT 1 FROM vehicle
        WHERE user_id = p_user_id AND plate_number = p_plate_number
    ) THEN
        SET p_vehicle_id = NULL;
        SET p_message    = 'ERROR: This plate number is already registered for this user.';

    ELSE
        START TRANSACTION;
        INSERT INTO vehicle (user_id, plate_number, vehicle_type)
        VALUES (p_user_id, p_plate_number, p_vehicle_type);
        SET p_vehicle_id = LAST_INSERT_ID();
        COMMIT;
        SET p_message = 'SUCCESS: Vehicle registered successfully.';
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_remove_vehicle` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_remove_vehicle`(
    IN  p_vehicle_id INT,
    OUT p_message    VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: Vehicle removal failed.';
    END;

    IF NOT EXISTS (SELECT 1 FROM vehicle WHERE vehicle_id = p_vehicle_id) THEN
        SET p_message = 'ERROR: Vehicle not found.';

    ELSEIF EXISTS (
        SELECT 1 FROM entry_exit_logs
        WHERE vehicle_id = p_vehicle_id AND log_status = 'in'
    ) THEN
        SET p_message = 'ERROR: Cannot remove a vehicle that is currently parked.';

    ELSE
        START TRANSACTION;
        DELETE FROM vehicle WHERE vehicle_id = p_vehicle_id;
        COMMIT;
        SET p_message = 'SUCCESS: Vehicle removed successfully.';
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_update_user_full` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_user_full`(
    IN  p_user_id       INT,
    IN  p_firstname     VARCHAR(50),
    IN  p_lastname      VARCHAR(50),
    IN  p_email         VARCHAR(100),
    IN  p_contact       VARCHAR(20),
    IN  p_role          ENUM('customer','staff','admin'),
    IN  p_password_hash VARCHAR(255),   -- pass NULL to keep existing password
    OUT p_message       VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: Update failed due to a database error.';
    END;

    -- User must exist
    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
        SET p_message = 'ERROR: User not found.';

    -- Email must not be taken by another user
    ELSEIF EXISTS (
        SELECT 1 FROM users
        WHERE email = p_email AND user_id != p_user_id
    ) THEN
        SET p_message = 'ERROR: Email address is already in use by another account.';

    ELSE
        START TRANSACTION;

        IF p_password_hash IS NOT NULL THEN
            UPDATE users
            SET
                firstname      = p_firstname,
                lastname       = p_lastname,
                email          = p_email,
                contact_number = p_contact,
                role           = p_role,
                password_hash  = p_password_hash
            WHERE user_id = p_user_id;
        ELSE
            UPDATE users
            SET
                firstname      = p_firstname,
                lastname       = p_lastname,
                email          = p_email,
                contact_number = p_contact,
                role           = p_role
            WHERE user_id = p_user_id;
        END IF;

        COMMIT;
        SET p_message = 'SUCCESS: User updated successfully.';
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_update_user_status` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_0900_ai_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_user_status`(
    IN  p_user_id  INT,
    IN  p_status   ENUM('active','suspended'),
    OUT p_message  VARCHAR(255)
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_message = 'ERROR: Status update failed.';
    END;

    IF NOT EXISTS (SELECT 1 FROM users WHERE user_id = p_user_id) THEN
        SET p_message = 'ERROR: User not found.';

    ELSEIF p_status = 'suspended' AND fn_user_has_active_reservation(p_user_id) = 1 THEN
        SET p_message = 'ERROR: Cannot suspend user with active reservations.';

    ELSE
        START TRANSACTION;
        UPDATE users SET status = p_status WHERE user_id = p_user_id;
        COMMIT;
        SET p_message = CONCAT('SUCCESS: User status updated to ', p_status, '.');
    END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `view_all_slots_status`
--

/*!50001 DROP VIEW IF EXISTS `view_all_slots_status`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_all_slots_status` AS select `s`.`slot_id` AS `slot_id`,`s`.`slot_number` AS `slot_number`,`s`.`location_area` AS `location_area`,`s`.`status` AS `status`,`v`.`plate_number` AS `plate_number`,concat(`u`.`firstname`,' ',`u`.`lastname`) AS `occupant_name`,`e`.`time_in` AS `time_in` from (((`parking_slots` `s` left join `entry_exit_logs` `e` on(((`e`.`slot_id` = `s`.`slot_id`) and (`e`.`time_out` is null)))) left join `vehicle` `v` on((`v`.`vehicle_id` = `e`.`vehicle_id`))) left join `users` `u` on((`u`.`user_id` = `v`.`user_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_current_month_summary`
--

/*!50001 DROP VIEW IF EXISTS `view_current_month_summary`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_current_month_summary` AS select count(0) AS `total_entries`,coalesce(sum(`entry_exit_logs`.`parking_fee`),0) AS `revenue`,coalesce(avg(`entry_exit_logs`.`total_duration`),0) AS `avg_duration` from `entry_exit_logs` where ((`entry_exit_logs`.`log_status` = 'out') and (year(`entry_exit_logs`.`time_in`) = year(curdate())) and (month(`entry_exit_logs`.`time_in`) = month(curdate()))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_current_month_top_vehicles`
--

/*!50001 DROP VIEW IF EXISTS `view_current_month_top_vehicles`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_current_month_top_vehicles` AS select `v`.`plate_number` AS `plate_number`,concat(`u`.`firstname`,' ',`u`.`lastname`) AS `owner`,`v`.`vehicle_type` AS `vehicle_type`,count(`eel`.`log_id`) AS `total_entries`,coalesce(sum(`eel`.`total_duration`),0) AS `total_hours`,coalesce(sum(`eel`.`parking_fee`),0) AS `total_fee`,coalesce(avg(`eel`.`total_duration`),0) AS `avg_duration` from ((`entry_exit_logs` `eel` join `vehicle` `v` on((`eel`.`vehicle_id` = `v`.`vehicle_id`))) join `users` `u` on((`v`.`user_id` = `u`.`user_id`))) where ((`eel`.`log_status` = 'out') and (year(`eel`.`time_in`) = year(curdate())) and (month(`eel`.`time_in`) = month(curdate()))) group by `v`.`vehicle_id`,`v`.`plate_number`,`u`.`firstname`,`u`.`lastname`,`v`.`vehicle_type` order by `total_entries` desc limit 5 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_daily_revenue`
--

/*!50001 DROP VIEW IF EXISTS `view_daily_revenue`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_daily_revenue` AS select cast(`p`.`payment_date` as date) AS `payment_date`,count(distinct `p`.`payment_id`) AS `total_transactions`,count(distinct `v`.`user_id`) AS `unique_users`,sum(`p`.`amount`) AS `total_revenue`,avg(`p`.`amount`) AS `average_transaction`,min(`p`.`amount`) AS `minimum_payment`,max(`p`.`amount`) AS `maximum_payment` from ((`payments` `p` join `entry_exit_logs` `eel` on((`p`.`log_id` = `eel`.`log_id`))) join `vehicle` `v` on((`eel`.`vehicle_id` = `v`.`vehicle_id`))) group by cast(`p`.`payment_date` as date) order by `payment_date` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_logs_list`
--

/*!50001 DROP VIEW IF EXISTS `view_logs_list`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_logs_list` AS select `eel`.`log_id` AS `log_id`,`eel`.`log_status` AS `log_status`,`eel`.`time_in` AS `time_in`,`eel`.`time_out` AS `time_out`,`eel`.`total_duration` AS `total_duration`,`eel`.`parking_fee` AS `parking_fee`,`v`.`vehicle_id` AS `vehicle_id`,`v`.`plate_number` AS `plate_number`,`v`.`vehicle_type` AS `vehicle_type`,`u`.`user_id` AS `user_id`,`u`.`user_code` AS `user_code`,concat(`u`.`firstname`,' ',`u`.`lastname`) AS `owner_name`,`u`.`contact_number` AS `contact_number`,`ps`.`slot_id` AS `slot_id`,`ps`.`slot_number` AS `slot_number`,`ps`.`location_area` AS `location_area`,`p`.`payment_id` AS `payment_id`,`p`.`amount` AS `payment_amount`,`p`.`payment_date` AS `payment_date`,`p`.`method` AS `payment_method`,`p`.`receipt_number` AS `receipt_number`,`eel`.`reservation_id` AS `reservation_id` from ((((`entry_exit_logs` `eel` join `vehicle` `v` on((`eel`.`vehicle_id` = `v`.`vehicle_id`))) join `users` `u` on((`v`.`user_id` = `u`.`user_id`))) join `parking_slots` `ps` on((`eel`.`slot_id` = `ps`.`slot_id`))) left join `payments` `p` on((`eel`.`log_id` = `p`.`log_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_logs_today_stats`
--

/*!50001 DROP VIEW IF EXISTS `view_logs_today_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_logs_today_stats` AS select count((case when (`entry_exit_logs`.`log_status` in ('in','out')) then 1 end)) AS `today_entries`,count((case when (`entry_exit_logs`.`log_status` = 'out') then 1 end)) AS `today_exits`,count((case when (`entry_exit_logs`.`log_status` = 'denied') then 1 end)) AS `today_denied`,coalesce(sum((case when (`entry_exit_logs`.`log_status` = 'out') then `entry_exit_logs`.`parking_fee` else 0 end)),0) AS `today_revenue` from `entry_exit_logs` where (cast(`entry_exit_logs`.`time_in` as date) = curdate()) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_monthly_parking_stats`
--

/*!50001 DROP VIEW IF EXISTS `view_monthly_parking_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_monthly_parking_stats` AS select year(`eel`.`time_in`) AS `year`,month(`eel`.`time_in`) AS `month`,count(distinct `eel`.`log_id`) AS `total_parking_sessions`,count(distinct `eel`.`vehicle_id`) AS `unique_vehicles`,count(distinct `v`.`user_id`) AS `unique_users`,sum(`eel`.`parking_fee`) AS `total_parking_fees`,avg(`eel`.`total_duration`) AS `avg_parking_duration` from (`entry_exit_logs` `eel` join `vehicle` `v` on((`eel`.`vehicle_id` = `v`.`vehicle_id`))) where (`eel`.`log_status` = 'out') group by year(`eel`.`time_in`),month(`eel`.`time_in`) order by `year` desc,`month` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_monthly_revenue_trend`
--

/*!50001 DROP VIEW IF EXISTS `view_monthly_revenue_trend`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_monthly_revenue_trend` AS select year(`p`.`payment_date`) AS `year`,month(`p`.`payment_date`) AS `month`,date_format(`p`.`payment_date`,'%b') AS `month_label`,concat(year(`p`.`payment_date`),'-',convert(lpad(month(`p`.`payment_date`),2,'0') using utf8mb4)) AS `yearmonth`,coalesce(sum(`p`.`amount`),0) AS `total_revenue`,count(distinct `p`.`payment_id`) AS `total_transactions` from `payments` `p` group by year(`p`.`payment_date`),month(`p`.`payment_date`),date_format(`p`.`payment_date`,'%b') order by `year` desc,`month` desc limit 7 */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_reservation_stats_today`
--

/*!50001 DROP VIEW IF EXISTS `view_reservation_stats_today`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_reservation_stats_today` AS select count(0) AS `total_today`,sum((case when (`reservations`.`status` = 'active') then 1 else 0 end)) AS `pending`,sum((case when (`reservations`.`status` = 'completed') then 1 else 0 end)) AS `approved`,sum((case when ((`reservations`.`status` = 'cancelled') or (`reservations`.`status` = 'expired')) then 1 else 0 end)) AS `cancelled` from `reservations` where (cast(`reservations`.`time_reserved` as date) = curdate()) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_reservations_full`
--

/*!50001 DROP VIEW IF EXISTS `view_reservations_full`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_reservations_full` AS select `r`.`reservation_id` AS `reservation_id`,concat('RES-',convert(lpad(`r`.`reservation_id`,3,'0') using utf8mb4)) AS `ref_number`,`r`.`user_id` AS `user_id`,`u`.`user_code` AS `user_code`,`u`.`firstname` AS `firstname`,`u`.`lastname` AS `lastname`,concat(`u`.`firstname`,' ',`u`.`lastname`) AS `full_name`,`u`.`email` AS `email`,`u`.`contact_number` AS `contact_number`,`u`.`role` AS `user_role`,`u`.`profile_picture` AS `profile_picture`,`v`.`vehicle_id` AS `vehicle_id`,`v`.`plate_number` AS `plate_number`,`v`.`vehicle_type` AS `vehicle_type`,`ps`.`slot_id` AS `slot_id`,`ps`.`slot_number` AS `slot_number`,`ps`.`location_area` AS `location_area`,`r`.`time_reserved` AS `time_reserved`,`r`.`reservation_expiry` AS `reservation_expiry`,timestampdiff(MINUTE,`r`.`time_reserved`,`r`.`reservation_expiry`) AS `duration_minutes`,timestampdiff(MINUTE,now(),`r`.`reservation_expiry`) AS `minutes_until_expiry`,`r`.`status` AS `status`,(case when (cast(`r`.`time_reserved` as date) = curdate()) then 'Today' when (cast(`r`.`time_reserved` as date) = (curdate() - interval 1 day)) then 'Yesterday' else convert(date_format(`r`.`time_reserved`,'%b %d, %Y') using utf8mb4) end) AS `date_label`,time_format(`r`.`time_reserved`,'%h:%i %p') AS `time_label` from (((`reservations` `r` join `users` `u` on((`r`.`user_id` = `u`.`user_id`))) join `vehicle` `v` on((`v`.`vehicle_id` = (select `vehicle`.`vehicle_id` from `vehicle` where (`vehicle`.`user_id` = `r`.`user_id`) order by `vehicle`.`vehicle_id` desc limit 1)))) join `parking_slots` `ps` on((`r`.`slot_id` = `ps`.`slot_id`))) order by field(`r`.`status`,'active','completed','expired','cancelled'),`r`.`time_reserved` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_slot_availability`
--

/*!50001 DROP VIEW IF EXISTS `view_slot_availability`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_slot_availability` AS select `parking_slots`.`location_area` AS `location_area`,count(0) AS `total_slots`,sum((case when (`parking_slots`.`status` = 'available') then 1 else 0 end)) AS `available_slots`,sum((case when (`parking_slots`.`status` = 'occupied') then 1 else 0 end)) AS `occupied_slots`,sum((case when (`parking_slots`.`status` = 'reserved') then 1 else 0 end)) AS `reserved_slots`,round(((sum((case when (`parking_slots`.`status` = 'available') then 1 else 0 end)) / count(0)) * 100),2) AS `availability_percentage` from `parking_slots` group by `parking_slots`.`location_area` order by `parking_slots`.`location_area` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_staff_activity`
--

/*!50001 DROP VIEW IF EXISTS `view_staff_activity`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_staff_activity` AS select `users`.`user_id` AS `user_id`,`users`.`user_code` AS `user_code`,`users`.`firstname` AS `firstname`,`users`.`lastname` AS `lastname`,`users`.`email` AS `email`,`users`.`role` AS `role`,`users`.`status` AS `status`,`users`.`last_login` AS `last_login`,(to_days(now()) - to_days(`users`.`last_login`)) AS `days_since_last_login` from `users` where (`users`.`role` in ('staff','admin')) order by `users`.`last_login` desc */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_user_statistics`
--

/*!50001 DROP VIEW IF EXISTS `view_user_statistics`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_user_statistics` AS select `u`.`user_id` AS `user_id`,`u`.`user_code` AS `user_code`,`u`.`firstname` AS `firstname`,`u`.`lastname` AS `lastname`,`u`.`email` AS `email`,`u`.`role` AS `role`,count(distinct `v`.`vehicle_id`) AS `total_vehicles`,count(distinct `r`.`reservation_id`) AS `total_reservations`,count(distinct `eel`.`log_id`) AS `total_parking_sessions`,coalesce(sum(`p`.`amount`),0) AS `total_amount_paid`,max(`eel`.`time_in`) AS `last_parking_date` from ((((`users` `u` left join `vehicle` `v` on((`u`.`user_id` = `v`.`user_id`))) left join `reservations` `r` on((`u`.`user_id` = `r`.`user_id`))) left join `entry_exit_logs` `eel` on((`v`.`vehicle_id` = `eel`.`vehicle_id`))) left join `payments` `p` on((`eel`.`log_id` = `p`.`log_id`))) group by `u`.`user_id`,`u`.`user_code`,`u`.`firstname`,`u`.`lastname`,`u`.`email`,`u`.`role` order by `u`.`user_id` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_users`
--

/*!50001 DROP VIEW IF EXISTS `view_users`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_users` AS select `u`.`user_id` AS `user_id`,`u`.`user_code` AS `user_code`,`u`.`firstname` AS `firstname`,`u`.`lastname` AS `lastname`,`u`.`email` AS `email`,`u`.`contact_number` AS `contact_number`,`u`.`role` AS `role`,`u`.`status` AS `status`,`u`.`last_login` AS `last_login`,`u`.`created_at` AS `created_at`,count(`v`.`vehicle_id`) AS `vehicle_count` from (`users` `u` left join `vehicle` `v` on((`v`.`user_id` = `u`.`user_id`))) group by `u`.`user_id`,`u`.`user_code`,`u`.`firstname`,`u`.`lastname`,`u`.`email`,`u`.`contact_number`,`u`.`role`,`u`.`status`,`u`.`last_login`,`u`.`created_at` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_vehicle_list`
--

/*!50001 DROP VIEW IF EXISTS `view_vehicle_list`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_vehicle_list` AS select `v`.`vehicle_id` AS `vehicle_id`,`v`.`plate_number` AS `plate_number`,`v`.`vehicle_type` AS `vehicle_type`,`u`.`user_id` AS `user_id`,`u`.`user_code` AS `user_code`,`u`.`firstname` AS `firstname`,`u`.`lastname` AS `lastname`,`u`.`role` AS `role`,(case when (`e`.`log_id` is not null) then 'inside' else 'outside' end) AS `parking_status`,`s`.`slot_number` AS `slot_number`,(select max(`entry_exit_logs`.`time_in`) from `entry_exit_logs` where (`entry_exit_logs`.`vehicle_id` = `v`.`vehicle_id`)) AS `last_seen` from (((`vehicle` `v` join `users` `u` on((`u`.`user_id` = `v`.`user_id`))) left join `entry_exit_logs` `e` on(((`e`.`vehicle_id` = `v`.`vehicle_id`) and (`e`.`log_status` = 'in')))) left join `parking_slots` `s` on((`s`.`slot_id` = `e`.`slot_id`))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `view_vehicle_stats`
--

/*!50001 DROP VIEW IF EXISTS `view_vehicle_stats`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `view_vehicle_stats` AS select count(distinct `v`.`vehicle_id`) AS `total`,count(distinct (case when (`e`.`log_status` = 'in') then `e`.`vehicle_id` end)) AS `inside`,sum((case when (`v`.`vehicle_type` = 'car') then 1 else 0 end)) AS `cars`,sum((case when (`v`.`vehicle_type` = 'motorcycle') then 1 else 0 end)) AS `motorcycles` from (`vehicle` `v` left join `entry_exit_logs` `e` on(((`e`.`vehicle_id` = `v`.`vehicle_id`) and (`e`.`log_status` = 'in')))) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-07 13:19:49
