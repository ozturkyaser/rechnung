<?php
namespace App\Models;

use Libs\Model;

/**
 * User Model
 */
class User extends Model {
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'is_active',
        'two_factor_enabled',
        'two_factor_secret'
    ];

    /**
     * Finde User nach Username
     */
    public function findByUsername($username) {
        return $this->findWhere(['username' => $username]);
    }

    /**
     * Finde User nach Email
     */
    public function findByEmail($email) {
        return $this->findWhere(['email' => $email]);
    }

    /**
     * Finde User nach Username oder Email
     */
    public function findByUsernameOrEmail($identifier) {
        $sql = "SELECT * FROM {$this->table} WHERE username = ? OR email = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$identifier, $identifier]);
    }

    /**
     * Erstelle neuen User mit gehashtem Passwort
     */
    public function createUser($data) {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        return $this->create($data);
    }

    /**
     * Aktualisiere User
     */
    public function updateUser($id, $data) {
        // Wenn neues Passwort, dann hashen
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        } else {
            unset($data['password']);
        }

        return $this->update($id, $data);
    }

    /**
     * Prüfe Passwort
     */
    public function verifyPassword($user, $password) {
        if (!$user || !isset($user['password'])) {
            return false;
        }

        return password_verify($password, $user['password']);
    }

    /**
     * Update Last Login
     */
    public function updateLastLogin($userId) {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE {$this->primaryKey} = ?";
        return $this->db->execute($sql, [$userId]);
    }

    /**
     * Hole aktive Users
     */
    public function getActiveUsers() {
        return $this->where(['is_active' => 1], 'username', 'ASC');
    }

    /**
     * Hole Users nach Rolle
     */
    public function getUsersByRole($role) {
        return $this->where(['role' => $role, 'is_active' => 1], 'username', 'ASC');
    }

    /**
     * Prüfe ob Username existiert
     */
    public function usernameExists($username, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Prüfe ob Email existiert
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
}
