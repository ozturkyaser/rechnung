-- Rechnungsprogramm - Datenbank Schema
-- Version 1.0
-- Charset: UTF8MB4 für volle Unicode-Unterstützung

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Tabelle: users (Benutzerverwaltung)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) DEFAULT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('admin', 'accounting', 'sales', 'readonly') NOT NULL DEFAULT 'readonly',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `last_login` DATETIME DEFAULT NULL,
  `two_factor_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `two_factor_secret` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: clients (Mandanten/Firmen)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clients` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(200) NOT NULL,
  `legal_form` VARCHAR(50) DEFAULT NULL,
  `street` VARCHAR(200) DEFAULT NULL,
  `zip` VARCHAR(20) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `country` VARCHAR(100) DEFAULT 'Deutschland',
  `phone` VARCHAR(50) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `website` VARCHAR(200) DEFAULT NULL,
  `tax_id` VARCHAR(50) DEFAULT NULL,
  `vat_id` VARCHAR(50) DEFAULT NULL,
  `register_number` VARCHAR(100) DEFAULT NULL,
  `register_court` VARCHAR(100) DEFAULT NULL,
  `bank_name` VARCHAR(200) DEFAULT NULL,
  `iban` VARCHAR(34) DEFAULT NULL,
  `bic` VARCHAR(11) DEFAULT NULL,
  `logo_path` VARCHAR(255) DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: customers (Kunden)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `customer_number` VARCHAR(50) NOT NULL,
  `customer_type` ENUM('b2b', 'b2c') NOT NULL DEFAULT 'b2b',
  `company_name` VARCHAR(200) DEFAULT NULL,
  `first_name` VARCHAR(100) DEFAULT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `mobile` VARCHAR(50) DEFAULT NULL,
  `website` VARCHAR(200) DEFAULT NULL,
  `tax_id` VARCHAR(50) DEFAULT NULL,
  `vat_id` VARCHAR(50) DEFAULT NULL,
  `tax_region` ENUM('domestic', 'eu', 'non_eu') NOT NULL DEFAULT 'domestic',
  `billing_street` VARCHAR(200) DEFAULT NULL,
  `billing_zip` VARCHAR(20) DEFAULT NULL,
  `billing_city` VARCHAR(100) DEFAULT NULL,
  `billing_country` VARCHAR(100) DEFAULT 'Deutschland',
  `shipping_street` VARCHAR(200) DEFAULT NULL,
  `shipping_zip` VARCHAR(20) DEFAULT NULL,
  `shipping_city` VARCHAR(100) DEFAULT NULL,
  `shipping_country` VARCHAR(100) DEFAULT NULL,
  `payment_terms_days` INT DEFAULT 14,
  `skonto_days` INT DEFAULT 7,
  `skonto_percent` DECIMAL(5,2) DEFAULT 2.00,
  `credit_limit` DECIMAL(15,2) DEFAULT 0.00,
  `notes` TEXT DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `shopify_customer_id` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_number` (`client_id`, `customer_number`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_customer_type` (`customer_type`),
  KEY `idx_email` (`email`),
  KEY `idx_shopify` (`shopify_customer_id`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_customers_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: tax_rates (Steuersätze)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tax_rates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `rate` DECIMAL(5,2) NOT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_is_default` (`is_default`),
  CONSTRAINT `fk_tax_rates_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: product_categories (Produktkategorien)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `product_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `parent_id` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_parent_id` (`parent_id`),
  CONSTRAINT `fk_product_categories_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: products (Produkte/Dienstleistungen)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `product_number` VARCHAR(50) NOT NULL,
  `sku` VARCHAR(100) DEFAULT NULL,
  `ean` VARCHAR(20) DEFAULT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `product_type` ENUM('product', 'service') NOT NULL DEFAULT 'product',
  `unit` VARCHAR(20) NOT NULL DEFAULT 'Stück',
  `price_net` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `price_gross` DECIMAL(15,2) DEFAULT NULL,
  `purchase_price` DECIMAL(15,2) DEFAULT 0.00,
  `tax_rate_id` INT UNSIGNED DEFAULT NULL,
  `stock_quantity` DECIMAL(15,3) DEFAULT 0.000,
  `stock_min` DECIMAL(15,3) DEFAULT 0.000,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `shopify_product_id` BIGINT UNSIGNED DEFAULT NULL,
  `shopify_variant_id` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_number` (`client_id`, `product_number`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_tax_rate_id` (`tax_rate_id`),
  KEY `idx_sku` (`sku`),
  KEY `idx_ean` (`ean`),
  KEY `idx_shopify_product` (`shopify_product_id`),
  KEY `idx_shopify_variant` (`shopify_variant_id`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_products_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_products_tax_rate` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: number_ranges (Nummernkreise)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `number_ranges` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `type` ENUM('invoice', 'offer', 'credit', 'delivery', 'order', 'customer', 'product') NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `prefix` VARCHAR(20) DEFAULT NULL,
  `suffix` VARCHAR(20) DEFAULT NULL,
  `current_number` INT UNSIGNED NOT NULL DEFAULT 0,
  `digits` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `year_separator` TINYINT(1) NOT NULL DEFAULT 0,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_type` (`type`),
  KEY `idx_is_default` (`is_default`),
  CONSTRAINT `fk_number_ranges_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: invoices (Rechnungen/Angebote/Gutschriften)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `number_range_id` INT UNSIGNED DEFAULT NULL,
  `invoice_number` VARCHAR(100) NOT NULL,
  `invoice_type` ENUM('invoice', 'offer', 'credit', 'proforma', 'partial', 'final', 'order_confirmation', 'delivery_note') NOT NULL DEFAULT 'invoice',
  `invoice_status` ENUM('draft', 'sent', 'viewed', 'partial', 'paid', 'overdue', 'cancelled', 'accepted', 'rejected') NOT NULL DEFAULT 'draft',
  `parent_invoice_id` INT UNSIGNED DEFAULT NULL COMMENT 'Für Gutschriften, Teilrechnungen',
  `invoice_date` DATE NOT NULL,
  `due_date` DATE DEFAULT NULL,
  `delivery_date` DATE DEFAULT NULL,
  `offer_valid_until` DATE DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `intro_text` TEXT DEFAULT NULL,
  `outro_text` TEXT DEFAULT NULL,
  `subtotal_net` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
  `discount_amount` DECIMAL(15,2) DEFAULT 0.00,
  `total_net` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total_tax` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total_gross` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `payment_terms_days` INT DEFAULT 14,
  `skonto_days` INT DEFAULT NULL,
  `skonto_percent` DECIMAL(5,2) DEFAULT NULL,
  `is_reverse_charge` TINYINT(1) NOT NULL DEFAULT 0,
  `is_small_business` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '§19 UStG',
  `is_differential_taxation` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '§25a UStG',
  `leitweg_id` VARCHAR(100) DEFAULT NULL COMMENT 'Für E-Rechnung',
  `buyer_reference` VARCHAR(100) DEFAULT NULL,
  `project_name` VARCHAR(200) DEFAULT NULL,
  `pdf_path` VARCHAR(255) DEFAULT NULL,
  `xrechnung_path` VARCHAR(255) DEFAULT NULL,
  `zugferd_path` VARCHAR(255) DEFAULT NULL,
  `shopify_order_id` BIGINT UNSIGNED DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`client_id`, `invoice_number`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_number_range_id` (`number_range_id`),
  KEY `idx_parent_invoice_id` (`parent_invoice_id`),
  KEY `idx_invoice_type` (`invoice_type`),
  KEY `idx_invoice_status` (`invoice_status`),
  KEY `idx_invoice_date` (`invoice_date`),
  KEY `idx_due_date` (`due_date`),
  KEY `idx_shopify_order` (`shopify_order_id`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoices_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_invoices_number_range` FOREIGN KEY (`number_range_id`) REFERENCES `number_ranges` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_invoices_parent` FOREIGN KEY (`parent_invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_invoices_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: invoice_items (Rechnungspositionen)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED DEFAULT NULL,
  `position` INT NOT NULL DEFAULT 0,
  `item_type` ENUM('product', 'service', 'text', 'subtotal') NOT NULL DEFAULT 'product',
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `quantity` DECIMAL(15,3) NOT NULL DEFAULT 1.000,
  `unit` VARCHAR(20) DEFAULT 'Stück',
  `price_net` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `discount_percent` DECIMAL(5,2) DEFAULT 0.00,
  `tax_rate` DECIMAL(5,2) NOT NULL DEFAULT 19.00,
  `tax_rate_id` INT UNSIGNED DEFAULT NULL,
  `subtotal_net` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `total_gross` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `is_optional` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Für Angebote',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_id` (`invoice_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_tax_rate_id` (`tax_rate_id`),
  KEY `idx_position` (`position`),
  CONSTRAINT `fk_invoice_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_invoice_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_invoice_items_tax_rate` FOREIGN KEY (`tax_rate_id`) REFERENCES `tax_rates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: payments (Zahlungen)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT UNSIGNED NOT NULL,
  `payment_date` DATE NOT NULL,
  `amount` DECIMAL(15,2) NOT NULL,
  `payment_method` ENUM('bank_transfer', 'cash', 'paypal', 'stripe', 'credit_card', 'direct_debit', 'other') NOT NULL DEFAULT 'bank_transfer',
  `reference` VARCHAR(255) DEFAULT NULL COMMENT 'Verwendungszweck',
  `notes` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_id` (`invoice_id`),
  KEY `idx_payment_date` (`payment_date`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: reminders (Mahnungen)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reminders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT UNSIGNED NOT NULL,
  `reminder_level` TINYINT UNSIGNED NOT NULL COMMENT '0=Zahlungserinnerung, 1-3=Mahnstufe',
  `reminder_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `reminder_fee` DECIMAL(15,2) DEFAULT 0.00,
  `interest_amount` DECIMAL(15,2) DEFAULT 0.00,
  `total_amount` DECIMAL(15,2) NOT NULL,
  `pdf_path` VARCHAR(255) DEFAULT NULL,
  `sent_at` DATETIME DEFAULT NULL,
  `sent_to` VARCHAR(100) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_id` (`invoice_id`),
  KEY `idx_reminder_level` (`reminder_level`),
  KEY `idx_reminder_date` (`reminder_date`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_reminders_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_reminders_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: email_templates (Email-Vorlagen)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `template_type` ENUM('invoice', 'offer', 'credit', 'reminder', 'payment_confirmation', 'custom') NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_template_type` (`template_type`),
  KEY `idx_is_default` (`is_default`),
  CONSTRAINT `fk_email_templates_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: email_log (Email-Versand-Protokoll)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `email_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id` INT UNSIGNED DEFAULT NULL,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `template_id` INT UNSIGNED DEFAULT NULL,
  `recipient_email` VARCHAR(100) NOT NULL,
  `recipient_name` VARCHAR(200) DEFAULT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT DEFAULT NULL,
  `attachments` TEXT DEFAULT NULL COMMENT 'JSON array of file paths',
  `sent_at` DATETIME DEFAULT NULL,
  `status` ENUM('pending', 'sent', 'failed', 'bounced') NOT NULL DEFAULT 'pending',
  `error_message` TEXT DEFAULT NULL,
  `opened_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_invoice_id` (`invoice_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_template_id` (`template_id`),
  KEY `idx_status` (`status`),
  KEY `idx_sent_at` (`sent_at`),
  CONSTRAINT `fk_email_log_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_email_log_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_email_log_template` FOREIGN KEY (`template_id`) REFERENCES `email_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: pdf_templates (PDF-Vorlagen)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pdf_templates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `template_type` ENUM('invoice', 'offer', 'credit', 'delivery_note', 'reminder') NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `html_content` LONGTEXT NOT NULL,
  `css_content` TEXT DEFAULT NULL,
  `header_html` TEXT DEFAULT NULL,
  `footer_html` TEXT DEFAULT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_template_type` (`template_type`),
  KEY `idx_is_default` (`is_default`),
  CONSTRAINT `fk_pdf_templates_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: recurring_invoices (Wiederkehrende Rechnungen/Abos)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `recurring_invoices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED NOT NULL,
  `template_invoice_id` INT UNSIGNED DEFAULT NULL,
  `name` VARCHAR(200) NOT NULL,
  `frequency` ENUM('daily', 'weekly', 'monthly', 'quarterly', 'semi_annually', 'annually') NOT NULL DEFAULT 'monthly',
  `start_date` DATE NOT NULL,
  `end_date` DATE DEFAULT NULL,
  `next_invoice_date` DATE NOT NULL,
  `last_invoice_id` INT UNSIGNED DEFAULT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `auto_send` TINYINT(1) NOT NULL DEFAULT 0,
  `payment_method` ENUM('bank_transfer', 'direct_debit', 'paypal', 'stripe', 'other') DEFAULT 'bank_transfer',
  `notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_template_invoice_id` (`template_invoice_id`),
  KEY `idx_next_invoice_date` (`next_invoice_date`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `fk_recurring_invoices_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recurring_invoices_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_recurring_invoices_template` FOREIGN KEY (`template_invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: settings (System-Einstellungen)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL = global setting',
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `setting_type` ENUM('string', 'integer', 'boolean', 'json') NOT NULL DEFAULT 'string',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting` (`client_id`, `setting_key`),
  KEY `idx_client_id` (`client_id`),
  CONSTRAINT `fk_settings_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: audit_log (Änderungsprotokoll für GoBD)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED DEFAULT NULL,
  `table_name` VARCHAR(50) NOT NULL,
  `record_id` INT UNSIGNED NOT NULL,
  `action` ENUM('create', 'update', 'delete', 'cancel') NOT NULL,
  `old_data` LONGTEXT DEFAULT NULL COMMENT 'JSON',
  `new_data` LONGTEXT DEFAULT NULL COMMENT 'JSON',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_table_record` (`table_name`, `record_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_audit_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: shopify_sync (Shopify Synchronisations-Log)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `shopify_sync` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `sync_type` ENUM('customer', 'product', 'order', 'payment', 'fulfillment') NOT NULL,
  `shopify_id` BIGINT UNSIGNED NOT NULL,
  `local_id` INT UNSIGNED DEFAULT NULL,
  `direction` ENUM('from_shopify', 'to_shopify') NOT NULL,
  `status` ENUM('pending', 'success', 'failed') NOT NULL DEFAULT 'pending',
  `data` LONGTEXT DEFAULT NULL COMMENT 'JSON',
  `error_message` TEXT DEFAULT NULL,
  `synced_at` DATETIME DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_sync_type` (`sync_type`),
  KEY `idx_shopify_id` (`shopify_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_shopify_sync_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabelle: documents (Dokumentenmanagement)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` INT UNSIGNED NOT NULL,
  `customer_id` INT UNSIGNED DEFAULT NULL,
  `invoice_id` INT UNSIGNED DEFAULT NULL,
  `document_type` VARCHAR(50) DEFAULT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL,
  `mime_type` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `uploaded_by` INT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_client_id` (`client_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_invoice_id` (`invoice_id`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  CONSTRAINT `fk_documents_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_documents_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_documents_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_documents_uploaded_by` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
