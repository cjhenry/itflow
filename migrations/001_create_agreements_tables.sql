-- Migration: Create Agreement Tables
-- Date: 2025-11-07
-- Purpose: Add agreement feature for MSP billing management

-- Create agreements table
CREATE TABLE IF NOT EXISTS `agreements` (
  `agreement_id` int(11) NOT NULL AUTO_INCREMENT,
  `agreement_prefix` varchar(200) DEFAULT 'AGR',
  `agreement_number` int(11) NOT NULL,
  `agreement_name` varchar(200) NOT NULL,
  `agreement_reference` varchar(200),
  `agreement_type` ENUM('Fixed Price - Monthly', 'Fixed Price - Quarterly', 'Fixed Price - Annually', 'Block Hours - Prepaid', 'Block Hours - Monthly Drawdown', 'Time & Materials') NOT NULL,
  `agreement_status` ENUM('Draft', 'Active', 'Expired', 'Cancelled', 'Renewed') DEFAULT 'Draft',
  `agreement_scope` TEXT,
  `agreement_exclusions` TEXT,
  `agreement_start_date` DATE NOT NULL,
  `agreement_end_date` DATE NOT NULL,
  `agreement_next_invoice_date` DATE,
  `agreement_last_invoice_date` DATE,
  `agreement_value` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `agreement_recurring_amount` DECIMAL(15,2) DEFAULT 0.00,
  `agreement_billing_frequency` ENUM('Monthly', 'Quarterly', 'Annually', 'One-Time') DEFAULT 'Monthly',
  `agreement_currency_code` varchar(200) DEFAULT 'USD',
  `agreement_net_terms` INT DEFAULT 30,
  `agreement_hours_included` DECIMAL(10,2) DEFAULT 0.00,
  `agreement_hours_used` DECIMAL(10,2) DEFAULT 0.00,
  `agreement_hours_remaining` DECIMAL(10,2) GENERATED ALWAYS AS (agreement_hours_included - agreement_hours_used) STORED,
  `agreement_hours_overage` DECIMAL(10,2) DEFAULT 0.00,
  `agreement_overage_rate` DECIMAL(15,2) DEFAULT 0.00,
  `agreement_hours_rollover` TINYINT(1) DEFAULT 0,
  `agreement_auto_renew` TINYINT(1) DEFAULT 0,
  `agreement_auto_renew_term` INT DEFAULT 12,
  `agreement_auto_invoice` TINYINT(1) DEFAULT 1,
  `agreement_email_notifications` TINYINT(1) DEFAULT 1,
  `agreement_low_hour_threshold` DECIMAL(5,2) DEFAULT 15.00,
  `agreement_all_assets_covered` TINYINT(1) DEFAULT 0,
  `agreement_client_id` INT NOT NULL,
  `agreement_recurring_invoice_id` INT,
  `agreement_template_id` INT,
  `agreement_parent_agreement_id` INT,
  `agreement_contract_file` varchar(200),
  `agreement_notes` TEXT,
  `agreement_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `agreement_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `agreement_archived_at` DATETIME,
  PRIMARY KEY (`agreement_id`),
  INDEX (agreement_client_id),
  INDEX (agreement_status),
  INDEX (agreement_start_date),
  INDEX (agreement_end_date),
  INDEX (agreement_next_invoice_date),
  FOREIGN KEY (agreement_client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
  FOREIGN KEY (agreement_recurring_invoice_id) REFERENCES recurring_invoices(recurring_invoice_id) ON DELETE SET NULL,
  FOREIGN KEY (agreement_parent_agreement_id) REFERENCES agreements(agreement_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create agreement_assets table (many-to-many)
CREATE TABLE IF NOT EXISTS `agreement_assets` (
  `agreement_asset_id` INT AUTO_INCREMENT PRIMARY KEY,
  `agreement_asset_agreement_id` INT NOT NULL,
  `agreement_asset_asset_id` INT NOT NULL,
  `agreement_asset_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_agreement_asset (agreement_asset_agreement_id, agreement_asset_asset_id),
  FOREIGN KEY (agreement_asset_agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE,
  FOREIGN KEY (agreement_asset_asset_id) REFERENCES assets(asset_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create agreement_services table (many-to-many)
CREATE TABLE IF NOT EXISTS `agreement_services` (
  `agreement_service_id` INT AUTO_INCREMENT PRIMARY KEY,
  `agreement_service_agreement_id` INT NOT NULL,
  `agreement_service_category` VARCHAR(200) NOT NULL,
  `agreement_service_included` TINYINT(1) DEFAULT 1,
  `agreement_service_notes` TEXT,
  UNIQUE KEY unique_agreement_service (agreement_service_agreement_id, agreement_service_category),
  FOREIGN KEY (agreement_service_agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create agreement_rate_tiers table
CREATE TABLE IF NOT EXISTS `agreement_rate_tiers` (
  `rate_tier_id` INT AUTO_INCREMENT PRIMARY KEY,
  `rate_tier_agreement_id` INT NOT NULL,
  `rate_tier_name` VARCHAR(100) NOT NULL,
  `rate_tier_rate` DECIMAL(15,2) NOT NULL,
  `rate_tier_rate_multiplier` DECIMAL(5,2) DEFAULT 1.00,
  `rate_tier_ticket_type` VARCHAR(100),
  `rate_tier_applies_after_hours` TINYINT(1) DEFAULT 0,
  `rate_tier_applies_weekends` TINYINT(1) DEFAULT 0,
  `rate_tier_notes` TEXT,
  FOREIGN KEY (rate_tier_agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE,
  INDEX (rate_tier_agreement_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create agreement_hours_history table
CREATE TABLE IF NOT EXISTS `agreement_hours_history` (
  `history_id` INT AUTO_INCREMENT PRIMARY KEY,
  `history_agreement_id` INT NOT NULL,
  `history_period_start` DATE NOT NULL,
  `history_period_end` DATE NOT NULL,
  `history_hours_included` DECIMAL(10,2),
  `history_hours_used` DECIMAL(10,2),
  `history_hours_overage` DECIMAL(10,2),
  `history_tickets_logged` INT DEFAULT 0,
  `history_notes` TEXT,
  `history_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (history_agreement_id) REFERENCES agreements(agreement_id) ON DELETE CASCADE,
  INDEX (history_agreement_id),
  INDEX (history_period_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Modify tickets table
ALTER TABLE `tickets` ADD COLUMN `ticket_agreement_id` INT AFTER `ticket_client_id`;
ALTER TABLE `tickets` ADD INDEX `ticket_agreement_id` (`ticket_agreement_id`);
ALTER TABLE `tickets` ADD FOREIGN KEY `fk_ticket_agreement` (`ticket_agreement_id`) REFERENCES `agreements`(`agreement_id`) ON DELETE SET NULL;

-- Modify ticket_replies table
ALTER TABLE `ticket_replies` ADD COLUMN `ticket_reply_rate` DECIMAL(15,2) DEFAULT 0.00 AFTER `ticket_reply_time_worked`;
ALTER TABLE `ticket_replies` ADD COLUMN `ticket_reply_rate_tier_id` INT AFTER `ticket_reply_rate`;
ALTER TABLE `ticket_replies` ADD COLUMN `ticket_reply_deduct_from_agreement` TINYINT(1) DEFAULT 0;

-- Modify invoices table
ALTER TABLE `invoices` ADD COLUMN `invoice_agreement_id` INT AFTER `invoice_recurring_invoice_id`;
ALTER TABLE `invoices` ADD INDEX `invoice_agreement_id` (`invoice_agreement_id`);
ALTER TABLE `invoices` ADD FOREIGN KEY `fk_invoice_agreement` (`invoice_agreement_id`) REFERENCES `agreements`(`agreement_id`) ON DELETE SET NULL;

-- Modify clients table (optional fields)
ALTER TABLE `clients` ADD COLUMN `client_default_agreement_type` VARCHAR(50) AFTER `client_rate`;
ALTER TABLE `clients` ADD COLUMN `client_preferred_agreement_term` INT DEFAULT 12 AFTER `client_default_agreement_type`;

-- Create view for agreement utilization reporting
CREATE OR REPLACE VIEW `view_agreement_utilization` AS
SELECT
    a.agreement_id,
    a.agreement_name,
    a.agreement_type,
    a.agreement_status,
    c.client_name,
    a.agreement_hours_included,
    a.agreement_hours_used,
    a.agreement_hours_remaining,
    ROUND((a.agreement_hours_used / a.agreement_hours_included * 100), 2) AS utilization_percentage,
    COUNT(t.ticket_id) AS total_tickets,
    DATEDIFF(a.agreement_end_date, CURDATE()) AS days_until_expiration
FROM agreements a
LEFT JOIN clients c ON a.agreement_client_id = c.client_id
LEFT JOIN tickets t ON t.ticket_agreement_id = a.agreement_id
WHERE a.agreement_type LIKE 'Block Hours%'
GROUP BY a.agreement_id;
