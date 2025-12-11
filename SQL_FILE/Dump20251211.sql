-- MySQL dump 10.13  Distrib 8.0.41, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: librarysystem
-- ------------------------------------------------------
-- Server version	8.0.41

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
-- Table structure for table `book_archive`
--

DROP TABLE IF EXISTS `book_archive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `book_archive` (
  `archive_id` int NOT NULL AUTO_INCREMENT,
  `book_id` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `isbn` varchar(50) DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `archived_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `book_archive`
--

LOCK TABLES `book_archive` WRITE;
/*!40000 ALTER TABLE `book_archive` DISABLE KEYS */;
/*!40000 ALTER TABLE `book_archive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `books`
--

DROP TABLE IF EXISTS `books`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `books` (
  `book_id` int NOT NULL AUTO_INCREMENT,
  `author` varchar(50) NOT NULL,
  `title` varchar(50) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('active','archived') DEFAULT 'active',
  PRIMARY KEY (`book_id`),
  UNIQUE KEY `isbn` (`isbn`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `books`
--

LOCK TABLES `books` WRITE;
/*!40000 ALTER TABLE `books` DISABLE KEYS */;
INSERT INTO `books` VALUES (1,'Clement Abatayo','Java for beginners','1091',16,500.00,'active'),(2,'Jubs','C++ Advance','1231',24,350.00,'archived'),(3,'Bengie Fernandez','Python Advance','3191',32,800.00,'active'),(4,'Abella','Data structure and algorithm ','3891',18,550.00,'archived'),(5,'Denis Ritchie','Javascript fifth edition','2940',38,450.00,'active'),(6,'Denis Ritchie','C Programming','3198',31,250.00,'active'),(7,'William Jone','System Analysis','2318',13,320.00,'active'),(8,'Dominic Verano','Business for beginners','3101',30,500.00,'active'),(10,'John smith','Sample book','4501',50,100.00,'archived'),(15,'James Harold','Business Process','3189',25,500.00,'active');
/*!40000 ALTER TABLE `books` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `borrow`
--

DROP TABLE IF EXISTS `borrow`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `borrow` (
  `borrow_id` int NOT NULL AUTO_INCREMENT,
  `borrow_date` date NOT NULL,
  `return_date` date NOT NULL,
  `book_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('Borrowed','Returned','Overdue') DEFAULT 'Borrowed',
  `return_actual_date` date DEFAULT NULL,
  `semester` varchar(20) NOT NULL DEFAULT '1st Semester',
  PRIMARY KEY (`borrow_id`),
  KEY `book_id` (`book_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `borrow_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`),
  CONSTRAINT `borrow_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `borrow`
--

LOCK TABLES `borrow` WRITE;
/*!40000 ALTER TABLE `borrow` DISABLE KEYS */;
INSERT INTO `borrow` VALUES (1,'2025-12-07','2025-12-09',3,1,'Returned','2025-12-11','1'),(2,'2025-12-14','2025-12-15',15,1,'Returned','2025-12-11','1'),(3,'2025-12-07','2025-12-08',7,2,'Returned','2025-12-11','1'),(4,'2025-12-14','2025-12-16',6,2,'Returned','2025-12-11','1'),(5,'2025-12-07','2025-12-08',7,1,'Borrowed',NULL,'2');
/*!40000 ALTER TABLE `borrow` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penalties`
--

DROP TABLE IF EXISTS `penalties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penalties` (
  `penalty_id` int NOT NULL AUTO_INCREMENT,
  `borrow_id` int NOT NULL,
  `book_id` int NOT NULL,
  `user_id` int NOT NULL,
  `days_late` int NOT NULL DEFAULT '0',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`penalty_id`),
  KEY `borrow_id` (`borrow_id`),
  KEY `book_id` (`book_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `penalties_ibfk_1` FOREIGN KEY (`borrow_id`) REFERENCES `borrow` (`borrow_id`) ON DELETE CASCADE,
  CONSTRAINT `penalties_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  CONSTRAINT `penalties_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penalties`
--

LOCK TABLES `penalties` WRITE;
/*!40000 ALTER TABLE `penalties` DISABLE KEYS */;
INSERT INTO `penalties` VALUES (1,1,3,1,2,10.00,'paid','2025-12-11 02:22:20'),(2,3,7,2,3,15.00,'paid','2025-12-11 02:22:20');
/*!40000 ALTER TABLE `penalties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservation` (
  `reservation_id` int NOT NULL AUTO_INCREMENT,
  `reservation_date` date NOT NULL,
  `pickup_date` date NOT NULL,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `status` enum('Pending','Approved','Cancelled','Completed') NOT NULL DEFAULT 'Pending',
  `semester` varchar(30) NOT NULL DEFAULT '1st Semester',
  PRIMARY KEY (`reservation_id`),
  KEY `user_id` (`user_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservation`
--

LOCK TABLES `reservation` WRITE;
/*!40000 ALTER TABLE `reservation` DISABLE KEYS */;
INSERT INTO `reservation` VALUES (1,'2025-12-11','2025-12-10',1,7,'Approved','1'),(2,'2025-12-14','2025-12-10',1,3,'Approved','2'),(3,'2025-12-11','2025-12-10',1,1,'Approved','1'),(4,'2025-12-11','2025-12-10',2,6,'Approved','1'),(5,'2025-12-12','2025-12-10',2,15,'Approved','1'),(6,'2025-12-12','2025-12-10',1,5,'Approved','2'),(7,'2025-12-07','2025-12-10',2,5,'Approved','1'),(8,'2025-12-11','2025-12-10',2,7,'Approved','2'),(9,'2025-12-14','2025-12-15',1,8,'Pending','1'),(10,'2025-12-14','2025-12-15',2,7,'Pending','2'),(11,'2025-12-15','2025-12-16',2,3,'Pending','1');
/*!40000 ALTER TABLE `reservation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `lastname` char(25) NOT NULL,
  `firstname` char(20) NOT NULL,
  `email` char(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` char(15) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'jekel','caburog','jekel123@email.com','$2y$10$nHZKoLLHevCj9GpLIVwgYuawHmg2siaSkZPAMsRoFMs8M3Ri8gFnC','student'),(2,'Kate','Rielle','kate123@email.com','$2y$10$Lae2ZF3k1Wgya3A9hRsn2eTS/asPYsahfAwr5vNBxdvG.j48mrgRC','teacher'),(3,'jubrielle','caburog','jubrielle@email.com','$2y$10$vLlVRCgRTioQDIGQbwkqfubK2cXFXNQXjaTga0W.Z9UedZmRIrseG','librarian'),(4,'Shenmale','Caburog','shenmale@email.com','$2y$10$.8VwuiXCQVZzSD7NMagvd.h9dLbRlcEQlgY12HxN9UkSrEcoQVigS','staff'),(5,'Mark','Robert','markrobert@email.com','$2y$10$w7Ut8UXMolmOoz9gFh1wbupZu9RN5qAMtnJepj8sX76DSXBlIgW42','staff');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-11 10:40:37
