<?php
namespace Libs;

use TCPDF;

/**
 * PDF Generator für Rechnungen
 * Nutzt TCPDF Library
 */
class PdfGenerator {
    private $pdf;
    private $companyData;

    public function __construct($companyData = []) {
        $this->companyData = $companyData;
        $this->initPdf();
    }

    /**
     * Initialisiere PDF
     */
    private function initPdf() {
        // PDF erstellen
        $this->pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Dokumentinformationen
        $this->pdf->SetCreator('Rechnungsprogramm');
        $this->pdf->SetAuthor($this->companyData['name'] ?? 'Firma');

        // Header und Footer deaktivieren (wir erstellen eigene)
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // Margins
        $this->pdf->SetMargins(20, 20, 20);
        $this->pdf->SetAutoPageBreak(true, 25);

        // Font
        $this->pdf->SetFont('helvetica', '', 10);
    }

    /**
     * Generiere Rechnungs-PDF
     */
    public function generateInvoice($invoice, $customer, $items) {
        $this->pdf->AddPage();

        // Logo (falls vorhanden)
        if (!empty($this->companyData['logo_path']) && file_exists($this->companyData['logo_path'])) {
            $this->pdf->Image($this->companyData['logo_path'], 20, 15, 40, 0, '', '', '', false, 300, '', false, false, 0);
            $yStart = 45;
        } else {
            $yStart = 20;
        }

        // Firmenadresse (rechts oben)
        $this->addCompanyAddress($yStart);

        // Kundenadresse
        $this->addCustomerAddress($customer, $yStart + 40);

        // Rechnungsdetails (Tabelle rechts)
        $this->addInvoiceDetails($invoice, $yStart + 40);

        // Betreff
        $this->pdf->SetY($yStart + 85);
        if (!empty($invoice['subject'])) {
            $this->pdf->SetFont('helvetica', 'B', 12);
            $this->pdf->Cell(0, 10, $invoice['subject'], 0, 1);
        } else {
            $this->pdf->SetFont('helvetica', 'B', 12);
            $this->pdf->Cell(0, 10, 'Rechnung ' . $invoice['invoice_number'], 0, 1);
        }

        // Einleitungstext
        $this->pdf->SetY($this->pdf->GetY() + 5);
        if (!empty($invoice['intro_text'])) {
            $this->pdf->SetFont('helvetica', '', 10);
            $this->pdf->MultiCell(0, 5, $invoice['intro_text'], 0, 'L');
        }

        // Positionen Tabelle
        $this->pdf->SetY($this->pdf->GetY() + 5);
        $this->addItemsTable($items);

        // Summen
        $this->addTotals($invoice);

        // Schlusstext
        if (!empty($invoice['outro_text'])) {
            $this->pdf->SetY($this->pdf->GetY() + 10);
            $this->pdf->SetFont('helvetica', '', 10);
            $this->pdf->MultiCell(0, 5, $invoice['outro_text'], 0, 'L');
        }

        // Zahlungsinformationen
        $this->addPaymentInfo($invoice);

        // Kleingedrucktes / Footer
        $this->addFooter();

        return $this->pdf;
    }

    /**
     * Firmenadresse hinzufügen
     */
    private function addCompanyAddress($y) {
        $this->pdf->SetXY(110, $y);
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 5, $this->companyData['name'] ?? '', 0, 1, 'R');

