<?php
/**
 * API : Traitement du formulaire de contact
 */
define('KASA_LOADED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

session_start();

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Méthode POST uniquement
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

// Vérifier le token CSRF
if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Requête invalide. Rechargez la page.']);
    exit;
}

// Récupérer et valider les champs
$name    = sanitize($_POST['name']    ?? '');
$email   = sanitize($_POST['email']   ?? '');
$phone   = sanitize($_POST['phone']   ?? '');
$service = sanitize($_POST['service'] ?? '');
$message = sanitize($_POST['message'] ?? '');

$errors = [];
if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Le nom est requis (minimum 2 caractères).';
}
if (empty($email) || !isValidEmail($email)) {
    $errors[] = 'Une adresse email valide est requise.';
}
if (empty($message) || strlen($message) < 10) {
    $errors[] = 'Le message est requis (minimum 10 caractères).';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Enregistrer en base de données
try {
    $stmt = db()->prepare("
        INSERT INTO contacts (name, email, phone, service, message)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$name, $email, $phone, $service, $message]);
    $contactId = db()->lastInsertId();
} catch (PDOException $e) {
    error_log('Erreur contact BD: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement. Veuillez réessayer.']);
    exit;
}

// Envoyer l'email de notification à l'admin
$emailSent = sendContactEmail($name, $email, $phone, $service, $message);

// Envoyer l'email de confirmation au visiteur
sendConfirmationEmail($name, $email);

echo json_encode([
    'success' => true,
    'message' => 'Merci ' . htmlspecialchars($name) . ' ! Votre message a bien été envoyé. Nous vous répondrons dans les plus brefs délais.',
]);

// ============================================================
// Fonctions d'envoi d'email (mail() natif PHP)
// ============================================================

function sendContactEmail(string $name, string $email, string $phone, string $service, string $message): bool {
    $to      = SITE_EMAIL;
    $subject = '=?UTF-8?B?' . base64_encode('[KASA] Nouveau message de ' . $name) . '?=';

    $body  = "Nouveau message reçu sur le site KASA Immobilier\n";
    $body .= str_repeat("=", 50) . "\n\n";
    $body .= "Nom      : $name\n";
    $body .= "Email    : $email\n";
    $body .= "Téléphone: " . ($phone ?: 'Non renseigné') . "\n";
    $body .= "Service  : " . ($service ?: 'Non précisé') . "\n\n";
    $body .= "Message :\n$message\n\n";
    $body .= str_repeat("=", 50) . "\n";
    $body .= "Répondre à: $email\n";

    $headers  = "From: " . SITE_NAME . " <" . SITE_EMAIL . ">\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    return @mail($to, $subject, $body, $headers);
}

function sendConfirmationEmail(string $name, string $email): bool {
    $subject = '=?UTF-8?B?' . base64_encode('KASA Immobilier - Confirmation de réception') . '?=';

    $body  = "Bonjour $name,\n\n";
    $body .= "Nous avons bien reçu votre message et nous vous en remercions.\n";
    $body .= "Notre équipe vous contactera dans les plus brefs délais.\n\n";
    $body .= "---\n";
    $body .= SITE_NAME . "\n";
    $body .= SITE_ADDRESS . "\n";
    $body .= "Tél : " . SITE_PHONE . "\n";
    $body .= "Email : " . SITE_EMAIL . "\n";

    $headers  = "From: " . SITE_NAME . " <" . SITE_EMAIL . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return @mail($email, $subject, $body, $headers);
}
