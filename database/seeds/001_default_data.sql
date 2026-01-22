-- Standard-Daten für das Rechnungsprogramm
-- Diese Daten werden bei der ersten Installation eingefügt

-- Standard Admin-Benutzer (Passwort: admin123 - BITTE ÄNDERN!)
-- Passwort-Hash für "admin123" mit bcrypt
INSERT INTO `users` (`username`, `email`, `password`, `first_name`, `last_name`, `role`, `is_active`) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 1);

-- Standard-Mandant
INSERT INTO `clients` (`name`, `legal_form`, `street`, `zip`, `city`, `country`, `phone`, `email`, `website`, `tax_id`, `vat_id`, `register_number`, `bank_name`, `iban`, `bic`, `is_active`) VALUES
('Muster GmbH', 'GmbH', 'Musterstraße 123', '12345', 'Berlin', 'Deutschland', '+49 30 12345678', 'info@muster-gmbh.de', 'www.muster-gmbh.de', 'DE123456789', 'DE123456789', 'HRB 12345 B', 'Musterbank', 'DE89370400440532013000', 'COBADEFFXXX', 1);

-- Standard-Steuersätze
INSERT INTO `tax_rates` (`client_id`, `name`, `rate`, `is_default`, `is_active`) VALUES
(1, '19% MwSt.', 19.00, 1, 1),
(1, '7% MwSt. (ermäßigt)', 7.00, 0, 1),
(1, '0% MwSt. (steuerfrei)', 0.00, 0, 1);

-- Standard-Nummernkreise
INSERT INTO `number_ranges` (`client_id`, `type`, `name`, `prefix`, `current_number`, `digits`, `year_separator`, `is_default`) VALUES
(1, 'invoice', 'Rechnungen', 'RE', 0, 5, 1, 1),
(1, 'offer', 'Angebote', 'ANG', 0, 5, 1, 1),
(1, 'credit', 'Gutschriften', 'GS', 0, 5, 1, 1),
(1, 'delivery', 'Lieferscheine', 'LS', 0, 5, 1, 1),
(1, 'order', 'Auftragsbestätigungen', 'AB', 0, 5, 1, 1),
(1, 'customer', 'Kunden', 'KD', 0, 5, 0, 1),
(1, 'product', 'Produkte', 'ART', 0, 5, 0, 1);

-- Beispiel Produktkategorien
INSERT INTO `product_categories` (`client_id`, `name`, `description`) VALUES
(1, 'Dienstleistungen', 'Alle Dienstleistungen'),
(1, 'Produkte', 'Physische Produkte'),
(1, 'Beratung', 'Beratungsleistungen');

-- Beispiel-Produkte
INSERT INTO `products` (`client_id`, `category_id`, `product_number`, `name`, `description`, `product_type`, `unit`, `price_net`, `tax_rate_id`, `is_active`) VALUES
(1, 1, 'ART-00001', 'Beratungsstunde', 'Professionelle IT-Beratung', 'service', 'Stunde', 120.00, 1, 1),
(1, 1, 'ART-00002', 'Entwicklungsstunde', 'Software-Entwicklung', 'service', 'Stunde', 95.00, 1, 1),
(1, 2, 'ART-00003', 'Standard-Lizenz', 'Jahreslizenz für Software', 'product', 'Stück', 299.00, 1, 1);

-- Standard Email-Vorlagen
INSERT INTO `email_templates` (`client_id`, `template_type`, `name`, `subject`, `body`, `is_default`) VALUES
(1, 'invoice', 'Standard Rechnung', 'Ihre Rechnung {invoice_number}',
'Sehr geehrte/r {customer_salutation} {customer_name},

anbei erhalten Sie die Rechnung {invoice_number} vom {invoice_date} über {total_gross} EUR.

Zahlbar bis zum {due_date}.

Mit freundlichen Grüßen
{company_name}', 1),

(1, 'offer', 'Standard Angebot', 'Ihr Angebot {invoice_number}',
'Sehr geehrte/r {customer_salutation} {customer_name},

vielen Dank für Ihre Anfrage. Anbei erhalten Sie unser Angebot {invoice_number}.

Das Angebot ist gültig bis {valid_until}.

Wir freuen uns auf Ihre Rückmeldung.

Mit freundlichen Grüßen
{company_name}', 1),

(1, 'credit', 'Standard Gutschrift', 'Ihre Gutschrift {invoice_number}',
'Sehr geehrte/r {customer_salutation} {customer_name},

anbei erhalten Sie die Gutschrift {invoice_number} über {total_gross} EUR.

Mit freundlichen Grüßen
{company_name}', 1),

(1, 'reminder', 'Zahlungserinnerung', 'Zahlungserinnerung für Rechnung {invoice_number}',
'Sehr geehrte/r {customer_salutation} {customer_name},

zu der Rechnung {invoice_number} vom {invoice_date} über {total_gross} EUR konnten wir bisher keinen Zahlungseingang feststellen.

Sollten Sie die Zahlung bereits veranlasst haben, betrachten Sie dieses Schreiben bitte als gegenstandslos.

Andernfalls bitten wir Sie, den offenen Betrag zeitnah zu begleichen.

Mit freundlichen Grüßen
{company_name}', 1);

-- Beispiel-Kunde
INSERT INTO `customers` (`client_id`, `customer_number`, `customer_type`, `company_name`, `first_name`, `last_name`, `email`, `phone`, `tax_region`, `billing_street`, `billing_zip`, `billing_city`, `billing_country`, `payment_terms_days`, `is_active`) VALUES
(1, 'KD-00001', 'b2b', 'Beispiel AG', 'Max', 'Mustermann', 'max.mustermann@beispiel-ag.de', '+49 40 98765432', 'domestic', 'Beispielweg 456', '20095', 'Hamburg', 'Deutschland', 14, 1);

-- Standard-Einstellungen
INSERT INTO `settings` (`client_id`, `setting_key`, `setting_value`, `setting_type`) VALUES
(1, 'currency', 'EUR', 'string'),
(1, 'date_format', 'd.m.Y', 'string'),
(1, 'time_format', 'H:i', 'string'),
(1, 'decimal_separator', ',', 'string'),
(1, 'thousands_separator', '.', 'string'),
(1, 'invoice_auto_send', '0', 'boolean'),
(1, 'reminder_auto_send', '0', 'boolean'),
(1, 'reminder_fee_1', '5.00', 'string'),
(1, 'reminder_fee_2', '10.00', 'string'),
(1, 'reminder_fee_3', '15.00', 'string'),
(1, 'interest_rate', '5.00', 'string');

-- Globale Einstellungen (client_id = NULL)
INSERT INTO `settings` (`client_id`, `setting_key`, `setting_value`, `setting_type`) VALUES
(NULL, 'app_version', '1.0.0', 'string'),
(NULL, 'maintenance_mode', '0', 'boolean');
