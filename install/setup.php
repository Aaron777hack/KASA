<?php
/**
 * Script d'installation de la base de données KASA
 * Accéder une seule fois : http://votre-site.com/install/setup.php
 */

// Protection : fichier à supprimer après installation
$lockFile = __DIR__ . '/installed.lock';
if (file_exists($lockFile)) {
    die('<h2 style="color:red;font-family:sans-serif;">Déjà installé. Supprimez /install/installed.lock pour réinstaller.</h2>');
}

define('KASA_LOADED', true);
require_once __DIR__ . '/../config/config.php';

// Connexion sans sélectionner de BD (pour la créer)
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET,
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('<h2 style="color:red;font-family:sans-serif;">Impossible de se connecter à MySQL : ' . $e->getMessage() . '</h2>');
}

$errors   = [];
$messages = [];

try {
    // Créer la base de données
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    $messages[] = '✅ Base de données `' . DB_NAME . '` créée/vérifiée';

    // Table admins
    $pdo->exec("CREATE TABLE IF NOT EXISTS `admins` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(150) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = '✅ Table `admins` créée';

    // Table categories
    $pdo->exec("CREATE TABLE IF NOT EXISTS `categories` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `slug` VARCHAR(100) NOT NULL,
        `type` ENUM('article','property') NOT NULL DEFAULT 'article',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = '✅ Table `categories` créée';

    // Table articles
    $pdo->exec("CREATE TABLE IF NOT EXISTS `articles` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `excerpt` TEXT,
        `content` LONGTEXT,
        `image` VARCHAR(255),
        `category_id` INT(11),
        `author` VARCHAR(100) DEFAULT 'Admin',
        `status` ENUM('published','draft') NOT NULL DEFAULT 'draft',
        `views` INT(11) DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`),
        KEY `status` (`status`),
        KEY `category_id` (`category_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = '✅ Table `articles` créée';

    // Table properties (annonces immobilières)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `properties` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) NOT NULL,
        `slug` VARCHAR(255) NOT NULL,
        `description` LONGTEXT,
        `price` DECIMAL(15,2),
        `price_type` ENUM('vente','location','terrain') NOT NULL DEFAULT 'vente',
        `property_type` VARCHAR(100),
        `location` VARCHAR(255),
        `bedrooms` INT(11) DEFAULT 0,
        `bathrooms` INT(11) DEFAULT 0,
        `area` INT(11) DEFAULT 0,
        `image` VARCHAR(255),
        `gallery` TEXT,
        `status` ENUM('disponible','vendu','loue') NOT NULL DEFAULT 'disponible',
        `featured` TINYINT(1) DEFAULT 0,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `slug` (`slug`),
        KEY `status` (`status`),
        KEY `price_type` (`price_type`),
        KEY `featured` (`featured`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = '✅ Table `properties` créée';

    // Table contacts (messages)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `contacts` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(150) NOT NULL,
        `phone` VARCHAR(30),
        `service` VARCHAR(100),
        `message` TEXT NOT NULL,
        `status` ENUM('new','read','replied') NOT NULL DEFAULT 'new',
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = '✅ Table `contacts` créée';

    // Table newsletter
    $pdo->exec("CREATE TABLE IF NOT EXISTS `newsletter` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `email` VARCHAR(150) NOT NULL,
        `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
        `token` VARCHAR(100),
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = '✅ Table `newsletter` créée';

    // Table settings
    $pdo->exec("CREATE TABLE IF NOT EXISTS `settings` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `setting_key` VARCHAR(100) NOT NULL,
        `setting_value` TEXT,
        PRIMARY KEY (`id`),
        UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $messages[] = '✅ Table `settings` créée';

    // Insérer l'admin par défaut (admin@kasa.ci / Admin@2024)
    $adminPassword = password_hash('Admin@2024', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO `admins` (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute(['Super Admin', 'admin@kasa.ci', $adminPassword]);
    $messages[] = '✅ Compte admin créé : admin@kasa.ci / Admin@2024';

    // Insérer les catégories par défaut
    $categories = [
        ['Immobilier', 'immobilier', 'article'],
        ['Actualités', 'actualites', 'article'],
        ['Conseils', 'conseils', 'article'],
        ['Duplex', 'duplex', 'property'],
        ['Villa', 'villa', 'property'],
        ['Appartement', 'appartement', 'property'],
        ['Terrain', 'terrain', 'property'],
        ['Bureau', 'bureau', 'property'],
    ];
    $stmtCat = $pdo->prepare("INSERT IGNORE INTO `categories` (name, slug, type) VALUES (?, ?, ?)");
    foreach ($categories as $cat) {
        $stmtCat->execute($cat);
    }
    $messages[] = '✅ Catégories par défaut insérées';

    // Insérer paramètres par défaut
    $settings = [
        ['site_name', 'KASA Immobilier'],
        ['site_email', SITE_EMAIL],
        ['site_phone', SITE_PHONE],
        ['site_address', SITE_ADDRESS],
        ['fb_url', '#'],
        ['tw_url', '#'],
        ['ig_url', '#'],
        ['wh_url', '#'],
    ];
    $stmtSet = $pdo->prepare("INSERT IGNORE INTO `settings` (setting_key, setting_value) VALUES (?, ?)");
    foreach ($settings as $s) {
        $stmtSet->execute($s);
    }
    $messages[] = '✅ Paramètres par défaut insérés';

    // Créer les dossiers d'upload
    $dirs = [
        __DIR__ . '/../uploads/',
        __DIR__ . '/../uploads/articles/',
        __DIR__ . '/../uploads/properties/',
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    $messages[] = '✅ Dossiers uploads créés';

    // Créer le fichier de verrouillage
    file_put_contents($lockFile, date('Y-m-d H:i:s'));
    $messages[] = '🔒 Installation verrouillée';

} catch (PDOException $e) {
    $errors[] = '❌ Erreur SQL : ' . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Installation KASA - Base de données</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 700px; margin: 40px auto; padding: 20px; }
        h1 { color: #ff5a3c; }
        .success { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        .box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .btn { display:inline-block; padding: 12px 24px; background: #ff5a3c; color: #fff; text-decoration: none; border-radius: 5px; margin-top: 20px; font-weight: bold; }
        .credentials { background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 15px; margin: 15px 0; }
        .credentials code { font-size: 16px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>🏠 Installation KASA Immobilier</h1>

    <?php if (!empty($errors)): ?>
        <div class="box">
            <h2 class="error">Erreurs détectées :</h2>
            <?php foreach ($errors as $e): ?>
                <p class="error"><?= htmlspecialchars($e) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($messages)): ?>
        <div class="box">
            <h2>Résultat de l'installation :</h2>
            <?php foreach ($messages as $m): ?>
                <p class="success"><?= htmlspecialchars($m) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($errors)): ?>
        <div class="credentials">
            <h3>🔑 Accès Administration</h3>
            <p>URL admin : <a href="../admin/login.php"><code><?= SITE_URL ?>/admin/login.php</code></a></p>
            <p>Email : <code>admin@kasa.ci</code></p>
            <p>Mot de passe : <code>Admin@2024</code></p>
            <p style="color:red;"><strong>⚠️ Changez le mot de passe dès la première connexion !</strong></p>
        </div>
        <a href="../index.php" class="btn">Aller sur le site →</a>
        <a href="../admin/login.php" class="btn" style="background:#333;margin-left:10px;">Administration →</a>
    <?php endif; ?>
</body>
</html>
