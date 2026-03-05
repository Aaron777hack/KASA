<?php
define('KASA_LOADED', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdminLogin();
$id = (int) getParam('id', 0);
if (!$id) redirect(SITE_URL . '/admin/properties/index.php');
try {
    $s = db()->prepare('SELECT image, gallery FROM properties WHERE id = ?');
    $s->execute([$id]);
    $prop = $s->fetch();
    if ($prop) {
        if ($prop['image']) deleteImage($prop['image']);
        $gallery = json_decode($prop['gallery'] ?? '[]', true) ?? [];
        foreach ($gallery as $img) deleteImage($img);
        db()->prepare('DELETE FROM properties WHERE id = ?')->execute([$id]);
        setFlash('success', 'Propriété supprimée.');
    }
} catch (PDOException $e) { setFlash('error', 'Erreur lors de la suppression.'); }
redirect(SITE_URL . '/admin/properties/index.php');
