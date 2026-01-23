<?php
namespace Libs;

use PDO;
use PDOException;

/**
 * Database Klasse - Singleton Pattern
 * Verwaltet die Datenbankverbindung
 */
class Database {
    private static $instance = null;
    private $connection;

    /**
     * Private Constructor für Singleton
     */
    private function __construct() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_PORT,
                DB_DATABASE,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];

            $this->connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            logMessage('Database Connection Error: ' . $e->getMessage(), 'error');
            if (APP_DEBUG) {
                die('Database Connection Error: ' . $e->getMessage());
            } else {
                die('Database connection failed. Please contact administrator.');
            }
        }
    }

    /**
     * Hole Singleton Instanz
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Hole PDO Connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Query ausführen
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            logMessage('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql, 'error');
            throw $e;
        }
    }

    /**
     * Einzelne Zeile holen
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Alle Zeilen holen
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insert und hole Last Insert ID
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }

    /**
     * Update/Delete und hole Anzahl betroffener Zeilen
     */
    public function execute($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Beginne Transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit Transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback Transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    /**
     * Verhindere Klonen
     */
    private function __clone() {}

    /**
     * Verhindere Unserialize
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize singleton");
    }
}
