# Professionelles Rechnungsprogramm

Ein umfassendes, webbasiertes Rechnungsprogramm entwickelt mit PHP, MySQL und JavaScript. Das System bietet vollständige Funktionen für Rechnungsstellung, Kundenverwaltung, Produktverwaltung, E-Rechnung (XRechnung/ZUGFeRD), Shopify-Integration und vieles mehr.

## Features

### Kernfunktionen
- ✅ **Kundenverwaltung** - Umfassende Verwaltung von B2B und B2C Kunden
- ✅ **Produktverwaltung** - Produkte und Dienstleistungen mit Lagerbestand
- ✅ **Rechnungserstellung** - Professionelle Rechnungen, Angebote, Gutschriften
- ✅ **PDF-Generierung** - Automatische PDF-Erstellung für alle Dokumente
- ✅ **Email-Versand** - Automatischer Versand von Rechnungen und Dokumenten
- ✅ **E-Rechnung** - XRechnung und ZUGFeRD 2.x Unterstützung
- ✅ **Zahlungsverwaltung** - Zahlungseingänge und Mahnwesen
- ✅ **Shopify-Integration** - Automatische Synchronisation mit Shopify
- ✅ **Reporting** - Umsatzstatistiken, Auswertungen, Dashboard
- ✅ **Mehrere Rechnungskreise** - Getrennte Nummernkreise nach Mandant/Jahr
- ✅ **GoBD-konform** - Unveränderbarkeit und Audit-Log
- ✅ **Mehrsprachig** - Deutsch (weitere Sprachen erweiterbar)
- ✅ **Multi-Mandantenfähig** - Mehrere Firmen in einem System

### Dokumenttypen
- Angebote mit Gültigkeitsdatum
- Auftragsbestätigungen
- Lieferscheine
- Rechnungen (Vollrechnung, Teilrechnung, Schlussrechnung)
- Proforma-Rechnungen
- Gutschriften
- Stornorechnungen
- Mahnungen (mehrstufig)

### Buchhaltungs-Features
- DATEV-Export (ASCII-Format)
- Umsatzsteuer-Voranmeldung Vorbereitung
- Offene Posten Verwaltung
- Skonto-Verwaltung
- Reverse-Charge-Verfahren
- Kleinunternehmerregelung §19 UStG
- Differenzbesteuerung §25a UStG
- Innergemeinschaftliche Lieferungen

## Technologie-Stack

### Backend
- **PHP 8.1+** - Modernes OOP, PSR-Standards
- **MySQL 8.0+** / MariaDB - Relationale Datenbank
- **Composer** - Dependency Management

### Frontend
- **Bootstrap 5** - Responsive UI Framework
- **JavaScript (Vanilla + jQuery)** - Interaktive Funktionen
- **Bootstrap Icons** - Icon-System

### Libraries
- **TCPDF** - PDF-Generierung
- **PHPMailer** - Email-Versand
- **Twig** - Template Engine
- **Dotenv** - Environment Management
- **Guzzle** - HTTP Client für APIs

## Installation

### Systemanforderungen
- PHP >= 8.1
- MySQL >= 8.0 oder MariaDB >= 10.5
- Apache/Nginx Webserver
- Composer
- PHP Extensions: PDO, mbstring, json, gd, zip

### Schritt 1: Repository klonen
```bash
git clone <repository-url>
cd rechnung
```

### Schritt 2: Dependencies installieren
```bash
composer install
```

### Schritt 3: Environment-Datei erstellen
```bash
cp .env.example .env
```

Passen Sie die `.env` Datei an (Datenbank, Mail, etc.)

### Schritt 4: Datenbank erstellen und importieren
```bash
# Datenbank erstellen
mysql -u root -p -e "CREATE DATABASE rechnung_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Schema importieren
mysql -u root -p rechnung_db < database/migrations/001_create_database_schema.sql

# Beispieldaten importieren
mysql -u root -p rechnung_db < database/seeds/001_default_data.sql
```

### Schritt 5: Berechtigungen setzen
```bash
chmod -R 775 storage/
chmod -R 775 public/uploads/
```

### Schritt 6: Webserver konfigurieren
Richten Sie Ihren Webserver so ein, dass das `public/` Verzeichnis als DocumentRoot dient.

### Schritt 7: Zugriff
Öffnen Sie die Anwendung im Browser und melden Sie sich an:
- **Benutzername:** admin
- **Passwort:** admin123

**⚠️ WICHTIG:** Ändern Sie das Passwort sofort nach dem ersten Login!

## Projektstruktur

```
rechnung/
├── app/                   # Application Code
│   ├── controllers/       # Controller
│   ├── models/           # Models
│   └── views/            # Views
├── config/               # Konfiguration
├── database/             # Datenbank Migrations & Seeds
├── libs/                 # Core Libraries
├── public/               # Public Files (DocumentRoot)
├── storage/              # Logs, Cache, Sessions
└── composer.json         # Dependencies
```

## Verwendung

### Kunden anlegen
1. Navigieren zu **Kunden** → **Neuer Kunde**
2. Kundendaten eingeben (B2B oder B2C)
3. Rechnungs- und Lieferadresse
4. Zahlungsbedingungen festlegen
5. Speichern

### Rechnung erstellen
1. Navigieren zu **Rechnungen** → **Neue Rechnung**
2. Kunde auswählen
3. Positionen hinzufügen
4. Steuersätze prüfen
5. Speichern und PDF generieren
6. Optional: per Email versenden

## Sicherheit

### Wichtige Maßnahmen
- Ändern Sie alle Standard-Passwörter
- Verwenden Sie HTTPS in Produktion
- Schützen Sie die Datenbank mit Firewall
- Erstellen Sie regelmäßig Backups
- Halten Sie PHP und Dependencies aktuell

### Implementierte Features
- Password Hashing (bcrypt)
- CSRF-Schutz
- SQL-Injection-Schutz (Prepared Statements)
- XSS-Schutz
- Session-Management
- Rollenbasierte Zugriffskontrolle

## Fehlerbehebung

### Database Connection Failed
- Prüfen Sie `.env` Datenbank-Einstellungen
- Stellen Sie sicher, dass MySQL läuft
- Prüfen Sie Datenbankname und Berechtigungen

### 404 Not Found
- Aktivieren Sie `mod_rewrite` in Apache
- Prüfen Sie `.htaccess`
- Überprüfen Sie DocumentRoot

### Permission Denied
```bash
chmod -R 775 storage/
chmod -R 775 public/uploads/
```

## Lizenz

MIT License

## Entwickelt mit ❤️