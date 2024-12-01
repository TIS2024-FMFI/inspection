-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: localhost    Database: defective_products_db
-- ------------------------------------------------------
-- Server version	8.0.40

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
-- Dumping data for table `defective_products`
--

LOCK TABLES `defective_products` WRITE;
/*!40000 ALTER TABLE `defective_products` DISABLE KEYS */;
INSERT INTO `defective_products` VALUES (1,1,'ALERT123','USA','Electrical','Overheating issue',NULL,NULL,'2024-11-25 11:24:01');
/*!40000 ALTER TABLE `defective_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `personalized_lists`
--

LOCK TABLES `personalized_lists` WRITE;
/*!40000 ALTER TABLE `personalized_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `personalized_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Sample Product','1234567890','Description of the product',NULL,'Electronics','USA',NULL,NULL,NULL,NULL,'2024-11-25 11:24:01');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `scanned_products`
--

LOCK TABLES `scanned_products` WRITE;
/*!40000 ALTER TABLE `scanned_products` DISABLE KEYS */;
INSERT INTO `scanned_products` VALUES (1,1,1,'2024-11-25 11:24:01','defective');
/*!40000 ALTER TABLE `scanned_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin_user','admin@example.com','hashed_password','admin','2024-11-25 11:24:01');
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

-- Dump completed on 2024-11-25 12:32:24
