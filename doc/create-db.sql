-- MySQL dump 10.13  Distrib 8.0.32, for Linux (x86_64)
--
-- Host: localhost    Database: piac
-- ------------------------------------------------------
-- Server version	8.0.32-0ubuntu0.22.04.2

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
-- Table structure for table `dbverzio`
--

DROP TABLE IF EXISTS `dbverzio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dbverzio` (
  `verzio` varchar(32) COLLATE utf8mb3_hungarian_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dbverzio`
--

LOCK TABLES `dbverzio` WRITE;
/*!40000 ALTER TABLE `dbverzio` DISABLE KEYS */;
INSERT INTO `dbverzio` VALUES ('v1.0.0');
/*!40000 ALTER TABLE `dbverzio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `id` bigint NOT NULL AUTO_INCREMENT COMMENT 'unique auto id',
  `parent` bigint NOT NULL,
  `name` varchar(256) COLLATE utf8mb3_hungarian_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,0,'admin'),(2,0,'moderator');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inits`
--

DROP TABLE IF EXISTS `inits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inits` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'rekord azonosító',
  `priceup` float DEFAULT '1.1' COMMENT 'Ár és órabér emelés mértéke ha kereséet > kinálat',
  `pricedown` float DEFAULT '0.9' COMMENT 'Ár és órabér csökkentés mértéke ha kereslet < kinálat',
  `quantityup` float DEFAULT '1.1' COMMENT 'termelés bővités ha átlag feletti és pzitiv a profitráta',
  `quantitydown` float DEFAULT '0.9' COMMENT 'termelés csökkentés ha átlag alatti vagy negativ a profitráta',
  `workhourprice` float DEFAULT '2000' COMMENT 'órabér kezdeti értéke',
  `population` int DEFAULT '1000' COMMENT 'populáció létszáma',
  `hourperday` int DEFAULT '8' COMMENT 'napi munkaóra',
  `set` int DEFAULT '0' COMMENT 'későbbi fejlesztésre',
  `products` text CHARACTER SET utf8mb3 COLLATE utf8mb3_hungarian_ci COMMENT 'termékeket és algoritmust tartalmazó html ',
  `days` varchar(45) COLLATE utf8mb3_hungarian_ci DEFAULT NULL,
  `algorithm` text COLLATE utf8mb3_hungarian_ci,
  `name` varchar(128) COLLATE utf8mb3_hungarian_ci DEFAULT NULL,
  `minworkhourprice` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci COMMENT='szimuláció alap beállításai';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inits`
--

