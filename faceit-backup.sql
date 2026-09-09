/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.28-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: faceit
-- ------------------------------------------------------
-- Server version	10.6.28-MariaDB-ubu2204

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `discussions`
--

DROP TABLE IF EXISTS `discussions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `discussions` (
  `discussion_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `image_url` varchar(2048) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`discussion_id`),
  KEY `fk_discussions_creator` (`created_by`),
  KEY `idx_discussions_group` (`group_id`),
  CONSTRAINT `fk_discussions_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_discussions_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discussions`
--

LOCK TABLES `discussions` WRITE;
/*!40000 ALTER TABLE `discussions` DISABLE KEYS */;
INSERT INTO `discussions` VALUES (1,13,2,'test',NULL,'2026-09-05 09:45:06'),(2,8,2,'test 2',NULL,'2026-09-07 08:32:59'),(3,8,2,'test 3',NULL,'2026-09-07 08:37:41'),(4,1,3,'Welcome to Web Development',NULL,'2026-09-07 13:24:38'),(5,2,4,'Welcome to PHP and MySQL',NULL,'2026-09-07 13:24:38'),(6,3,5,'Welcome to JavaScript',NULL,'2026-09-07 13:24:38'),(7,4,3,'Welcome to Frontend Development',NULL,'2026-09-07 13:24:38'),(8,5,4,'Welcome to Backend Development',NULL,'2026-09-07 13:24:38'),(9,6,5,'Welcome to Databases and SQL',NULL,'2026-09-07 13:24:38'),(10,7,3,'Welcome to Cybersecurity',NULL,'2026-09-07 13:24:38'),(11,8,4,'Welcome to Artificial Intelligence',NULL,'2026-09-07 13:24:38'),(12,9,5,'Welcome to UI and UX Design',NULL,'2026-09-07 13:24:38'),(13,10,3,'Welcome to Cloud and DevOps',NULL,'2026-09-07 13:24:38'),(14,11,4,'Welcome to Git and GitHub',NULL,'2026-09-07 13:24:38'),(15,12,5,'Welcome to Linux',NULL,'2026-09-07 13:24:38'),(16,13,3,'Welcome to Computer Networks',NULL,'2026-09-07 13:24:38'),(17,14,4,'Welcome to Software Testing',NULL,'2026-09-07 13:24:38'),(18,15,5,'Welcome to IT Careers',NULL,'2026-09-07 13:24:38'),(19,1,3,'Web Development: tips and resources',NULL,'2026-09-07 13:24:38'),(20,2,4,'PHP and MySQL: tips and resources',NULL,'2026-09-07 13:24:38'),(21,3,5,'JavaScript: tips and resources',NULL,'2026-09-07 13:24:38'),(22,4,3,'Frontend Development: tips and resources',NULL,'2026-09-07 13:24:38'),(23,5,4,'Backend Development: tips and resources',NULL,'2026-09-07 13:24:38'),(24,6,5,'Databases and SQL: tips and resources',NULL,'2026-09-07 13:24:38'),(25,7,3,'Cybersecurity: tips and resources',NULL,'2026-09-07 13:24:38'),(26,8,4,'Artificial Intelligence: tips and resources',NULL,'2026-09-07 13:24:38'),(27,9,5,'UI and UX Design: tips and resources',NULL,'2026-09-07 13:24:38'),(28,10,3,'Cloud and DevOps: tips and resources',NULL,'2026-09-07 13:24:38'),(29,11,4,'Git and GitHub: tips and resources',NULL,'2026-09-07 13:24:38'),(30,12,5,'Linux: tips and resources',NULL,'2026-09-07 13:24:38'),(31,13,3,'Computer Networks: tips and resources',NULL,'2026-09-07 13:24:38'),(32,14,4,'Software Testing: tips and resources',NULL,'2026-09-07 13:24:38'),(33,15,5,'IT Careers: tips and resources',NULL,'2026-09-07 13:24:38'),(34,1,2,'Test',NULL,'2026-09-09 08:23:37');
/*!40000 ALTER TABLE `discussions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_applications`
--

DROP TABLE IF EXISTS `group_applications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_applications` (
  `application_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `application_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `handled_at` timestamp NULL DEFAULT NULL,
  `handled_by` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`application_id`),
  UNIQUE KEY `unique_group_application` (`group_id`,`user_id`),
  KEY `fk_applications_user` (`user_id`),
  KEY `fk_applications_handler` (`handled_by`),
  KEY `idx_applications_group_status` (`group_id`,`application_status`),
  CONSTRAINT `fk_applications_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_applications_handler` FOREIGN KEY (`handled_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_applications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_applications`
--

LOCK TABLES `group_applications` WRITE;
/*!40000 ALTER TABLE `group_applications` DISABLE KEYS */;
INSERT INTO `group_applications` VALUES (3,8,2,'pending','2026-09-07 20:35:10',NULL,NULL),(4,9,2,'pending','2026-09-07 20:47:19',NULL,NULL),(11,8,3,'pending','2026-09-08 19:17:51',NULL,NULL),(12,11,3,'pending','2026-09-08 19:17:54',NULL,NULL),(13,15,3,'pending','2026-09-08 19:17:55',NULL,NULL),(14,3,3,'pending','2026-09-08 19:17:55',NULL,NULL),(15,12,3,'pending','2026-09-08 19:17:56',NULL,NULL),(16,2,3,'pending','2026-09-08 19:17:57',NULL,NULL),(17,5,3,'pending','2026-09-08 19:17:58',NULL,NULL),(18,6,3,'pending','2026-09-08 19:17:58',NULL,NULL),(19,14,3,'pending','2026-09-08 19:17:59',NULL,NULL),(20,9,3,'pending','2026-09-08 19:18:00',NULL,NULL);
/*!40000 ALTER TABLE `group_applications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_invites`
--

DROP TABLE IF EXISTS `group_invites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_invites` (
  `invite_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `used_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`invite_id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `fk_invites_creator` (`created_by`),
  KEY `fk_invites_user` (`used_by`),
  KEY `idx_invites_group_expiration` (`group_id`,`expires_at`),
  CONSTRAINT `fk_invites_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_invites_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invites_user` FOREIGN KEY (`used_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_invites`
--

LOCK TABLES `group_invites` WRITE;
/*!40000 ALTER TABLE `group_invites` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_invites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_members` (
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `group_role` enum('member','admin') NOT NULL DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `fk_members_user` (`user_id`),
  CONSTRAINT `fk_members_group` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_members`
--

LOCK TABLES `group_members` WRITE;
/*!40000 ALTER TABLE `group_members` DISABLE KEYS */;
INSERT INTO `group_members` VALUES (1,2,'admin','2026-09-04 11:24:56'),(1,3,'admin','2026-09-07 13:24:38'),(2,2,'admin','2026-09-04 11:24:56'),(2,4,'admin','2026-09-07 13:24:38'),(3,5,'admin','2026-09-07 13:24:38'),(4,2,'member','2026-09-04 11:24:56'),(4,3,'admin','2026-09-07 13:24:38'),(5,2,'member','2026-09-04 11:24:56'),(5,4,'admin','2026-09-07 13:24:38'),(6,5,'admin','2026-09-07 13:24:38'),(7,3,'admin','2026-09-07 13:24:38'),(8,4,'admin','2026-09-07 13:24:38'),(9,5,'admin','2026-09-07 13:24:38'),(10,3,'admin','2026-09-07 13:24:38'),(11,4,'admin','2026-09-07 13:24:38'),(12,5,'admin','2026-09-07 13:24:38'),(13,3,'admin','2026-09-07 13:24:38'),(14,4,'admin','2026-09-07 13:24:38'),(15,5,'admin','2026-09-07 13:24:38');
/*!40000 ALTER TABLE `group_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `groups`
--

DROP TABLE IF EXISTS `groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `groups` (
  `group_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(150) NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`group_id`),
  KEY `fk_groups_creator` (`created_by`),
  CONSTRAINT `fk_groups_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `groups`
--

LOCK TABLES `groups` WRITE;
/*!40000 ALTER TABLE `groups` DISABLE KEYS */;
INSERT INTO `groups` VALUES (1,'Web Development',2,'2026-09-04 11:24:56'),(2,'PHP and MySQL',2,'2026-09-04 11:24:56'),(3,'JavaScript',2,'2026-09-04 11:24:56'),(4,'Frontend Development',2,'2026-09-04 11:24:56'),(5,'Backend Development',2,'2026-09-04 11:24:56'),(6,'Databases and SQL',2,'2026-09-04 11:24:56'),(7,'Cybersecurity',2,'2026-09-04 11:24:56'),(8,'Artificial Intelligence',2,'2026-09-04 11:24:56'),(9,'UI and UX Design',2,'2026-09-04 11:24:56'),(10,'Cloud and DevOps',2,'2026-09-04 11:24:56'),(11,'Git and GitHub',2,'2026-09-04 11:24:56'),(12,'Linux',2,'2026-09-04 11:24:56'),(13,'Computer Networks',2,'2026-09-04 11:24:56'),(14,'Software Testing',2,'2026-09-04 11:24:56'),(15,'IT Careers',2,'2026-09-04 11:24:56');
/*!40000 ALTER TABLE `groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `discussion_id` int(10) unsigned NOT NULL,
  `created_by` int(10) unsigned DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`post_id`),
  KEY `fk_posts_creator` (`created_by`),
  KEY `idx_posts_discussion` (`discussion_id`),
  CONSTRAINT `fk_posts_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  CONSTRAINT `fk_posts_discussion` FOREIGN KEY (`discussion_id`) REFERENCES `discussions` (`discussion_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,1,2,'testar lite text :D','2026-09-05 09:45:06'),(2,2,2,'Här kommer mycket mer text som kan vara bra att läsa om man är intresserad. Här kommer mycket mer text som kan vara bra att läsa om man är intresserad.Här kommer mycket mer text som kan vara bra att läsa om man är intresserad.Här kommer mycket mer text som kan vara bra att läsa om man är intresserad.Här kommer mycket mer text som kan vara bra att läsa om man är intresserad.Här kommer mycket mer text som kan vara bra att läsa om man är intresserad.Här kommer mycket mer text som kan vara bra att läsa om man är intresserad.Här kommer mycket mer text som kan vara bra att läsa om man är intresserad.Här kommer mycket mer text som kan vara bra att läsa om man är intresserad.Här kommer mycket mer text som kan vara bra att läsa om man är intresserad.','2026-09-07 08:32:59'),(3,3,2,'lalalalalalalala','2026-09-07 08:37:41'),(4,3,2,'Det tycker inte jag','2026-09-07 11:00:43'),(5,4,3,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(6,5,4,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(7,6,5,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(8,7,3,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(9,8,4,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(10,9,5,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(11,10,3,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(12,11,4,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(13,12,5,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(14,13,3,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(15,14,4,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(16,15,5,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(17,16,3,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(18,17,4,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(19,18,5,'Welcome! What are you currently learning, and what would you like to discuss with the group?','2026-09-07 13:24:38'),(20,19,3,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(21,20,4,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(22,21,5,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(23,22,3,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(24,23,4,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(25,24,5,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(26,25,3,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(27,26,4,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(28,27,5,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(29,28,3,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(30,29,4,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(31,30,5,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(32,31,3,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(33,32,4,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(34,33,5,'Share your favorite tools, resources and practical tips with the rest of the group.','2026-09-07 13:24:38'),(36,23,2,'wow, coolt!','2026-09-09 08:22:33'),(37,34,2,'Det här är en lite text om ett test.','2026-09-09 08:23:37');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (2,'exempel','exempelson','Emily','emily.eriksson@gmail.com','$2y$12$c4O3a0H/qS2Z6/3L9p5UBuZ2cTEobP2VsgO7RD8jc1ILZvzzScgqC','2026-09-01 07:54:31'),(3,'Alex','Andersson','CodeAlex','alex@faceit.test','$2y$12$GzaIPBejdjFFRn4RPmNseekmwMu3hhyZzqc4XR2h7SE.8ZD5FR3S2','2026-09-07 13:24:38'),(4,'Samira','Svensson','Sam','samira@faceit.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi','2026-09-07 13:24:38'),(5,'Leo','Lindberg','Leo Lindberg','leo@faceit.test','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi','2026-09-07 13:24:38');
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

-- Dump completed on 2026-09-09 10:02:50
