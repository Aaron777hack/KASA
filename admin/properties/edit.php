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
    $s = db()->prepare('SELECT * FROM properties WHERE id = ?');
    $s->execute([$id]);
    $property = $s->fetch();
} catch (PDOException $e) { $property = null; }
if (!$property) redirect(SITE_URL . '/admin/properties/index.php');

$adminTitle = 'Modifier: ' . $property['title'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Requête invalide.';
    } else {
        $title        = sanitize($_POST['title']         ?? '');
        $description  = sanitize($_POST['description']   ?? '');
        $price        = (float) str_replace([' ', ','], ['', '.'], $_POST['price'] ?? '0');
        $priceType    = in_array($_POST['price_type'] ?? '', ['vente','location','terrain']) ? $_POST['price_type'] : 'vente';
        $propertyType = sanitize($_POST['property_type'] ?? '');
        $location     = sanitize($_POST['location']      ?? '');
        $bedrooms     = (int) ($_POST['bedrooms']        ?? 0);
        $bathrooms    = (int) ($_POST['bathrooms']       ?? 0);
        $area         = (int) ($_POST['area']            ?? 0);
        $status       = in_array($_POST['status'] ?? '', ['disponible','vendu','loue']) ? $_POST['status'] : 'disponible';
        $featured     = isset($_POST['featured']) ? 1 : 0;
        $slug         = sanitize($_POST['slug'] ?? '') ?: uniqueSlug($title, 'properties', $id);

        if (empty($title))    $errors[] = 'Le titre est requis.';
        if (empty($location)) $errors[] = 'La localisation est requise.';

        $imagePath = $property['image'];
        if (!empty($_FILES['image']['name'])) {
            $uploaded = uploadImage($_FILES['image'], 'properties');
            if ($uploaded) { if ($imagePath) deleteImage($imagePath); $imagePath = $uploaded; }
            else $errors[] = 'Erreur upload image.';
        }

        $galleryPaths = json_decode($property['gallery'] ?? '[]', true) ?? [];
        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['name'] as $k => $name) {
                if ($name && $_FILES['gallery']['error'][$k] === 0) {
                    $file = ['name' => $name, 'type' => $_FILES['gallery']['type'][$k], 'tmp_name' => $_FILES['gallery']['tmp_name'][$k], 'error' => $_FILES['gallery']['error'][$k], 'size' => $_FILES['gallery']['size'][$k]];
                    $up = uploadImage($file, 'properties');
                    if ($up) $galleryPaths[] = $up;
                }
            }
        }

        if (empty($errors)) {
            try {
                db()->prepare("UPDATE properties SET title=?,slug=?,description=?,price=?,price_type=?,property_type=?,location=?,bedrooms=?,bathrooms=?,area=?,image=?,gallery=?,status=?,featured=? WHERE id=?")
                   ->execute([$title,$slug,$description,$price,$priceType,$propertyType,$location,$bedrooms,$bathrooms,$area,$imagePath,json_encode($galleryPaths),$status,$featured,$id]);
                setFlash('success', 'Propriété mise à jour !');
                redirect(SITE_URL . '/admin/properties/index.php');
            } catch (PDOException $e) {
                $errors[] = 'Erreur BD : ' . $e->getMessage();
            }
        }
    }
}

$v = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $property;
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
                <div class="card-header"><h2 class="card-title">Informations</h2></div>
                <div class="form-group"><label class="form-label">Titre *</label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?= e($v['title'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Slug</label>
                    <input type="text" id="slug" name="slug" class="form-control" value="<?= e($v['slug'] ?? '') ?>"></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Type transaction</label>
                        <select name="price_type" class="form-control">
                            <?php foreach (['vente','location','terrain'] as $pt): ?>
                            <option value="<?= $pt ?>" <?= ($v['price_type'] ?? '') === $pt ? 'selected' : '' ?>><?= ucfirst($pt) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div class="form-group"><label class="form-label">Type bien</label>
                        <select name="property_type" class="form-control">
                            <?php foreach (['Duplex','Villa','Appartement','Terrain','Bureau','Commerce'] as $pt): ?>
                            <option value="<?= $pt ?>" <?= ($v['property_type'] ?? '') === $pt ? 'selected' : '' ?>><?= $pt ?></option>
                            <?php endforeach; ?>
                        </select></div>
                </div>
                <div class="form-group"><label class="form-label">Localisation *</label>
                    <input type="text" name="location" class="form-control" value="<?= e($v['location'] ?? '') ?>"></div>
                <div class="form-group"><label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="8"><?= e($v['description'] ?? '') ?></textarea></div>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Chambres</label>
                        <input type="number" name="bedrooms" class="form-control" min="0" value="<?= (int)($v['bedrooms'] ?? 0) ?>"></div>
                    <div class="form-group"><label class="form-label">Douches</label>
                        <input type="number" name="bathrooms" class="form-control" min="0" value="<?= (int)($v['bathrooms'] ?? 0) ?>"></div>
                </div>
                <div class="form-group"><label class="form-label">Surface (m²)</label>
                    <input type="number" name="area" class="form-control" min="0" value="<?= (int)($v['area'] ?? 0) ?>"></div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Prix & Statut</h2></div>
                <div class="form-group"><label class="form-label">Prix (FCFA)</label>
                    <input type="number" name="price" class="form-control" min="0" step="1000" value="<?= (float)($v['price'] ?? 0) ?>"></div>
                <div class="form-group"><label class="form-label">Statut</label>
                    <select name="status" class="form-control">
                        <?php foreach (['disponible','vendu','loue'] as $st): ?>
                        <option value="<?= $st ?>" <?= ($v['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                        <input type="checkbox" name="featured" value="1" <?= ($v['featured'] ?? 0) ? 'checked' : '' ?> style="width:18px;height:18px;">
                        <span>⭐ Propriété vedette</span>
                    </label>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save"></i> Enregistrer</button>
                    <a href="<?= SITE_URL ?>/admin/properties/index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Image principale</h2></div>
                <?php if ($property['image']): ?>
                <img src="<?= e(imageUrl($property['image'])) ?>" alt="" class="img-preview" style="display:block;margin-bottom:10px;">
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/*" data-preview="img-preview">
                <img id="img-preview" alt="" class="img-preview" style="display:none;margin-top:10px;">
                <p style="font-size:12px;color:#999;margin-top:8px;">Laisser vide pour conserver l'actuelle.</p>
            </div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Galerie</h2></div>
                <?php
                $gallery = json_decode($property['gallery'] ?? '[]', true) ?? [];
                if (!empty($gallery)):
                ?>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                    <?php foreach ($gallery as $img): ?>
                    <img src="<?= e(imageUrl($img)) ?>" alt="" style="width:70px;height:60px;object-fit:cover;border-radius:4px;">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                <p style="font-size:12px;color:#999;margin-top:8px;">Les nouvelles images s'ajoutent aux existantes.</p>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
