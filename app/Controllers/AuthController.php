<?php
namespace App\Controllers;

use Libs\Controller;
use App\Models\User;

/**
 * Auth Controller
 * Verwaltet Authentifizierung
 */
class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Zeige Login-Formular
     */
    public function showLogin() {
        // Wenn bereits eingeloggt, redirect zum Dashboard
        if (isLoggedIn()) {
            $this->redirect(url('dashboard'));
        }

        $this->view('auth.login', [
            'title' => 'Login'
        ]);
    }

    /**
     * Login verarbeiten
     */
    public function login() {
        if (requestMethod() !== 'POST') {
            $this->redirect(url('login'));
        }

        $username = $this->post('username');
        $password = $this->post('password');
        $remember = $this->post('remember');

        // Validierung
        $errors = $this->validate([
            'username' => $username,
            'password' => $password
        ], [
            'username' => 'required',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            setFlash('error', 'Bitte füllen Sie alle Felder aus.');
            $this->redirect(url('login'));
        }

        // Finde User
        $user = $this->userModel->findByUsernameOrEmail($username);

        // Prüfe User und Passwort
        if (!$user || !$this->userModel->verifyPassword($user, $password)) {
            setFlash('error', 'Ungültige Anmeldedaten.');
            $this->redirect(url('login'));
        }

        // Prüfe ob User aktiv
        if (!$user['is_active']) {
            setFlash('error', 'Ihr Account ist deaktiviert.');
            $this->redirect(url('login'));
        }

        // Session setzen
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role']
        ];

        // Update Last Login
        $this->userModel->updateLastLogin($user['id']);

        // Log
        logMessage("User logged in: {$user['username']} (ID: {$user['id']})");

        setFlash('success', 'Willkommen zurück, ' . ($user['first_name'] ?: $user['username']) . '!');

        // Redirect zum Dashboard
        $this->redirect(url('dashboard'));
    }

    /**
     * Logout
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['user']['username'] ?? 'Unknown';

        // Session zerstören
        session_destroy();

        // Log
        if ($userId) {
            logMessage("User logged out: {$username} (ID: {$userId})");
        }

        setFlash('success', 'Sie wurden erfolgreich abgemeldet.');
        $this->redirect(url('login'));
    }
}
