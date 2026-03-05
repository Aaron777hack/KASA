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
    $article = db()->prepare('SELECT * FROM articles WHERE id = ?');
    $article->execute([$id]);
    $article = $article->fetch();
} catch (PDOException $e) { $article = null; }
if (!$article) redirect(SITE_URL . '/admin/articles/index.php');

$adminTitle = 'Modifier: ' . $article['title'];
$categories = db()->query("SELECT * FROM categories WHERE type = 'article' ORDER BY name")->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Requête invalide.';
    } else {
        $title      = sanitize($_POST['title']      ?? '');
        $excerpt    = sanitize($_POST['excerpt']    ?? '');
        $content    = $_POST['content']   ?? '';
        $categoryId = (int) ($_POST['category_id'] ?? 0) ?: null;
        $author     = sanitize($_POST['author']     ?? '');
        $status     = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'draft';
        $slug       = sanitize($_POST['slug'] ?? '') ?: uniqueSlug($title, 'articles', $id);

        if (empty($title))   $errors[] = 'Le titre est requis.';
        if (empty($content)) $errors[] = 'Le contenu est requis.';

        $imagePath = $article['image'];
        if (!empty($_FILES['image']['name'])) {
            $uploaded = uploadImage($_FILES['image'], 'articles');
            if ($uploaded) {
                if ($imagePath) deleteImage($imagePath);
                $imagePath = $uploaded;
            } else {
                $errors[] = 'Erreur lors de l\'upload de l\'image.';
            }
        }

        if (empty($errors)) {
            try {
                db()->prepare("
                    UPDATE articles SET title=?, slug=?, excerpt=?, content=?, image=?, category_id=?, author=?, status=?
                    WHERE id=?
                ")->execute([$title, $slug, $excerpt, $content, $imagePath, $categoryId, $author, $status, $id]);
                setFlash('success', 'Article mis à jour !');
                redirect(SITE_URL . '/admin/articles/index.php');
            } catch (PDOException $e) {
                $errors[] = 'Erreur : ' . $e->getMessage();
            }
        }
    }
}

// Valeurs du formulaire
$v = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $article;

include __DIR__ . '/../includes/admin-header.php';
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">
        <div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Contenu</h2></div>
                <div class="form-group">
                    <label class="form-label">Titre *</label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?= e($v['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" id="slug" name="slug" class="form-control" value="<?= e($v['slug'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Extrait</label>
                    <textarea name="excerpt" class="form-control" rows="3"><?= e($v['excerpt'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Contenu *</label>
                    <textarea name="content" class="form-control" rows="15"><?= e($v['content'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Publication</h2></div>
                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?= ($v['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Brouillon</option>
                        <option value="published" <?= ($v['status'] ?? '') === 'published' ? 'selected' : '' ?>>Publié</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <select name="category_id" class="form-control">
                        <option value="">Sans catégorie</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($v['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Auteur</label>
                    <input type="text" name="author" class="form-control" value="<?= e($v['author'] ?? '') ?>">
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save"></i> Enregistrer</button>
                    <a href="<?= SITE_URL ?>/admin/articles/index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Image</h2></div>
                <?php if ($article['image']): ?>
                <img src="<?= e(imageUrl($article['image'])) ?>" alt="" class="img-preview" style="display:block;margin-bottom:10px;">
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/*" data-preview="img-preview">
                <img id="img-preview" alt="Aperçu" class="img-preview" style="display:none;margin-top:10px;">
                <p style="font-size:12px;color:#999;margin-top:8px;">Laisser vide pour conserver l'image actuelle.</p>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
