<?php
define('KASA_LOADED', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdminLogin();

$id = (int) getParam('id', 0);
if (!$id) redirect(SITE_URL . '/admin/articles/index.php');

try {
    $stmt = db()->prepare('SELECT image FROM articles WHERE id = ?');
    $stmt->execute([$id]);
    $article = $stmt->fetch();
    if ($article) {
        if ($article['image']) deleteImage($article['image']);
        db()->prepare('DELETE FROM articles WHERE id = ?')->execute([$id]);
        setFlash('success', 'Article supprimé avec succès.');
    }
} catch (PDOException $e) {
    setFlash('error', 'Erreur lors de la suppression.');
}

redirect(SITE_URL . '/admin/articles/index.php');