LOCK TABLES `inits` WRITE;
/*!40000 ALTER TABLE `inits` DISABLE KEYS */;
/*!40000 ALTER TABLE `inits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `many_time`
--

DROP TABLE IF EXISTS `many_time`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `many_time` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'rekord azonosító',
  `set` int DEFAULT '0' COMMENT 'későbbi fejlesztéshez',
  `init_id` int DEFAULT '0' COMMENT 'szimuláció azonosító',
  `day` int DEFAULT '0' COMMENT 'nap 0,1,2....',
  `requiredMany` float DEFAULT '0' COMMENT 'a fogyasztási cikk igény kmegvásárlásához szükséges össz pénzmennyiség',
  `validMany` float DEFAULT '0' COMMENT 'a termelés során kifizetett munkabérek összesen',
  `workhourprice` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '0' COMMENT 'órabér',
  PRIMARY KEY (`id`),
  KEY `day` (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci COMMENT='pénz mennyiségek és órabér idősoros adatai';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `many_time`
--

LOCK TABLES `many_time` WRITE;
/*!40000 ALTER TABLE `many_time` DISABLE KEYS */;
/*!40000 ALTER TABLE `many_time` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_time`
--

DROP TABLE IF EXISTS `product_time`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_time` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'rekord azonosító',
  `init_id` int DEFAULT '0' COMMENT 'szimuláció azonosító',
  `product_id` int DEFAULT '0' COMMENT 'termék azonosító',
  `day` int DEFAULT '0' COMMENT 'nap 0,1,2...',
  `quantity` float DEFAULT '0' COMMENT 'termelt mennyiség',
  `price` float DEFAULT '0' COMMENT 'termék ára',
  `selfcost` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_hungarian_ci DEFAULT '0' COMMENT 'önköltségi ár',
  `required` float DEFAULT '0' COMMENT 'fogy.cikknél szükséglet, másnál kereslet',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `fk_product_time_inits1_idx` (`init_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci COMMENT='termékek, szolgáltatások idősoros adatai';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_time`
--

LOCK TABLES `product_time` WRITE;
/*!40000 ALTER TABLE `product_time` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_time` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'rekord azonosító',
  `user_id` int DEFAULT '0' COMMENT 'bejelentkezett user',
  `name` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'termék neve',
  `type` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'fogy.cikk' COMMENT '"fogy.cikk'' | ''term.eszköz'' | ''alapanyag'' | ''alkatrész''',
  `unit` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'db' COMMENT 'mértékegység',
  `workHours` float DEFAULT '0' COMMENT 'termék előállításhoz szükséges munkaóra mennyiség (a beépülő alkatrészek, anyagok és termelő eszközökkel nem számolva)',
  `required` float DEFAULT '0' COMMENT 'fogy.cikkeknél a szükséglet unit/nap/fő',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci COMMENT='termékek bejelentkezett userhez rendelt adatok';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (16,0,'fogyastási cikk 1','fogy.cikk','db',2,1),(17,0,'fogyastási cikk 2','fogy.cikk','db',1,2),(18,0,'fogyastási cikk 3','fogy.cikk','db',2,3),(19,0,'termelő eszköz 1','termelő eszköz','db',30,0),(20,0,'termelő eszköz 2','termelő eszköz','db',10,0);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subproducts`
--

DROP TABLE IF EXISTS `subproducts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subproducts` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'rekord azonosító',
  `product_id` int NOT NULL COMMENT 'termék azonosító',
  `subproduct_id` int DEFAULT NULL COMMENT 'beépülö termék azonosító',
  `quantity` float DEFAULT NULL COMMENT 'beépülő mennyiség',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `fk_subproducts_products2_idx` (`subproduct_id`),
  CONSTRAINT `fk_subproducts_products1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `fk_subproducts_products2` FOREIGN KEY (`subproduct_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci COMMENT='termékekbe beépülő alapnyagok, alkatrészek, term.eszköz amortizáció';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subproducts`
--

LOCK TABLES `subproducts` WRITE;
/*!40000 ALTER TABLE `subproducts` DISABLE KEYS */;
INSERT INTO `subproducts` VALUES (4,16,19,0.0001),(5,17,19,0.0002),(6,18,20,0.0003);
/*!40000 ALTER TABLE `subproducts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_group`
--

DROP TABLE IF EXISTS `user_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_group` (
  `id` bigint NOT NULL AUTO_INCREMENT COMMENT 'unique auto id',
  `user_id` bigint NOT NULL,
  `group_id` bigint NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_group`
--

LOCK TABLES `user_group` WRITE;
/*!40000 ALTER TABLE `user_group` DISABLE KEYS */;
INSERT INTO `user_group` VALUES (1,1,1);
/*!40000 ALTER TABLE `user_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint NOT NULL AUTO_INCREMENT COMMENT 'unique auto id',
  `username` varchar(256) COLLATE utf8mb3_hungarian_ci NOT NULL,
  `password` varchar(256) COLLATE utf8mb3_hungarian_ci NOT NULL,
  `realname` varchar(256) COLLATE utf8mb3_hungarian_ci NOT NULL,
  `email` varchar(256) COLLATE utf8mb3_hungarian_ci NOT NULL,
  `avatar` varchar(256) COLLATE utf8mb3_hungarian_ci DEFAULT NULL,
  `email_verifyed` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','44e811bdcae2bc26af5c11bc5f8b963d7bfea65ec4d6fd6373f7d04f5e6dcf7a','Fogler Tibor','tibor.fogler@gmail.com','1-alak.jpg',1,1,0);
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

-- Dump completed on 2023-02-10 16:00:39
