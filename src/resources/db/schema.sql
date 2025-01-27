CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `google_id` int DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `notified` boolean DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
)  ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `defective_products` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  -- Basic identifiers
  `alert_number` VARCHAR(100) DEFAULT NULL,          -- EU: caseNumber
  `case_url` VARCHAR(255) DEFAULT NULL,                -- EU: reference, Slovak: case_url
  
  -- Product information
  `product_name` VARCHAR(255) DEFAULT NULL,           -- EU: name, Slovak: product_name
  `product_info` VARCHAR(255) DEFAULT NULL,           -- EU: product
  `product_category` VARCHAR(255) DEFAULT NULL,       -- EU: category, Slovak: product_category
  `brand` VARCHAR(100) DEFAULT NULL,                   -- brand
  `model_type_number` VARCHAR(100) DEFAULT NULL,       -- EU: type_numberOfModel
  
  -- Batch and codes
  `batch_number` VARCHAR(255) DEFAULT NULL,           -- batchNumber
  `barcode` VARCHAR(255) DEFAULT NULL,                 -- barcode
  `company_recall_code` VARCHAR(100) DEFAULT NULL,     -- companyRecallCode
  
  -- Risk and measures information
  `risk_type` VARCHAR(255) DEFAULT NULL,               -- EU: riskType, Slovak: risky_type
  `risk_info` TEXT DEFAULT NULL,                       -- Combined EU (danger) and Slovak (risk_description)
  `measures` TEXT DEFAULT NULL,                        -- EU: measures
  
  -- Additional recall and production info
  `company_recall_page` VARCHAR(255) DEFAULT NULL,     -- EU: URLrecall
  `product_description` TEXT DEFAULT NULL,            -- EU: description, Slovak: product_description
  `production_dates` VARCHAR(255) DEFAULT NULL,        -- productionDates
  
  -- Country and origin info
  `notifying_country` VARCHAR(100) DEFAULT NULL,       -- EU: notifyingCountry
  `country_of_origin` VARCHAR(255) DEFAULT NULL,      -- EU: countryOfOrigin; also Slovak: country_of_origin
  
  -- Other fields (if needed)
  `type` VARCHAR(100) DEFAULT NULL,                    -- EU: type (or used for product type, if needed)
  `level` VARCHAR(100) DEFAULT NULL,                   -- EU: level
  
  -- Media
  `images` TEXT DEFAULT NULL                           -- EU: pictures (from <picture>), Slovak: images
) ENGINE=InnoDB 
  DEFAULT CHARSET=utf8mb4 
  COLLATE=utf8mb4_general_ci;


CREATE TABLE `product_history` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `product_id` INT NOT NULL,
    `status` INT NOT NULL,
    `user_id` INT NOT NULL,
    `barcode` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255),
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

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `token` VARCHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `token_unique` (`token`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
