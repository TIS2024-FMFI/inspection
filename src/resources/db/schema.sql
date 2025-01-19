CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `google_id` int DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
)  ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `defective_products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `alert_number` varchar(100) DEFAULT NULL,
  `type_of_alert` varchar(100) DEFAULT NULL,
  `type` VARCHAR(100) DEFAULT NULL,
  `risk_type` VARCHAR(100) DEFAULT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `product_history` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `product_id` INT NOT NULL,
    `status` INT NOT NULL,
    `user_id` INT NOT NULL,
    `barcode` VARCHAR(255) NOT NULL,
    `product_link` VARCHAR(255) NOT NULL,
    `date` DATE NOT NULL,
    `time` TIME NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `user_submitted_products` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `name` VARCHAR(255),
    `barcode` VARCHAR(255),
    `product_description` TEXT,
    `brand` VARCHAR(255),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;