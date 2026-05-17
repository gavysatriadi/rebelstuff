-- Table structure for table `product_images`

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_filename` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `product_images`

LOCK TABLES `product_images` WRITE;
INSERT INTO `product_images` VALUES (10,27,'https://res.cloudinary.com/dohpfdgki/image/upload/v1779015469/rebelstuff/tk2ddyxzsrwhrw0ihkul.jpg'),(11,27,'https://res.cloudinary.com/dohpfdgki/image/upload/v1779015472/rebelstuff/doqcpva3ekipl5ze0qmr.jpg'),(12,28,'https://res.cloudinary.com/dohpfdgki/image/upload/v1779015474/rebelstuff/uvqaztdwg0vma6qzfzba.jpg'),(13,28,'https://res.cloudinary.com/dohpfdgki/image/upload/v1779015476/rebelstuff/rbaqqmwjcpccxsjxyyfe.jpg'),(14,29,'https://res.cloudinary.com/dohpfdgki/image/upload/v1779015478/rebelstuff/tzvt2luhdr1wtky7ukxn.jpg'),(15,29,'https://res.cloudinary.com/dohpfdgki/image/upload/v1779015481/rebelstuff/x4xeye9e97rw5qf8vlp2.jpg'),(16,30,'voucherexam.png'),(17,31,'https://res.cloudinary.com/dohpfdgki/image/upload/v1779014476/rebelstuff/eylznamdiysea4h4t5ub.png'),(18,32,'https://res.cloudinary.com/dohpfdgki/image/upload/v1779014618/rebelstuff/ukqbawyun01fbupvxqls.png');
UNLOCK TABLES;

-- Table structure for table `products`

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `image` text COLLATE utf8mb4_general_ci,
  `ukuran` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bahan` text COLLATE utf8mb4_general_ci,
  `desain` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `products`

LOCK TABLES `products` WRITE;
INSERT INTO `products` VALUES (27,'Baju',150000.00,'kkdskmdaskdma','2025-05-14 21:44:45',NULL,NULL,'daasd','cassddas'),(28,'Baju',150000.00,'kewqkmqwek','2025-05-14 21:57:17',NULL,'S,M,L,XL','cotton','qkddl,dsa'),(29,'baju',150000.00,'ini baju','2025-05-14 22:17:35',NULL,'S,M,L,XL','cotton','admsmasd'),(30,'Testing',99000.00,'Testing','2026-05-17 10:12:15',NULL,'S, M, L','Testing','Testing'),(31,'Testing1',1000000.00,'Testing1','2026-05-17 10:41:15',NULL,'S, M, L','Testing1','Testing1'),(32,'Testing2',99999.00,'Testing2','2026-05-17 10:43:36',NULL,'S, M, L','Testing2','Testing2');
UNLOCK TABLES;

-- Table structure for table `users`

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `users`

LOCK TABLES `users` WRITE;
INSERT INTO `users` VALUES (1,'gavy','satriadigavy@gmail.com','$2y$10$5le4V1BB.Rhb.FQXOIjW7eE77o0mX9mqUoJDowABhmtgUe/qcUaPW',NULL,NULL,NULL,'2025-05-04 10:33:43'),(3,'admin','admin@gmail.com','$2y$10$oQ9Sk2CBhtVLse0B/YyFVemCPoxy5iXmC2CJ0Za5COF4rKn9/S/Ue',NULL,NULL,NULL,'2025-05-04 10:39:06'),(4,'akmal','akmal@gmail.com','$2y$10$TK54GDTCArUqM8gJqIV12eopIRCGnbvzKxN7.HXgdz/EzLrfBWm0C',NULL,NULL,NULL,'2025-05-04 12:03:33');
UNLOCK TABLES;