        $this->pdf->SetX(110);
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 4, ($this->companyData['street'] ?? ''), 0, 1, 'R');

        $this->pdf->SetX(110);
        $this->pdf->Cell(0, 4, ($this->companyData['zip'] ?? '') . ' ' . ($this->companyData['city'] ?? ''), 0, 1, 'R');

        if (!empty($this->companyData['phone'])) {
            $this->pdf->SetX(110);
            $this->pdf->Cell(0, 4, 'Tel: ' . $this->companyData['phone'], 0, 1, 'R');
        }

        if (!empty($this->companyData['email'])) {
            $this->pdf->SetX(110);
            $this->pdf->Cell(0, 4, 'Email: ' . $this->companyData['email'], 0, 1, 'R');
        }
    }

    /**
     * Kundenadresse hinzufügen
     */
    private function addCustomerAddress($customer, $y) {
        $this->pdf->SetXY(20, $y);

        // Absenderzeile (klein)
        $this->pdf->SetFont('helvetica', '', 7);
        $absender = ($this->companyData['name'] ?? '') . ' • ' .
                    ($this->companyData['street'] ?? '') . ' • ' .
                    ($this->companyData['zip'] ?? '') . ' ' . ($this->companyData['city'] ?? '');
        $this->pdf->Cell(0, 3, $absender, 0, 1);

        $this->pdf->Ln(3);

        // Kundenadresse
        $this->pdf->SetFont('helvetica', '', 11);

        if ($customer['customer_type'] === 'b2b' && !empty($customer['company_name'])) {
            $this->pdf->Cell(0, 5, $customer['company_name'], 0, 1);
        }

        $name = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
        if (!empty($name)) {
            $this->pdf->Cell(0, 5, $name, 0, 1);
        }

        $this->pdf->Cell(0, 5, $customer['billing_street'] ?? '', 0, 1);
        $this->pdf->Cell(0, 5, ($customer['billing_zip'] ?? '') . ' ' . ($customer['billing_city'] ?? ''), 0, 1);

        if (!empty($customer['billing_country']) && $customer['billing_country'] !== 'Deutschland') {
            $this->pdf->Cell(0, 5, $customer['billing_country'], 0, 1);
        }
    }

    /**
     * Rechnungsdetails hinzufügen
     */
    private function addInvoiceDetails($invoice, $y) {
        $this->pdf->SetXY(110, $y + 25);
        $this->pdf->SetFont('helvetica', '', 9);

        $details = [
            ['label' => 'Rechnungsnr.:', 'value' => $invoice['invoice_number']],
            ['label' => 'Rechnungsdatum:', 'value' => date('d.m.Y', strtotime($invoice['invoice_date']))],
        ];

        if (!empty($invoice['delivery_date'])) {
            $details[] = ['label' => 'Leistungsdatum:', 'value' => date('d.m.Y', strtotime($invoice['delivery_date']))];
        }

        if (!empty($invoice['due_date'])) {
            $details[] = ['label' => 'Fällig am:', 'value' => date('d.m.Y', strtotime($invoice['due_date']))];
        }

        foreach ($details as $detail) {
            $this->pdf->SetX(110);
            $this->pdf->Cell(40, 4, $detail['label'], 0, 0, 'L');
            $this->pdf->Cell(0, 4, $detail['value'], 0, 1, 'R');
        }
    }

    /**
     * Positionen-Tabelle hinzufügen
     */
    private function addItemsTable($items) {
        // Tabellenkopf
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(240, 240, 240);

        $this->pdf->Cell(10, 7, 'Pos.', 1, 0, 'C', true);
        $this->pdf->Cell(80, 7, 'Bezeichnung', 1, 0, 'L', true);
        $this->pdf->Cell(20, 7, 'Menge', 1, 0, 'C', true);
        $this->pdf->Cell(25, 7, 'Einzelpreis', 1, 0, 'R', true);
        $this->pdf->Cell(15, 7, 'MwSt.', 1, 0, 'C', true);
        $this->pdf->Cell(25, 7, 'Gesamt', 1, 1, 'R', true);

        // Positionen
        $this->pdf->SetFont('helvetica', '', 9);
        $position = 1;

        foreach ($items as $item) {
            $y = $this->pdf->GetY();

            // Beschreibungstext vorbereiten
            $description = $item['name'];
            if (!empty($item['description'])) {
                $description .= "\n" . $item['description'];
            }

            // Höhe für Beschreibung berechnen
            $nb = $this->pdf->getStringHeight(80, $description);
            $height = max(7, $nb);

            // Position
            $this->pdf->MultiCell(10, $height, $position, 1, 'C', false, 0);

            // Bezeichnung
            $this->pdf->MultiCell(80, $height, $description, 1, 'L', false, 0);

            // Menge
            $qty = number_format($item['quantity'], 2, ',', '.') . ' ' . $item['unit'];
            $this->pdf->MultiCell(20, $height, $qty, 1, 'C', false, 0);

            // Einzelpreis
            $price = number_format($item['price_net'], 2, ',', '.') . ' €';
            if (!empty($item['discount_percent']) && $item['discount_percent'] > 0) {
                $price .= "\n-" . number_format($item['discount_percent'], 2, ',', '.') . '%';
            }
            $this->pdf->MultiCell(25, $height, $price, 1, 'R', false, 0);

            // MwSt
            $this->pdf->MultiCell(15, $height, number_format($item['tax_rate'], 0) . '%', 1, 'C', false, 0);

            // Gesamt
            $total = number_format($item['total_gross'], 2, ',', '.') . ' €';
            $this->pdf->MultiCell(25, $height, $total, 1, 'R', false, 1);

            $position++;
        }
    }

    /**
     * Summen hinzufügen
     */
    private function addTotals($invoice) {
        $this->pdf->Ln(2);
        $this->pdf->SetFont('helvetica', '', 10);

        // Zwischensumme
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(25, 6, 'Zwischensumme (Netto):', 0, 0, 'L');
        $this->pdf->Cell(25, 6, number_format($invoice['total_net'], 2, ',', '.') . ' €', 0, 1, 'R');

        // MwSt
        $this->pdf->Cell(130, 6, '', 0, 0);
        $this->pdf->Cell(25, 6, 'MwSt. gesamt:', 0, 0, 'L');
        $this->pdf->Cell(25, 6, number_format($invoice['total_tax'], 2, ',', '.') . ' €', 0, 1, 'R');

        // Gesamtbetrag
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(130, 8, '', 0, 0);
        $this->pdf->Cell(25, 8, 'Gesamtbetrag:', 1, 0, 'L', true);
        $this->pdf->Cell(25, 8, number_format($invoice['total_gross'], 2, ',', '.') . ' €', 1, 1, 'R', true);
    }

    /**
     * Zahlungsinformationen hinzufügen
     */
    private function addPaymentInfo($invoice) {
        $this->pdf->Ln(10);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 5, 'Zahlungsinformationen', 0, 1);

        $this->pdf->SetFont('helvetica', '', 9);

        $paymentText = 'Bitte überweisen Sie den Betrag innerhalb von ' . $invoice['payment_terms_days'] . ' Tagen ';

        if (!empty($invoice['skonto_days']) && !empty($invoice['skonto_percent'])) {
            $skontoAmount = $invoice['total_gross'] * ($invoice['skonto_percent'] / 100);
            $skontoTotal = $invoice['total_gross'] - $skontoAmount;
            $paymentText .= 'oder ' . number_format($skontoTotal, 2, ',', '.') . ' € bei Zahlung innerhalb von ' .
                           $invoice['skonto_days'] . ' Tagen (' . number_format($invoice['skonto_percent'], 2, ',', '.') . '% Skonto)';
        }

        $paymentText .= ' auf folgendes Konto:';

        $this->pdf->MultiCell(0, 5, $paymentText, 0, 'L');
        $this->pdf->Ln(2);

        if (!empty($this->companyData['bank_name'])) {
            $this->pdf->Cell(40, 4, 'Bank:', 0, 0);
            $this->pdf->Cell(0, 4, $this->companyData['bank_name'], 0, 1);
        }

        if (!empty($this->companyData['iban'])) {
            $this->pdf->Cell(40, 4, 'IBAN:', 0, 0);
            $this->pdf->Cell(0, 4, $this->companyData['iban'], 0, 1);
        }

        if (!empty($this->companyData['bic'])) {
            $this->pdf->Cell(40, 4, 'BIC:', 0, 0);
            $this->pdf->Cell(0, 4, $this->companyData['bic'], 0, 1);
        }

        $this->pdf->Ln(2);
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->Cell(0, 4, 'Verwendungszweck: ' . $invoice['invoice_number'], 0, 1);
    }

    /**
     * Footer hinzufügen
     */
    private function addFooter() {
        $this->pdf->SetY(-20);
        $this->pdf->SetFont('helvetica', '', 7);
        $this->pdf->SetTextColor(100, 100, 100);

        // Drei Spalten
        $colWidth = 60;

        $footer1 = ($this->companyData['name'] ?? '') . "\n" .
                   ($this->companyData['street'] ?? '') . "\n" .
                   ($this->companyData['zip'] ?? '') . ' ' . ($this->companyData['city'] ?? '');

        $footer2 = "Geschäftsführer: \n" .
                   "Registergericht: " . ($this->companyData['register_court'] ?? '') . "\n" .
                   "Register-Nr: " . ($this->companyData['register_number'] ?? '');

        $footer3 = "Steuernummer: " . ($this->companyData['tax_id'] ?? '') . "\n" .
                   "USt-IdNr: " . ($this->companyData['vat_id'] ?? '') . "\n" .
                   "Bank: " . ($this->companyData['bank_name'] ?? '');

        $this->pdf->MultiCell($colWidth, 3, $footer1, 0, 'L', false, 0);
        $this->pdf->MultiCell($colWidth, 3, $footer2, 0, 'L', false, 0);
        $this->pdf->MultiCell($colWidth, 3, $footer3, 0, 'L', false, 1);
    }

    /**
     * Generiere Angebots-PDF
     */
    public function generateOffer($offer, $customer, $items) {
        $this->pdf->AddPage();

        // Logo (falls vorhanden)
        if (!empty($this->companyData['logo_path']) && file_exists($this->companyData['logo_path'])) {
            $this->pdf->Image($this->companyData['logo_path'], 20, 15, 40, 0, '', '', '', false, 300, '', false, false, 0);
            $yStart = 45;
        } else {
            $yStart = 20;
        }

        // Firmenadresse (rechts oben)
        $this->addCompanyAddress($yStart);

        // Kundenadresse
        $this->addCustomerAddress($customer, $yStart + 40);

        // Angebotsdetails (Tabelle rechts)
        $this->addOfferDetails($offer, $yStart + 40);

        // Betreff
        $this->pdf->SetY($yStart + 85);
        if (!empty($offer['subject'])) {
            $this->pdf->SetFont('helvetica', 'B', 12);
            $this->pdf->Cell(0, 10, $offer['subject'], 0, 1);
        } else {
            $this->pdf->SetFont('helvetica', 'B', 12);
            $this->pdf->Cell(0, 10, 'Angebot ' . $offer['invoice_number'], 0, 1);
        }

        // Einleitungstext
        $this->pdf->SetY($this->pdf->GetY() + 5);
        if (!empty($offer['intro_text'])) {
            $this->pdf->SetFont('helvetica', '', 10);
            $this->pdf->MultiCell(0, 5, $offer['intro_text'], 0, 'L');
        } else {
            $this->pdf->SetFont('helvetica', '', 10);
            $this->pdf->MultiCell(0, 5, 'Vielen Dank für Ihre Anfrage. Gerne unterbreiten wir Ihnen folgendes Angebot:', 0, 'L');
        }

        // Positionen Tabelle (mit optionalen Items)
        $this->pdf->SetY($this->pdf->GetY() + 5);
        $this->addOfferItemsTable($items);

        // Summen
        $this->addTotals($offer);

        // Schlusstext
        if (!empty($offer['outro_text'])) {
            $this->pdf->SetY($this->pdf->GetY() + 10);
            $this->pdf->SetFont('helvetica', '', 10);
            $this->pdf->MultiCell(0, 5, $offer['outro_text'], 0, 'L');
        } else {
            $this->pdf->SetY($this->pdf->GetY() + 10);
            $this->pdf->SetFont('helvetica', '', 10);
            $validUntil = !empty($offer['valid_until']) ? date('d.m.Y', strtotime($offer['valid_until'])) : 'auf Anfrage';
            $this->pdf->MultiCell(0, 5, 'Dieses Angebot ist gültig bis zum ' . $validUntil . '. Wir freuen uns auf Ihre Bestellung!', 0, 'L');
        }

        // Kleingedrucktes / Footer
        $this->addFooter();

        return $this->pdf;
    }

    /**
     * Angebotsdetails hinzufügen
     */
    private function addOfferDetails($offer, $y) {
        $this->pdf->SetXY(110, $y + 25);
        $this->pdf->SetFont('helvetica', '', 9);

        $details = [
            ['label' => 'Angebotsnr.:', 'value' => $offer['invoice_number']],
            ['label' => 'Angebotsdatum:', 'value' => date('d.m.Y', strtotime($offer['invoice_date']))],
        ];

        if (!empty($offer['valid_until'])) {
            $details[] = ['label' => 'Gültig bis:', 'value' => date('d.m.Y', strtotime($offer['valid_until']))];
        }

        foreach ($details as $detail) {
            $this->pdf->SetX(110);
            $this->pdf->Cell(40, 4, $detail['label'], 0, 0, 'L');
            $this->pdf->Cell(0, 4, $detail['value'], 0, 1, 'R');
        }
    }

    /**
     * Positionen-Tabelle für Angebote hinzufügen (mit optionalen Items)
     */
    private function addOfferItemsTable($items) {
        // Tabellenkopf
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(240, 240, 240);

        $this->pdf->Cell(10, 7, 'Pos.', 1, 0, 'C', true);
        $this->pdf->Cell(75, 7, 'Bezeichnung', 1, 0, 'L', true);
        $this->pdf->Cell(20, 7, 'Menge', 1, 0, 'C', true);
        $this->pdf->Cell(25, 7, 'Einzelpreis', 1, 0, 'R', true);
        $this->pdf->Cell(15, 7, 'MwSt.', 1, 0, 'C', true);
        $this->pdf->Cell(25, 7, 'Gesamt', 1, 1, 'R', true);

        // Positionen
        $this->pdf->SetFont('helvetica', '', 9);
        $position = 1;

        foreach ($items as $item) {
            $y = $this->pdf->GetY();

            // Beschreibungstext vorbereiten
            $description = $item['name'];
            if (!empty($item['description'])) {
                $description .= "\n" . $item['description'];
            }
            // Optional-Markierung
            if (!empty($item['is_optional']) && $item['is_optional'] == 1) {
                $description .= "\n(Optional)";
            }

            // Höhe für Beschreibung berechnen
            $nb = $this->pdf->getStringHeight(75, $description);
            $height = max(7, $nb);

            // Position
            $this->pdf->MultiCell(10, $height, $position, 1, 'C', false, 0);

            // Bezeichnung
            $this->pdf->MultiCell(75, $height, $description, 1, 'L', false, 0);

            // Menge
            $qty = number_format($item['quantity'], 2, ',', '.') . ' ' . $item['unit'];
            $this->pdf->MultiCell(20, $height, $qty, 1, 'C', false, 0);

            // Einzelpreis
            $price = number_format($item['price_net'], 2, ',', '.') . ' €';
            if (!empty($item['discount_percent']) && $item['discount_percent'] > 0) {
                $price .= "\n-" . number_format($item['discount_percent'], 2, ',', '.') . '%';
            }
            $this->pdf->MultiCell(25, $height, $price, 1, 'R', false, 0);

            // MwSt
            $this->pdf->MultiCell(15, $height, number_format($item['tax_rate'], 0) . '%', 1, 'C', false, 0);

            // Gesamt
            $total = number_format($item['total_gross'], 2, ',', '.') . ' €';
            $this->pdf->MultiCell(25, $height, $total, 1, 'R', false, 1);

            $position++;
        }
    }

    /**
     * PDF als String ausgeben
     */
    public function output($filename = 'rechnung.pdf', $destination = 'I') {
        return $this->pdf->Output($filename, $destination);
    }

    /**
     * PDF speichern
     */
    public function save($filepath) {
        return $this->pdf->Output($filepath, 'F');
    }
}
