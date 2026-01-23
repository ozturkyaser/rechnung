#!/bin/bash
# Rechnungsprogramm Quick Start Script

echo "════════════════════════════════════════════════════════════"
echo "   Rechnungsprogramm - Setup & Start"
echo "════════════════════════════════════════════════════════════"
echo ""

# Farben
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# 1. MySQL Check
echo -e "${YELLOW}[1/5]${NC} Prüfe MySQL/MariaDB Installation..."
if command -v mysql &> /dev/null || command -v mariadb &> /dev/null; then
    echo -e "${GREEN}✓${NC} MySQL/MariaDB gefunden"
else
    echo -e "${RED}✗${NC} MySQL/MariaDB nicht gefunden"
    echo ""
    echo "Bitte installiere MySQL/MariaDB zuerst:"
    echo "  sudo apt update"
    echo "  sudo apt install mysql-server"
    echo ""
    exit 1
fi

# 2. Datenbank erstellen
echo -e "\n${YELLOW}[2/5]${NC} Datenbank-Setup..."
echo "Bitte erstelle die Datenbank manuell mit:"
echo "  sudo mysql"
echo "  CREATE DATABASE rechnung_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "  CREATE USER 'rechnung_user'@'localhost' IDENTIFIED BY 'dein_passwort';"
echo "  GRANT ALL PRIVILEGES ON rechnung_db.* TO 'rechnung_user'@'localhost';"
echo "  FLUSH PRIVILEGES;"
echo "  EXIT;"
echo ""
read -p "Hast du die Datenbank erstellt? (j/n): " db_created
if [ "$db_created" != "j" ]; then
    echo "Bitte erstelle zuerst die Datenbank."
    exit 1
fi

# 3. Schema importieren
echo -e "\n${YELLOW}[3/5]${NC} Importiere Datenbank-Schema..."
read -p "Datenbank-Benutzername [rechnung_user]: " db_user
db_user=${db_user:-rechnung_user}

mysql -u "$db_user" -p rechnung_db < database/migrations/001_create_database_schema.sql
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Schema importiert"
else
    echo -e "${RED}✗${NC} Fehler beim Schema-Import"
    exit 1
fi

# 4. Standard-Daten importieren
echo -e "\n${YELLOW}[4/5]${NC} Importiere Standard-Daten..."
mysql -u "$db_user" -p rechnung_db < database/seeds/001_default_data.sql
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓${NC} Standard-Daten importiert"
else
    echo -e "${RED}✗${NC} Fehler beim Daten-Import"
    exit 1
fi

# 5. Server starten
echo -e "\n${YELLOW}[5/5]${NC} Starte PHP Development Server..."
echo ""
echo "════════════════════════════════════════════════════════════"
echo -e "${GREEN}✓ Setup abgeschlossen!${NC}"
echo ""
echo "Server läuft auf: ${GREEN}http://localhost:8000${NC}"
echo ""
echo "Login-Daten:"
echo "  Benutzername: ${GREEN}admin${NC}"
echo "  Passwort: ${GREEN}admin123${NC}"
echo ""
echo "Drücke STRG+C zum Beenden"
echo "════════════════════════════════════════════════════════════"
echo ""

cd /home/user/rechnung
php -S localhost:8000 -t public
