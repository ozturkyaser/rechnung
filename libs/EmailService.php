<?php
namespace Libs;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Email Service
 * Nutzt PHPMailer für Email-Versand
 */
class EmailService {
    private $mailer;
    private $db;

    public function __construct() {
        $this->mailer = new PHPMailer(true);
        $this->db = Database::getInstance();
        $this->configure();
    }

    /**
     * Konfiguriere PHPMailer
     */
    private function configure() {
        try {
            // Server Einstellungen
            $this->mailer->isSMTP();
            $this->mailer->Host = MAIL_HOST;
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = MAIL_USERNAME;
            $this->mailer->Password = MAIL_PASSWORD;
            $this->mailer->SMTPSecure = MAIL_ENCRYPTION;
            $this->mailer->Port = MAIL_PORT;

            // Absender
            $this->mailer->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);

            // Charset
            $this->mailer->CharSet = 'UTF-8';

        } catch (Exception $e) {
            logMessage("Email configuration error: " . $e->getMessage(), 'error');
        }
    }

    /**
     * Sende Rechnung per Email
     */
    public function sendInvoice($invoice, $customer, $pdfPath = null) {
        try {
            // Hole Email-Template
            $template = $this->getTemplate('invoice', 1);

            if (!$template) {
                throw new Exception('Email-Template nicht gefunden');
            }

            // Empfänger
            $this->mailer->addAddress($customer['email'], $this->getCustomerName($customer));

            // Betreff
            $subject = $this->replacePlaceholders($template['subject'], $invoice, $customer);
            $this->mailer->Subject = $subject;

            // Email-Body
            $body = $this->replacePlaceholders($template['body'], $invoice, $customer);
            $this->mailer->Body = nl2br($body);
            $this->mailer->AltBody = $body;

            // PDF anhängen (falls vorhanden)
            if ($pdfPath && file_exists($pdfPath)) {
                $this->mailer->addAttachment($pdfPath, 'Rechnung_' . $invoice['invoice_number'] . '.pdf');
            }

            // BCC (optional)
            if (defined('MAIL_BCC_ADDRESS') && !empty(MAIL_BCC_ADDRESS)) {
                $this->mailer->addBCC(MAIL_BCC_ADDRESS);
            }

            // Sende Email
            $sent = $this->mailer->send();

            if ($sent) {
                // Logge Email
                $this->logEmail($invoice['id'], $customer['id'], $template['id'], $customer['email'], $subject, $body, 'sent');

                // Update Invoice Status
                if ($invoice['invoice_status'] === 'draft') {
                    $this->db->execute("UPDATE invoices SET invoice_status = 'sent' WHERE id = ?", [$invoice['id']]);
                }
            }

            return ['success' => true, 'message' => 'Email erfolgreich versendet'];

        } catch (Exception $e) {
            logMessage("Email send error: " . $e->getMessage(), 'error');

            // Logge Fehler
            $this->logEmail(
                $invoice['id'] ?? null,
                $customer['id'] ?? null,
                null,
                $customer['email'] ?? null,
                $subject ?? 'Error',
                $body ?? '',
                'failed',
                $e->getMessage()
            );

            return ['success' => false, 'message' => 'Fehler beim Versenden: ' . $e->getMessage()];
        } finally {
            // Clear für nächste Email
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
        }
    }

    /**
     * Sende Angebot per Email
     */
    public function sendOffer($offer, $customer, $pdfPath = null) {
        try {
            $template = $this->getTemplate('offer', 1);

            if (!$template) {
                throw new Exception('Email-Template nicht gefunden');
            }

            $this->mailer->addAddress($customer['email'], $this->getCustomerName($customer));

            $subject = $this->replacePlaceholders($template['subject'], $offer, $customer);
            $this->mailer->Subject = $subject;

            $body = $this->replacePlaceholders($template['body'], $offer, $customer);
            $this->mailer->Body = nl2br($body);
            $this->mailer->AltBody = $body;

            if ($pdfPath && file_exists($pdfPath)) {
                $this->mailer->addAttachment($pdfPath, 'Angebot_' . $offer['invoice_number'] . '.pdf');
            }

            $sent = $this->mailer->send();

            if ($sent) {
                $this->logEmail($offer['id'], $customer['id'], $template['id'], $customer['email'], $subject, $body, 'sent');

                if ($offer['invoice_status'] === 'draft') {
                    $this->db->execute("UPDATE invoices SET invoice_status = 'sent' WHERE id = ?", [$offer['id']]);
                }
            }

            return ['success' => true, 'message' => 'Email erfolgreich versendet'];

        } catch (Exception $e) {
            logMessage("Email send error: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Fehler beim Versenden: ' . $e->getMessage()];
        } finally {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
        }
    }

    /**
     * Sende Mahnung per Email
     */
    public function sendReminder($invoice, $customer, $reminder, $pdfPath = null) {
        try {
            $template = $this->getTemplate('reminder', 1);

            if (!$template) {
                throw new Exception('Email-Template nicht gefunden');
            }

            $this->mailer->addAddress($customer['email'], $this->getCustomerName($customer));

            // Erweitere Daten für Mahnung
            $data = array_merge($invoice, [
                'reminder_level' => $reminder['reminder_level'],
                'reminder_fee' => $reminder['reminder_fee'],
                'total_with_fee' => $reminder['total_amount']
            ]);

            $subject = $this->replacePlaceholders($template['subject'], $data, $customer);
            $this->mailer->Subject = $subject;

            $body = $this->replacePlaceholders($template['body'], $data, $customer);
            $this->mailer->Body = nl2br($body);
            $this->mailer->AltBody = $body;

            if ($pdfPath && file_exists($pdfPath)) {
                $this->mailer->addAttachment($pdfPath, 'Mahnung_' . $invoice['invoice_number'] . '.pdf');
            }

            $sent = $this->mailer->send();

            if ($sent) {
                $this->logEmail($invoice['id'], $customer['id'], $template['id'], $customer['email'], $subject, $body, 'sent');
                $this->db->execute("UPDATE reminders SET sent_at = NOW(), sent_to = ? WHERE id = ?", [
                    $customer['email'],
                    $reminder['id']
                ]);
            }

            return ['success' => true, 'message' => 'Mahnung erfolgreich versendet'];

        } catch (Exception $e) {
            logMessage("Email send error: " . $e->getMessage(), 'error');
            return ['success' => false, 'message' => 'Fehler beim Versenden: ' . $e->getMessage()];
        } finally {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
        }
    }

    /**
     * Hole Email-Template
     */
    private function getTemplate($type, $clientId) {
        $sql = "SELECT * FROM email_templates
                WHERE client_id = ? AND template_type = ? AND is_default = 1
                LIMIT 1";

        return $this->db->fetchOne($sql, [$clientId, $type]);
    }

    /**
     * Ersetze Platzhalter im Template
     */
    private function replacePlaceholders($text, $invoice, $customer) {
        $placeholders = [
            '{invoice_number}' => $invoice['invoice_number'] ?? '',
            '{invoice_date}' => !empty($invoice['invoice_date']) ? date('d.m.Y', strtotime($invoice['invoice_date'])) : '',
            '{due_date}' => !empty($invoice['due_date']) ? date('d.m.Y', strtotime($invoice['due_date'])) : '',
            '{valid_until}' => !empty($invoice['offer_valid_until']) ? date('d.m.Y', strtotime($invoice['offer_valid_until'])) : '',
            '{total_gross}' => number_format($invoice['total_gross'] ?? 0, 2, ',', '.'),
            '{total_net}' => number_format($invoice['total_net'] ?? 0, 2, ',', '.'),
            '{customer_name}' => $this->getCustomerName($customer),
            '{customer_number}' => $customer['customer_number'] ?? '',
            '{customer_salutation}' => $this->getCustomerSalutation($customer),
            '{company_name}' => COMPANY_NAME,
            '{reminder_level}' => $invoice['reminder_level'] ?? '',
            '{reminder_fee}' => !empty($invoice['reminder_fee']) ? number_format($invoice['reminder_fee'], 2, ',', '.') : '0,00',
            '{total_with_fee}' => !empty($invoice['total_with_fee']) ? number_format($invoice['total_with_fee'], 2, ',', '.') : '0,00'
        ];

        return str_replace(array_keys($placeholders), array_values($placeholders), $text);
    }

    /**
     * Hole Kundenname
     */
    private function getCustomerName($customer) {
        if ($customer['customer_type'] === 'b2b' && !empty($customer['company_name'])) {
            return $customer['company_name'];
        }

        return trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
    }

    /**
     * Hole Kundenanrede
     */
    private function getCustomerSalutation($customer) {
        if ($customer['customer_type'] === 'b2b') {
            return 'Damen und Herren';
        }

        // Sehr geehrte/r basierend auf Vorname (vereinfacht)
        return 'Damen und Herren';
    }

    /**
     * Logge Email
     */
    private function logEmail($invoiceId, $customerId, $templateId, $email, $subject, $body, $status, $error = null) {
        $sql = "INSERT INTO email_log (invoice_id, customer_id, template_id, recipient_email, subject, body, status, error_message, sent_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, " . ($status === 'sent' ? 'NOW()' : 'NULL') . ")";

        $this->db->execute($sql, [
            $invoiceId,
            $customerId,
            $templateId,
            $email,
            $subject,
            $body,
            $status,
            $error
        ]);
    }

    /**
     * Test Email Connection
     */
    public function testConnection() {
        try {
            $this->mailer->SMTPDebug = 2;
            return $this->mailer->smtpConnect();
        } catch (Exception $e) {
            return false;
        }
    }
}
