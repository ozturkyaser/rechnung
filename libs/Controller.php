<?php
namespace Libs;

/**
 * Base Controller Klasse
 * Alle Controller erben von dieser Klasse
 */
abstract class Controller {
    /**
     * View rendern
     */
    protected function view($view, $data = []) {
        // Extrahiere Daten als Variablen
        extract($data);

        // Layout-Variablen
        $content = $this->renderView($view, $data);

        // Wenn AJAX, nur Content zurückgeben
        if (isAjax()) {
            echo $content;
            return;
        }

        // Sonst mit Layout
        require_once APP_PATH . '/Views/layouts/main.php';
    }

    /**
     * Rendere View ohne Layout
     */
    protected function renderView($view, $data = []) {
        extract($data);

        ob_start();
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            throw new \Exception("View not found: {$view}");
        }

        return ob_get_clean();
    }

    /**
     * JSON Response
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect
     */
    protected function redirect($url, $statusCode = 302) {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    /**
     * Redirect zurück
     */
    protected function redirectBack() {
        $referer = $_SERVER['HTTP_REFERER'] ?? url();
        $this->redirect($referer);
    }

    /**
     * Validiere CSRF Token
     */
    protected function validateCsrf() {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';

        if (!validateCsrfToken($token)) {
            if (isAjax()) {
                $this->json(['error' => 'Invalid CSRF token'], 403);
            } else {
                die('Invalid CSRF token');
            }
        }
    }

    /**
     * Prüfe ob User eingeloggt ist
     */
    protected function requireAuth() {
        if (!isLoggedIn()) {
            if (isAjax()) {
                $this->json(['error' => 'Unauthorized'], 401);
            } else {
                setFlash('error', 'Bitte melden Sie sich an.');
                $this->redirect(url('login'));
            }
        }
    }

    /**
     * Prüfe Benutzerrolle
     */
    protected function requireRole($role) {
        $this->requireAuth();

        if (!hasRole($role)) {
            if (isAjax()) {
                $this->json(['error' => 'Forbidden'], 403);
            } else {
                setFlash('error', 'Sie haben keine Berechtigung für diese Aktion.');
                $this->redirectBack();
            }
        }
    }

    /**
     * Hole POST-Daten
     */
    protected function post($key = null, $default = null) {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Hole GET-Daten
     */
    protected function get($key = null, $default = null) {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Hole Request-Daten (POST oder GET)
     */
    protected function input($key = null, $default = null) {
        if ($key === null) {
            return array_merge($_GET, $_POST);
        }
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Validiere Input
     */
    protected function validate($data, $rules) {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $ruleArray = explode('|', $ruleString);
            $value = $data[$field] ?? null;

            foreach ($ruleArray as $rule) {
                if ($rule === 'required' && empty($value)) {
                    $errors[$field][] = "Das Feld {$field} ist erforderlich.";
                }

                if ($rule === 'email' && !empty($value) && !isValidEmail($value)) {
                    $errors[$field][] = "Das Feld {$field} muss eine gültige E-Mail-Adresse sein.";
                }

                if (strpos($rule, 'min:') === 0) {
                    $min = (int)substr($rule, 4);
                    if (!empty($value) && strlen($value) < $min) {
                        $errors[$field][] = "Das Feld {$field} muss mindestens {$min} Zeichen lang sein.";
                    }
                }

                if (strpos($rule, 'max:') === 0) {
                    $max = (int)substr($rule, 4);
                    if (!empty($value) && strlen($value) > $max) {
                        $errors[$field][] = "Das Feld {$field} darf maximal {$max} Zeichen lang sein.";
                    }
                }

                if ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
                    $errors[$field][] = "Das Feld {$field} muss eine Zahl sein.";
                }
            }
        }

        return $errors;
    }

    /**
     * Upload File
     */
    protected function uploadFile($fileInputName, $targetDir = 'uploads', $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf']) {
        if (!isset($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Keine Datei hochgeladen'];
        }

        $file = $_FILES[$fileInputName];
        $fileName = $file['name'];
        $fileTmp = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Prüfe Dateityp
        if (!in_array($fileExt, $allowedTypes)) {
            return ['success' => false, 'error' => 'Dateityp nicht erlaubt'];
        }

        // Prüfe Dateigröße (max 10MB)
        if ($fileSize > 10 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Datei zu groß (max 10MB)'];
        }

        // Erstelle eindeutigen Dateinamen
        $newFileName = uniqid() . '_' . time() . '.' . $fileExt;
        $uploadPath = PUBLIC_PATH . '/' . $targetDir . '/' . $newFileName;

        // Verschiebe Datei
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            return [
                'success' => true,
                'file_name' => $newFileName,
                'file_path' => $targetDir . '/' . $newFileName,
                'full_path' => $uploadPath
            ];
        }

        return ['success' => false, 'error' => 'Fehler beim Hochladen'];
    }
}
