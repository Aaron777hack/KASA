<?php
define('KASA_LOADED', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdminLogin();
$adminTitle = 'Créer un Article';

// Catégories
try {
    $categories = db()->query("SELECT * FROM categories WHERE type = 'article' ORDER BY name")->fetchAll();
} catch (PDOException $e) { $categories = []; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Requête invalide.';
    } else {
        $title      = sanitize($_POST['title']       ?? '');
        $excerpt    = sanitize($_POST['excerpt']     ?? '');
        $content    = $_POST['content']  ?? '';  // HTML du textarea
        $categoryId = (int) ($_POST['category_id']  ?? 0) ?: null;
        $author     = sanitize($_POST['author']      ?? 'Admin');
        $status     = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'draft';
        $slug       = sanitize($_POST['slug'] ?? '') ?: uniqueSlug($title, 'articles');

        if (empty($title))   $errors[] = 'Le titre est requis.';
        if (empty($content)) $errors[] = 'Le contenu est requis.';

        // Upload image
        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $uploaded = uploadImage($_FILES['image'], 'articles');
            if ($uploaded) {
                $imagePath = $uploaded;
            } else {
                $errors[] = 'Erreur lors de l\'upload de l\'image (format ou taille invalide).';
            }
        }

        if (empty($errors)) {
            try {
                $stmt = db()->prepare("
                    INSERT INTO articles (title, slug, excerpt, content, image, category_id, author, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$title, $slug, $excerpt, $content, $imagePath, $categoryId, $author, $status]);
                setFlash('success', 'Article créé avec succès !');
                redirect(SITE_URL . '/admin/articles/index.php');
            } catch (PDOException $e) {
                $errors[] = 'Erreur lors de la création : ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">
        <!-- Contenu principal -->
        <div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Contenu</h2></div>
                <div class="form-group">
                    <label class="form-label">Titre *</label>
                    <input type="text" id="title" name="title" class="form-control" required placeholder="Titre de l'article" value="<?= e($_POST['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Slug (URL)</label>
                    <input type="text" id="slug" name="slug" class="form-control" placeholder="url-de-larticle" value="<?= e($_POST['slug'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Extrait / Résumé</label>
                    <textarea name="excerpt" class="form-control" rows="3" placeholder="Court résumé affiché dans les listes..."><?= e($_POST['excerpt'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Contenu *</label>
                    <textarea name="content" id="content" class="form-control" rows="15" placeholder="Contenu de l'article..."><?= e($_POST['content'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <!-- Métadonnées -->
        <div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Publication</h2></div>
                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?= ($_POST['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                        <option value="published" <?= ($_POST['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publié</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <select name="category_id" class="form-control">
                        <option value="">Sans catégorie</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Auteur</label>
                    <input type="text" name="author" class="form-control" value="<?= e($_POST['author'] ?? getCurrentAdmin()['name']) ?>">
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save"></i> Publier</button>
                    <a href="<?= SITE_URL ?>/admin/articles/index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Image à la une</h2></div>
                <div class="form-group">
                    <input type="file" name="image" class="form-control" accept="image/*" data-preview="img-preview">
                    <img id="img-preview" src="" alt="Aperçu" class="img-preview" style="display:none;margin-top:10px;">
                </div>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
