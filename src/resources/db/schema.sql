DROP TABLE IF EXISTS `defective_products`;
CREATE TABLE `defective_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alert_number` varchar(100) DEFAULT NULL,
  `type_of_alert` varchar(100) DEFAULT NULL,
  `type` VARCHAR(100) DEFAULT NULL COMMENT 'This was added later - usually consumer'; 
  `risk_type` VARCHAR(100) DEFAULT NULL COMMENT 'This was added later', 
  `alert_type` varchar(100) DEFAULT NULL,
  `country_of_origin` VARCHAR(100) DEFAULT NULL,
  `alert_submitted_by` varchar(100) DEFAULT NULL,
  `notifying_country` varchar(100) DEFAULT NULL,
  `counterfeit` boolean DEFAULT NULL,
  `hazard_type` varchar(100) DEFAULT NULL,
  `hazard_causes` text DEFAULT NULL,
  `measures_operators` text DEFAULT NULL,
  `measures_authorities` text DEFAULT NULL,
  `compulsory_measures` text DEFAULT NULL,
  `voluntary_measures` text DEFAULT NULL,
  `found_and_measures_taken_in` text DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `product_description` text DEFAULT NULL,
  `packaging_description` text DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `product_category` varchar(100) DEFAULT NULL,
  `model_type_number` varchar(100) DEFAULT NULL,
  `oecd_portal_category` varchar(100) DEFAULT NULL,
  `risk_description` text DEFAULT NULL,
  `risk_legal_provision` text DEFAULT NULL,
  `recall_code` varchar(100) DEFAULT NULL,
  `company_recall_code` varchar(100) DEFAULT NULL,
  `company_recall_page` varchar(255) DEFAULT NULL,
  `case_url` varchar(255) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `production_dates` varchar(255) DEFAULT NULL,
  `published_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `images` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `defective_products_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `product_history`;
CREATE TABLE product_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    barcode VARCHAR(255) NOT NULL,
    product_link VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    FOREIGN KEY (product_id) REFERENCES defective_products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `personalized_lists`;
CREATE TABLE `personalized_lists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `user_submitted_product_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `user_submitted_product_id` (`user_submitted_product_id`),
  CONSTRAINT `personalized_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `personalized_lists_ibfk_2` FOREIGN KEY (`user_submitted_product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `user_submitted_products`;
CREATE TABLE user_submitted_products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL COMMENT 'Reference to the user who submitted the product',
    name VARCHAR(255) COMMENT 'Product name',
    barcode VARCHAR(255) NOT NULL COMMENT 'Optional barcode',
    product_description TEXT COMMENT 'General description',
    brand VARCHAR(255) COMMENT 'Brand name',
    FOREIGN KEY (user_id) REFERENCES users(id)
); ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;