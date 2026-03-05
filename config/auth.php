<?php
/**
 * Gestion de l'authentification admin
 */

if (!defined('KASA_LOADED')) {
    die('Accès interdit.');
}

// Démarrer la session si ce n'est pas déjà fait
if (session_status() === PHP_SESSION_NONE) {
    session_name(ADMIN_SESSION_NAME);
    session_start();
}

/**
 * Vérifier si l'admin est connecté
 */
function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_email']);
}

/**
 * Rediriger vers le login si non connecté
 */
function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
    // Vérifier le timeout de session
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
        session_destroy();
        header('Location: ' . SITE_URL . '/admin/login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Connecter un admin
 */
function loginAdmin(string $email, string $password): bool {
    try {
        $stmt = db()->prepare('SELECT id, name, email, password FROM admins WHERE email = ?');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']    = $admin['id'];
            $_SESSION['admin_name']  = $admin['name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['last_activity'] = time();
            return true;
        }
    } catch (PDOException $e) {
        error_log('Erreur login: ' . $e->getMessage());
    }
    return false;
}

/**
 * Déconnecter l'admin
 */
function logoutAdmin(): void {
    $_SESSION = [];
    session_destroy();
}

/**
 * Obtenir les infos de l'admin connecté
 */
function getCurrentAdmin(): array {
    return [
        'id'    => $_SESSION['admin_id'] ?? null,
        'name'  => $_SESSION['admin_name'] ?? '',
        'email' => $_SESSION['admin_email'] ?? '',
    ];
}
