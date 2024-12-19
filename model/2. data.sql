LOCK TABLES `authorization_providers` WRITE;
/*!40000 ALTER TABLE `authorization_providers` DISABLE KEYS */;
INSERT INTO `authorization_providers` VALUES (1,'Google','google.png','google',NOW(),NULL,NULL),(2,'Facebook','facebook.png','facebook',NOW(),NULL,NULL),(10,'Microsoft','microsoft.png','live',NOW(),NULL,NULL);
/*!40000 ALTER TABLE `authorization_providers` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES ('Administrators',1,NOW(),NULL),('Users',2,NOW(),NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;
