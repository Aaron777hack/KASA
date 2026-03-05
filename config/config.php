<?php
/**
 * Configuration générale du site KASA Immobilier
 */

// Empêcher l'accès direct
if (!defined('KASA_LOADED')) {
    die('Accès interdit.');
}

// =============================================
// CONFIGURATION BASE DE DONNÉES
// =============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'kasa_db');
define('DB_USER', 'root');         // Remplacer par votre utilisateur MySQL
define('DB_PASS', '');             // Remplacer par votre mot de passe MySQL
define('DB_CHARSET', 'utf8mb4');

// =============================================
// CONFIGURATION SITE
// =============================================
define('SITE_NAME', 'KASA Immobilier');

// Auto-détection de l'URL de base (fonctionne en local ET sur hébergement mutualisé)
// Calcule automatiquement le chemin selon la position du fichier par rapport au document root
(function () {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Normaliser les séparateurs de chemin (Windows/Linux)
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    // Le dossier racine du site = parent de /config/
    $siteDir = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');

    // Calculer le sous-chemin par rapport au document root
    $basePath = '';
    if ($docRoot && strpos($siteDir, $docRoot) === 0) {
        $basePath = substr($siteDir, strlen($docRoot));
    }
    // Nettoyer (éviter double slash ou slash final)
    $basePath = '/' . ltrim(str_replace('//', '/', $basePath), '/');
    $basePath = rtrim($basePath, '/');

    define('SITE_URL', rtrim($protocol . '://' . $host . $basePath, '/'));
    define('BASE_PATH', $basePath); // Chemin relatif au document root (ex: /quarter ou '')
})();

define('SITE_EMAIL', 'info@kasa-immo.ci');
define('SITE_PHONE', '+2250153847878');
define('SITE_ADDRESS', 'Cocody Attoban, Abidjan, Côte d\'Ivoire');

// =============================================
// CONFIGURATION EMAIL (SMTP)
// =============================================
define('MAIL_HOST', 'smtp.gmail.com');   // Remplacer par votre serveur SMTP
define('MAIL_PORT', 587);
define('MAIL_USER', 'info@kasa-immo.ci');
define('MAIL_PASS', 'votre_mot_de_passe');
define('MAIL_FROM_NAME', 'KASA Immobilier');
define('MAIL_ENCRYPTION', 'tls');

// =============================================
// CONFIGURATION UPLOADS
// =============================================
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// =============================================
// CONFIGURATION PAGINATION
// =============================================
define('ITEMS_PER_PAGE', 9);

// =============================================
// CONFIGURATION SESSION ADMIN
// =============================================
define('ADMIN_SESSION_NAME', 'kasa_admin');
define('SESSION_LIFETIME', 3600 * 8); // 8 heures

// =============================================
// MODE DEBUG (false en production)
// =============================================
define('DEBUG_MODE', true);

if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
