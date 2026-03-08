<?php
/**
 * Fonctions utilitaires globales KASA
 */

if (!defined('KASA_LOADED')) {
    die('Accès interdit.');
}

/**
 * Échapper une chaîne pour l'affichage HTML
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Générer un slug à partir d'un titre
 */
function slugify(string $text): string {
    // Translittération des caractères accentués
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return $text;
}

/**
 * Générer un slug unique (vérifie dans la BD)
 */
function uniqueSlug(string $title, string $table, ?int $excludeId = null): string {
    $baseSlug = slugify($title);
    $slug = $baseSlug;
    $i = 1;

    do {
        $query = "SELECT id FROM $table WHERE slug = ?";
        $params = [$slug];
        if ($excludeId) {
            $query .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = db()->prepare($query);
        $stmt->execute($params);
        $exists = $stmt->fetch();

        if ($exists) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }
    } while ($exists);

    return $slug;
}

/**
 * Formater un prix en FCFA
 */
function formatPrice(float $price, string $suffix = ''): string {
    $formatted = number_format($price, 0, ',', ' ');
    return $formatted . ' FCFA' . ($suffix ? '<label>' . $suffix . '</label>' : '');
}

/**
 * Formater une date en français
 */
function formatDate(string $date, string $format = 'd F Y'): string {
    $months = [
        1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
        5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
        9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
    ];

    $timestamp = strtotime($date);
    $day   = date('d', $timestamp);
    $month = $months[(int) date('n', $timestamp)];
    $year  = date('Y', $timestamp);

    return "$day $month $year";
}

/**
 * Formater une date courte
 */
function formatDateShort(string $date): string {
    return date('d/m/Y', strtotime($date));
}

/**
 * Tronquer un texte
 */
function truncate(string $text, int $length = 150): string {
    $text = strip_tags($text);
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Upload d'une image
 */
function uploadImage(array $file, string $folder = 'articles'): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return false;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return false;
    }

    $uploadDir = UPLOAD_DIR . $folder . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('kasa_', true) . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return 'uploads/' . $folder . '/' . $filename;
    }

    return false;
}

/**
 * Supprimer une image uploadée
 */
function deleteImage(string $path): void {
    $fullPath = __DIR__ . '/../' . $path;
    if (file_exists($fullPath) && strpos($path, 'uploads/') === 0) {
        unlink($fullPath);
    }
}

/**
 * Pagination - calculer les données
 */
function getPagination(int $total, int $currentPage, int $perPage = ITEMS_PER_PAGE): array {
    $totalPages = (int) ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $currentPage,
        'total_pages' => $totalPages,
        'offset'      => $offset,
        'has_prev'    => $currentPage > 1,
        'has_next'    => $currentPage < $totalPages,
        'prev_page'   => $currentPage - 1,
        'next_page'   => $currentPage + 1,
    ];
}

/**
 * Redirection sécurisée
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Flash message (en session)
 */
function setFlash(string $type, string $message): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Nettoyer les entrées utilisateur
 */
function sanitize(string $input): string {
    return trim(strip_tags($input));
}

/**
 * Valider un email
 */
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Obtenir les paramètres GET avec valeur par défaut
 */
function getParam(string $key, mixed $default = null): mixed {
    return isset($_GET[$key]) ? sanitize($_GET[$key]) : $default;
}

/**
 * Obtenir les paramètres POST avec valeur par défaut
 */
function postParam(string $key, mixed $default = ''): string {
    return isset($_POST[$key]) ? sanitize($_POST[$key]) : $default;
}

/**
 * Token CSRF
 */
function generateCsrfToken(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Récupérer un paramètre de configuration depuis la BD
 */
function getSetting(string $key, string $default = ''): string {
    try {
        $stmt = db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Incrémenter les vues d'un article
 */
function incrementViews(int $id, string $table = 'articles'): void {
    try {
        db()->prepare("UPDATE $table SET views = views + 1 WHERE id = ?")->execute([$id]);
    } catch (PDOException $e) {
        // Silencieux
    }
}

/**
 * Obtenir l'URL de l'image ou l'image par défaut
 */
function imageUrl(string $path, string $default = 'img/product-3/1.jpg'): string {
    if ($path && file_exists(__DIR__ . '/../' . $path)) {
        return SITE_URL . '/' . $path;
    }
    return SITE_URL . '/' . $default;
}

/**
 * Envoyer une notification de nouvelle propriété à tous les abonnés actifs
 * Retourne le nombre d'emails envoyés avec succès
 */
function sendPropertyNewsletter(array $property): int {
    try {
        $subscribers = db()->query(
            "SELECT email, token FROM newsletter WHERE status = 'active'"
        )->fetchAll();

        $sent = 0;
        foreach ($subscribers as $sub) {
            if (sendPropertyNewsletterEmail($sub['email'], $sub['token'], $property)) {
                $sent++;
            }
        }
        return $sent;
    } catch (PDOException $e) {
        error_log('Newsletter property error: ' . $e->getMessage());
        return 0;
    }
}

/**
 * Envoyer l'email de notification de nouvelle propriété à un abonné
 */
function sendPropertyNewsletterEmail(string $email, string $token, array $property): bool {
    $subject     = '=?UTF-8?B?' . base64_encode('[KASA] Nouvelle propriété : ' . $property['title']) . '?=';
    $propertyUrl = SITE_URL . '/property-details.php?slug=' . urlencode($property['slug']);
    $unsubUrl    = SITE_URL . '/api/newsletter.php?action=unsubscribe&email=' . urlencode($email) . '&token=' . $token;

    $priceLabel = match($property['price_type']) {
        'location' => number_format($property['price'], 0, ',', ' ') . ' FCFA / mois',
        default    => number_format($property['price'], 0, ',', ' ') . ' FCFA',
    };

    $body  = "Bonjour,\n\n";
    $body .= "Une nouvelle propriété vient d'être ajoutée sur " . SITE_NAME . " !\n\n";
    $body .= str_repeat("=", 50) . "\n";
    $body .= $property['title'] . "\n";
    $body .= str_repeat("=", 50) . "\n\n";
    $body .= "Localisation : " . $property['location'] . "\n";
    $body .= "Prix         : " . $priceLabel . "\n";
    if (!empty($property['property_type'])) {
        $body .= "Type         : " . $property['property_type'] . "\n";
    }
    if (!empty($property['area'])) {
        $body .= "Surface      : " . $property['area'] . " m\u{00B2}\n";
    }
    if (!empty($property['bedrooms'])) {
        $body .= "Chambres     : " . $property['bedrooms'] . "\n";
    }
    if (!empty($property['bathrooms'])) {
        $body .= "Douches      : " . $property['bathrooms'] . "\n";
    }
    if (!empty($property['description'])) {
        $body .= "\n" . truncate($property['description'], 200) . "\n";
    }
    $body .= "\nVoir la propriété : " . $propertyUrl . "\n\n";
    $body .= str_repeat("-", 50) . "\n";
    $body .= SITE_NAME . " - " . SITE_ADDRESS . "\n";
    $body .= "Tel : " . SITE_PHONE . "\n\n";
    $body .= "Se desinscrire : " . $unsubUrl . "\n";

    $headers  = "From: " . SITE_NAME . " <" . SITE_EMAIL . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return @mail($email, $subject, $body, $headers);
}
