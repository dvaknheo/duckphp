DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(32) COLLATE utf8_bin NOT NULL,
  `password` varchar(64) COLLATE utf8_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `Users`
--

LOCK TABLES `Users` WRITE;
/*!40000 ALTER TABLE `Users` DISABLE KEYS */;
INSERT INTO `Users` VALUES (1,'aa','$2y$10$HEnsqIcNWAYGiyvwVnJGo.IIogl2X0YQL7JlTuKRYcN.Z/rhvMHI2',NULL,NULL,NULL),(2,'abc','$2y$10$G25OLpq5SRPxR4kFv.ZIwegESIyoEka/j6MV1OPjGzCb6hi5kOtbW',NULL,NULL,NULL),(3,'a','$2y$10$HKlThhmUWgEjLbm1.3qhFOa0Xq.1QXigAfT/7UZT6HUwg7c08UfP.','2018-06-11 02:50:10',NULL,NULL),(4,'t1','$2y$10$xYPQEeIw/V9ITux.EvOr0uCfA5caLew5guCytERcwx4SnuCgwmaxG','2018-07-23 02:10:14',NULL,NULL),(5,'t2','$2y$10$p3lYbiPJuQPnrH6IbhSzFujIFskchrdqjuF8Qs6SnTyCy5KOHrueW','2018-07-24 14:34:51',NULL,NULL);
/*!40000 ALTER TABLE `Users` ENABLE KEYS */;
UNLOCK TABLES;