<?php
namespace Libs;

use Libs\Database;

/**
 * Base Model Klasse
 * Alle Models erben von dieser Klasse
 */
abstract class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $timestamps = true;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Alle Datensätze holen
     */
    public function all($orderBy = null, $order = 'ASC') {
        $sql = "SELECT * FROM {$this->table}";
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }
        return $this->db->fetchAll($sql);
    }

    /**
     * Datensatz nach ID holen
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Datensätze nach Bedingung holen
     */
    public function where($conditions, $orderBy = null, $order = 'ASC', $limit = null) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        foreach ($conditions as $field => $value) {
            if (is_array($value)) {
                // IN Clause
                $placeholders = str_repeat('?,', count($value) - 1) . '?';
                $sql .= " AND {$field} IN ({$placeholders})";
                $params = array_merge($params, $value);
            } else {
                $sql .= " AND {$field} = ?";
                $params[] = $value;
            }
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Einzelnen Datensatz nach Bedingung holen
     */
    public function findWhere($conditions) {
        $results = $this->where($conditions, null, 'ASC', 1);
        return $results[0] ?? null;
    }

    /**
     * Neuen Datensatz erstellen
     */
    public function create($data) {
        // Nur erlaubte Felder
        $data = $this->filterFillable($data);

        // Timestamps hinzufügen
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $fields = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            $placeholders
        );

        return $this->db->insert($sql, $values);
    }

    /**
     * Datensatz aktualisieren
     */
    public function update($id, $data) {
        // Nur erlaubte Felder
        $data = $this->filterFillable($data);

        // Timestamp aktualisieren
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $fields = array_keys($data);
        $values = array_values($data);
        $values[] = $id; // ID am Ende für WHERE clause

        $setPart = implode(' = ?, ', $fields) . ' = ?';

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            $setPart,
            $this->primaryKey
        );

        return $this->db->execute($sql, $values);
    }

    /**
     * Datensatz löschen
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Anzahl Datensätze
     */
    public function count($conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE 1=1";
        $params = [];

        foreach ($conditions as $field => $value) {
            $sql .= " AND {$field} = ?";
            $params[] = $value;
        }

        $result = $this->db->fetchOne($sql, $params);
        return (int)$result['count'];
    }

    /**
     * Prüfe ob Datensatz existiert
     */
    public function exists($id) {
        return $this->find($id) !== false;
    }

    /**
     * Custom Query
     */
    public function query($sql, $params = []) {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Single Custom Query
     */
    public function queryOne($sql, $params = []) {
        return $this->db->fetchOne($sql, $params);
    }

    /**
     * Filtere nur erlaubte Felder
     */
    protected function filterFillable($data) {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Pagination
     */
    public function paginate($page = 1, $perPage = 20, $conditions = [], $orderBy = null, $order = 'ASC') {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        foreach ($conditions as $field => $value) {
            $sql .= " AND {$field} = ?";
            $params[] = $value;
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }

        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $data = $this->db->fetchAll($sql, $params);
        $total = $this->count($conditions);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage)
        ];
    }
}
