<?php
/**
 * Connexion Admin - KASA Immobilier
 */
define('KASA_LOADED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/auth.php';

// Déjà connecté
if (isAdminLoggedIn()) {
    redirect(SITE_URL . '/admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Requête invalide.';
    } else {
        $email    = sanitize($_POST['email']    ?? '');
        $password = $_POST['password'] ?? '';

        if (loginAdmin($email, $password)) {
            redirect(SITE_URL . '/admin/index.php');
        } else {
            $error = 'Email ou mot de passe incorrect.';
            // Anti-bruteforce léger
            sleep(1);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - KASA Immobilier</title>
    <link rel="shortcut icon" href="<?= SITE_URL ?>/img/favicon.png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,.12); padding: 48px 40px; width: 100%; max-width: 420px; }
        .login-logo { text-align: center; margin-bottom: 32px; }
        .login-logo img { height: 70px; }
        .login-logo h2 { color: #ff5a3c; font-size: 22px; margin-top: 10px; }
        .login-logo p { color: #666; font-size: 14px; margin-top: 4px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 6px; }
        input { width: 100%; padding: 12px 16px; border: 2px solid #e1e4e8; border-radius: 8px; font-size: 15px; transition: border-color .2s; outline: none; }
        input:focus { border-color: #ff5a3c; }
        .btn-login { width: 100%; padding: 14px; background: #ff5a3c; color: #fff; border: none; border-radius: 8px; font-size: 16px; font-weight: 700; cursor: pointer; transition: background .2s; }
        .btn-login:hover { background: #e04b2d; }
        .error-msg { background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #666; font-size: 13px; text-decoration: none; }
        .back-link a:hover { color: #ff5a3c; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-logo">
            <img src="<?= SITE_URL ?>/img/logo-4.png" alt="KASA Logo">
            <h2>Administration</h2>
            <p>Connectez-vous à votre espace admin</p>
        </div>

        <?php if ($error): ?>
        <div class="error-msg">⚠️ <?= e($error) ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['timeout'])): ?>
        <div class="error-msg">⏱ Votre session a expiré. Reconnectez-vous.</div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="admin@kasa.ci" autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required placeholder="••••••••" autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
        <div class="back-link">
            <a href="<?= SITE_URL ?>/index.php">← Retour au site</a>
        </div>
    </div>
</body>
</html>
