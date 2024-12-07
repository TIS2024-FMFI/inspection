-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
-- Updated schema reflecting additional fields in the defective_products table

DROP TABLE IF EXISTS `defective_products`;
CREATE TABLE `defective_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int DEFAULT NULL,
  `alert_number` varchar(100) DEFAULT NULL,
  `type_of_alert` varchar(100) DEFAULT NULL COMMENT 'Type of alert issued',
  `alert_type` varchar(100) DEFAULT NULL COMMENT 'Safety, health, counterfeit, etc.',
  `alert_submitted_by` varchar(100) DEFAULT NULL COMMENT 'Entity submitting the alert',
  `notifying_country` varchar(100) DEFAULT NULL,
  `counterfeit` boolean DEFAULT NULL COMMENT 'Indicates if the product is counterfeit',
  `hazard_type` varchar(100) DEFAULT NULL COMMENT 'Type of danger or risk',
  `hazard_causes` text DEFAULT NULL COMMENT 'Causes or details of the hazard',
  `measures_operators` text DEFAULT NULL COMMENT 'Preventive measures taken by operators',
  `measures_authorities` text DEFAULT NULL COMMENT 'Actions mandated by authorities',
  `compulsory_measures` text DEFAULT NULL COMMENT 'Compulsory measures taken to address risk',
  `voluntary_measures` text DEFAULT NULL COMMENT 'Voluntary measures taken by the company',
  `found_and_measures_taken_in` text DEFAULT NULL COMMENT 'Countries where measures were taken',
  `product_name` varchar(255) DEFAULT NULL COMMENT 'Name of the product',
  `product_description` text DEFAULT NULL COMMENT 'Detailed product description',
  `packaging_description` text DEFAULT NULL COMMENT 'Details about the product packaging',
  `brand` varchar(100) DEFAULT NULL COMMENT 'Brand of the product',
  `product_category` varchar(100) DEFAULT NULL COMMENT 'Category of the product (e.g., toys, electronics)',
  `model_type_number` varchar(100) DEFAULT NULL COMMENT 'Type/number of the product model',
  `oecd_portal_category` varchar(100) DEFAULT NULL COMMENT 'OECD classification category',
  `risk_description` text DEFAULT NULL COMMENT 'Additional details about the risk',
  `risk_legal_provision` text DEFAULT NULL COMMENT 'Legal provisions associated with the risk',
  `recall_code` varchar(100) DEFAULT NULL COMMENT 'Optional recall code',
  `company_recall_code` varchar(100) DEFAULT NULL COMMENT 'Recall code provided by the company',
  `company_recall_page` varchar(255) DEFAULT NULL COMMENT 'URL to the company recall page',
  `case_url` varchar(255) DEFAULT NULL COMMENT 'URL of the defect case',
  `barcode` varchar(100) DEFAULT NULL COMMENT 'Unique barcode of the product',
  `batch_number` varchar(100) DEFAULT NULL COMMENT 'Batch number of the product',
  `production_dates` varchar(255) DEFAULT NULL COMMENT 'Manufacturing dates of the product',
  `published_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Date of alert publication',
  `images` text DEFAULT NULL COMMENT 'URLs or paths to product images',
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `defective_products_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Updated products table for backward compatibility and alignment
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `product_description` text,
  `packaging_description` text,
  `category` varchar(100) DEFAULT NULL,
  `country_of_origin` varchar(100) DEFAULT NULL,
  `risk_type` varchar(100) DEFAULT NULL,
  `legal_provisions` text,
  `ec_approval_model` varchar(100) DEFAULT NULL,
  `recall_code` varchar(100) DEFAULT NULL,
  `date_added` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `barcode` (`barcode`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Retain other tables with minimal changes as per the new structure
DROP TABLE IF EXISTS `personalized_lists`;
CREATE TABLE `personalized_lists` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `added_on` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `personalized_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `personalized_lists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `scanned_products`;
CREATE TABLE `scanned_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `scanned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) DEFAULT NULL COMMENT 'defective or not defective',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `scanned_products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `scanned_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL COMMENT 'guest, registered, admin',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
