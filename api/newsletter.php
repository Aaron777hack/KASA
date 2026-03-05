<?php
/**
 * API : Inscription à la newsletter
 */
define('KASA_LOADED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// Vérifier CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Requête invalide.']);
    exit;
}

$email = sanitize($_POST['email'] ?? '');

if (empty($email) || !isValidEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
    exit;
}

try {
    // Vérifier si déjà inscrit
    $stmt = db()->prepare('SELECT id, status FROM newsletter WHERE email = ?');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['status'] === 'active') {
            echo json_encode(['success' => false, 'message' => 'Cette adresse est déjà inscrite à notre newsletter.']);
        } else {
            // Réactiver
            db()->prepare('UPDATE newsletter SET status = ? WHERE email = ?')->execute(['active', $email]);
            echo json_encode(['success' => true, 'message' => 'Votre inscription a été réactivée. Merci !']);
        }
        exit;
    }

    // Nouvelle inscription
    $token = bin2hex(random_bytes(16));
    $stmt  = db()->prepare('INSERT INTO newsletter (email, token) VALUES (?, ?)');
    $stmt->execute([$email, $token]);

    // Email de bienvenue
    sendWelcomeEmail($email, $token);

    echo json_encode(['success' => true, 'message' => 'Merci ! Vous êtes maintenant inscrit(e) à notre newsletter.']);

} catch (PDOException $e) {
    error_log('Newsletter error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription. Veuillez réessayer.']);
}

function sendWelcomeEmail(string $email, string $token): void {
    $subject = '=?UTF-8?B?' . base64_encode('Bienvenue dans la newsletter KASA Immobilier') . '?=';
    $unsubUrl = SITE_URL . '/api/newsletter.php?action=unsubscribe&email=' . urlencode($email) . '&token=' . $token;

    $body  = "Bonjour,\n\n";
    $body .= "Merci de vous être inscrit(e) à la newsletter KASA Immobilier !\n\n";
    $body .= "Vous recevrez désormais nos dernières offres immobilières et actualités.\n\n";
    $body .= "Pour vous désinscrire à tout moment : $unsubUrl\n\n";
    $body .= "---\n" . SITE_NAME . " - " . SITE_ADDRESS;

    $headers  = "From: " . SITE_NAME . " <" . SITE_EMAIL . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";

    @mail($email, $subject, $body, $headers);
}

// Désinscription via lien email
if (isset($_GET['action']) && $_GET['action'] === 'unsubscribe') {
    $emailGet = $_GET['email'] ?? '';
    $tokenGet = $_GET['token'] ?? '';
    if ($emailGet && $tokenGet) {
        try {
            $stmt = db()->prepare('UPDATE newsletter SET status = ? WHERE email = ? AND token = ?');
            $stmt->execute(['inactive', $emailGet, $tokenGet]);
        } catch (PDOException $e) {}
    }
    header('Location: ' . SITE_URL . '/index.php?unsubscribed=1');
    exit;
}
