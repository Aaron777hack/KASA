<?php
define('KASA_LOADED', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdminLogin();
$adminTitle = 'Nouvelle Propriété';
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
        $slug         = sanitize($_POST['slug'] ?? '') ?: uniqueSlug($title, 'properties');

        if (empty($title))    $errors[] = 'Le titre est requis.';
        if (empty($location)) $errors[] = 'La localisation est requise.';

        $imagePath = null;
        if (!empty($_FILES['image']['name'])) {
            $uploaded = uploadImage($_FILES['image'], 'properties');
            if ($uploaded) $imagePath = $uploaded;
            else $errors[] = 'Erreur upload image principale.';
        }

        // Galerie multiple
        $galleryPaths = [];
        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['name'] as $k => $name) {
                if ($name && $_FILES['gallery']['error'][$k] === 0) {
                    $file = [
                        'name'     => $_FILES['gallery']['name'][$k],
                        'type'     => $_FILES['gallery']['type'][$k],
                        'tmp_name' => $_FILES['gallery']['tmp_name'][$k],
                        'error'    => $_FILES['gallery']['error'][$k],
                        'size'     => $_FILES['gallery']['size'][$k],
                    ];
                    $up = uploadImage($file, 'properties');
                    if ($up) $galleryPaths[] = $up;
                }
            }
        }

        if (empty($errors)) {
            try {
                db()->prepare("
                    INSERT INTO properties (title, slug, description, price, price_type, property_type, location, bedrooms, bathrooms, area, image, gallery, status, featured)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
                ")->execute([$title, $slug, $description, $price, $priceType, $propertyType, $location, $bedrooms, $bathrooms, $area, $imagePath, json_encode($galleryPaths), $status, $featured]);

                // Notifier les abonnés de la newsletter
                $newsletterSent = sendPropertyNewsletter([
                    'title'         => $title,
                    'slug'          => $slug,
                    'description'   => $description,
                    'price'         => $price,
                    'price_type'    => $priceType,
                    'property_type' => $propertyType,
                    'location'      => $location,
                    'bedrooms'      => $bedrooms,
                    'bathrooms'     => $bathrooms,
                    'area'          => $area,
                ]);

                $flashMsg = 'Propriété créée avec succès !';
                if ($newsletterSent > 0) {
                    $flashMsg .= ' Newsletter envoyée à ' . $newsletterSent . ' abonné(s).';
                }
                setFlash('success', $flashMsg);
                redirect(SITE_URL . '/admin/properties/index.php');
            } catch (PDOException $e) {
                $errors[] = 'Erreur BD : ' . $e->getMessage();
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
        <div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Informations</h2></div>
                <div class="form-group">
                    <label class="form-label">Titre *</label>
                    <input type="text" id="title" name="title" class="form-control" required value="<?= e($_POST['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Slug</label>
                    <input type="text" id="slug" name="slug" class="form-control" value="<?= e($_POST['slug'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Type de transaction *</label>
                        <select name="price_type" class="form-control">
                            <option value="vente" <?= ($_POST['price_type'] ?? '') === 'vente' ? 'selected' : '' ?>>En vente</option>
                            <option value="location" <?= ($_POST['price_type'] ?? '') === 'location' ? 'selected' : '' ?>>En location</option>
                            <option value="terrain" <?= ($_POST['price_type'] ?? '') === 'terrain' ? 'selected' : '' ?>>Terrain</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type de bien</label>
                        <select name="property_type" class="form-control">
                            <option value="">Sélectionner...</option>
                            <option value="Duplex" <?= ($_POST['property_type'] ?? '') === 'Duplex' ? 'selected' : '' ?>>Duplex</option>
                            <option value="Villa" <?= ($_POST['property_type'] ?? '') === 'Villa' ? 'selected' : '' ?>>Villa</option>
                            <option value="Appartement" <?= ($_POST['property_type'] ?? '') === 'Appartement' ? 'selected' : '' ?>>Appartement</option>
                            <option value="Terrain" <?= ($_POST['property_type'] ?? '') === 'Terrain' ? 'selected' : '' ?>>Terrain</option>
                            <option value="Bureau" <?= ($_POST['property_type'] ?? '') === 'Bureau' ? 'selected' : '' ?>>Bureau</option>
                            <option value="Commerce" <?= ($_POST['property_type'] ?? '') === 'Commerce' ? 'selected' : '' ?>>Commerce</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Localisation *</label>
                    <input type="text" name="location" class="form-control" placeholder="ex: Cocody, Attoban, Abidjan" value="<?= e($_POST['location'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="8" placeholder="Description détaillée du bien..."><?= e($_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Chambres</label>
                        <input type="number" name="bedrooms" class="form-control" min="0" value="<?= (int)($_POST['bedrooms'] ?? 0) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Douches</label>
                        <input type="number" name="bathrooms" class="form-control" min="0" value="<?= (int)($_POST['bathrooms'] ?? 0) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Surface (m²)</label>
                    <input type="number" name="area" class="form-control" min="0" value="<?= (int)($_POST['area'] ?? 0) ?>">
                </div>
            </div>
        </div>
        <div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Prix & Statut</h2></div>
                <div class="form-group">
                    <label class="form-label">Prix (FCFA) *</label>
                    <input type="number" name="price" class="form-control" min="0" step="1000" value="<?= (float)($_POST['price'] ?? 0) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Statut</label>
                    <select name="status" class="form-control">
                        <option value="disponible" <?= ($_POST['status'] ?? 'disponible') === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                        <option value="vendu" <?= ($_POST['status'] ?? '') === 'vendu' ? 'selected' : '' ?>>Vendu</option>
                        <option value="loue" <?= ($_POST['status'] ?? '') === 'loue' ? 'selected' : '' ?>>Loué</option>
                    </select>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                        <input type="checkbox" name="featured" value="1" <?= isset($_POST['featured']) ? 'checked' : '' ?> style="width:18px;height:18px;">
                        <span class="form-label" style="margin:0;">⭐ Propriété vedette (homepage)</span>
                    </label>
                </div>
                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save"></i> Créer</button>
                    <a href="<?= SITE_URL ?>/admin/properties/index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Image principale</h2></div>
                <input type="file" name="image" class="form-control" accept="image/*" data-preview="img-preview">
                <img id="img-preview" alt="Aperçu" class="img-preview" style="display:none;margin-top:10px;">
            </div>
            <div class="card">
                <div class="card-header"><h2 class="card-title">Galerie (photos supplémentaires)</h2></div>
                <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                <p style="font-size:12px;color:#999;margin-top:8px;">Sélectionnez plusieurs images en même temps.</p>
            </div>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
