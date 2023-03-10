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
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'rekord azonos??t??',
  `priceup` float DEFAULT '1.1' COMMENT '??r ??s ??rab??r emel??s m??rt??ke ha keres??et > kin??lat',
  `pricedown` float DEFAULT '0.9' COMMENT '??r ??s ??rab??r cs??kkent??s m??rt??ke ha kereslet < kin??lat',
  `quantityup` float DEFAULT '1.1' COMMENT 'termel??s b??vit??s ha ??tlag feletti ??s pzitiv a profitr??ta',
  `quantitydown` float DEFAULT '0.9' COMMENT 'termel??s cs??kkent??s ha ??tlag alatti vagy negativ a profitr??ta',
  `workhourprice` float DEFAULT '2000' COMMENT '??rab??r kezdeti ??rt??ke',
  `population` int DEFAULT '1000' COMMENT 'popul??ci?? l??tsz??ma',
  `hourperday` int DEFAULT '8' COMMENT 'napi munka??ra',
  `set` int DEFAULT '0' COMMENT 'k??s??bbi fejleszt??sre',
  `products` text CHARACTER SET utf8mb3 COLLATE utf8mb3_hungarian_ci COMMENT 'term??keket ??s algoritmust tartalmaz?? html ',
  `days` varchar(45) COLLATE utf8mb3_hungarian_ci DEFAULT NULL,
  `algorithm` text COLLATE utf8mb3_hungarian_ci,
  `name` varchar(128) COLLATE utf8mb3_hungarian_ci DEFAULT NULL,
  `minworkhourprice` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci COMMENT='szimul??ci?? alap be??ll??t??sai';
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
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'rekord azonos??t??',
  `set` int DEFAULT '0' COMMENT 'k??s??bbi fejleszt??shez',
  `init_id` int DEFAULT '0' COMMENT 'szimul??ci?? azonos??t??',
  `day` int DEFAULT '0' COMMENT 'nap 0,1,2....',
  `requiredMany` float DEFAULT '0' COMMENT 'a fogyaszt??si cikk ig??ny kmegv??s??rl??s??hoz sz??ks??ges ??ssz p??nzmennyis??g',
  `validMany` float DEFAULT '0' COMMENT 'a termel??s sor??n kifizetett munkab??rek ??sszesen',
  `workhourprice` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT '0' COMMENT '??rab??r',
  PRIMARY KEY (`id`),
  KEY `day` (`day`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci COMMENT='p??nz mennyis??gek ??s ??rab??r id??soros adatai';
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
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'rekord azonos??t??',
  `init_id` int DEFAULT '0' COMMENT 'szimul??ci?? azonos??t??',
  `product_id` int DEFAULT '0' COMMENT 'term??k azonos??t??',
  `day` int DEFAULT '0' COMMENT 'nap 0,1,2...',
  `quantity` float DEFAULT '0' COMMENT 'termelt mennyis??g',
  `price` float DEFAULT '0' COMMENT 'term??k ??ra',
  `selfcost` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_hungarian_ci DEFAULT '0' COMMENT '??nk??lts??gi ??r',
  `required` float DEFAULT '0' COMMENT 'fogy.cikkn??l sz??ks??glet, m??sn??l kereslet',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `fk_product_time_inits1_idx` (`init_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci COMMENT='term??kek, szolg??ltat??sok id??soros adatai';
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
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'rekord azonos??t??',
  `user_id` int DEFAULT '0' COMMENT 'bejelentkezett user',
  `name` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT NULL COMMENT 'term??k neve',
  `type` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'fogy.cikk' COMMENT '"fogy.cikk'' | ''term.eszk??z'' | ''alapanyag'' | ''alkatr??sz''',
  `unit` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci DEFAULT 'db' COMMENT 'm??rt??kegys??g',
  `workHours` float DEFAULT '0' COMMENT 'term??k el????ll??t??shoz sz??ks??ges munka??ra mennyis??g (a be??p??l?? alkatr??szek, anyagok ??s termel?? eszk??z??kkel nem sz??molva)',
  `required` float DEFAULT '0' COMMENT 'fogy.cikkekn??l a sz??ks??glet unit/nap/f??',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci COMMENT='term??kek bejelentkezett userhez rendelt adatok';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (16,0,'fogyast??si cikk 1','fogy.cikk','db',2,1),(17,0,'fogyast??si cikk 2','fogy.cikk','db',1,2),(18,0,'fogyast??si cikk 3','fogy.cikk','db',2,3),(19,0,'termel?? eszk??z 1','termel?? eszk??z','db',30,0),(20,0,'termel?? eszk??z 2','termel?? eszk??z','db',10,0);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subproducts`
--

DROP TABLE IF EXISTS `subproducts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subproducts` (
  `id` int NOT NULL AUTO_INCREMENT COMMENT 'rekord azonos??t??',
  `product_id` int NOT NULL COMMENT 'term??k azonos??t??',
  `subproduct_id` int DEFAULT NULL COMMENT 'be??p??l?? term??k azonos??t??',
  `quantity` float DEFAULT NULL COMMENT 'be??p??l?? mennyis??g',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `fk_subproducts_products2_idx` (`subproduct_id`),
  CONSTRAINT `fk_subproducts_products1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `fk_subproducts_products2` FOREIGN KEY (`subproduct_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_hungarian_ci COMMENT='term??kekbe be??p??l?? alapnyagok, alkatr??szek, term.eszk??z amortiz??ci??';
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
