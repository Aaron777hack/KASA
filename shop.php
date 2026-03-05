<?php
/**
 * Page propriétés - Liste des annonces immobilières
 */
define('KASA_LOADED', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$pageTitle = 'Nos Propriétés';
$pageDesc  = 'Découvrez toutes nos propriétés immobilières en Côte d\'Ivoire - Vente et location';

// Filtres
$currentPage  = max(1, (int) getParam('page', 1));
$priceType    = getParam('price_type', '');
$propertyType = getParam('property_type', '');
$location     = getParam('location', '');
$sortBy       = getParam('sort', 'newest');
$search       = getParam('search', '');

// Construire la requête
$where  = ["p.status = 'disponible'"];
$params = [];

if ($priceType) {
    $where[]  = 'p.price_type = ?';
    $params[] = $priceType;
}
if ($propertyType) {
    $where[]  = 'p.property_type LIKE ?';
    $params[] = "%$propertyType%";
}
if ($location) {
    $where[]  = 'p.location LIKE ?';
    $params[] = "%$location%";
}
if ($search) {
    $where[]  = '(p.title LIKE ? OR p.description LIKE ? OR p.location LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = implode(' AND ', $where);

$orderSQL = match($sortBy) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    default      => 'p.created_at DESC',
};

// Compter le total
try {
    $stmtCount = db()->prepare("SELECT COUNT(*) FROM properties p WHERE $whereSQL");
    $stmtCount->execute($params);
    $total = (int) $stmtCount->fetchColumn();
} catch (PDOException $e) {
    $total = 0;
}

$pagination = getPagination($total, $currentPage);

// Récupérer les propriétés
try {
    $stmt = db()->prepare("
        SELECT * FROM properties p
        WHERE $whereSQL
        ORDER BY $orderSQL
        LIMIT ? OFFSET ?
    ");
    $stmt->execute(array_merge($params, [$pagination['per_page'], $pagination['offset']]));
    $properties = $stmt->fetchAll();
} catch (PDOException $e) {
    $properties = [];
}

include __DIR__ . '/includes/header.php';
?>

    <!-- BREADCRUMB -->
    <div class="ltn__breadcrumb-area text-left bg-overlay-white-30 bg-image" data-bs-bg="<?= SITE_URL ?>/img/bg/14.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__breadcrumb-inner">
                        <h1 class="page-title">Nos Propriétés</h1>
                        <div class="ltn__breadcrumb-list">
                            <ul>
                                <li><a href="<?= SITE_URL ?>/index.php"><span class="ltn__secondary-color"><i class="fas fa-home"></i></span> Accueil</a></li>
                                <li>Propriétés</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PROPERTY LISTING -->
    <div class="ltn__product-area ltn__product-gutter mb-100">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <!-- Barre de filtre / tri -->
                    <div class="ltn__shop-options">
                        <ul class="justify-content-start">
                            <li>
                                <div class="ltn__grid-list-tab-menu">
                                    <div class="nav">
                                        <a class="active show" data-bs-toggle="tab" href="#liton_product_grid"><i class="fas fa-th-large"></i></a>
                                        <a data-bs-toggle="tab" href="#liton_product_list"><i class="fas fa-list"></i></a>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="showing-product-number">
                                    <span>Affichage de <?= ($pagination['offset'] + 1) ?>-<?= min($pagination['offset'] + $pagination['per_page'], $total) ?> sur <?= $total ?> résultats</span>
                                </div>
                            </li>
                            <li>
                                <form method="GET" id="filter-form" class="d-flex align-items-center gap-10">
                                    <select name="price_type" class="nice-select" onchange="this.form.submit()">
                                        <option value="">Type de transaction</option>
                                        <option value="vente" <?= $priceType === 'vente' ? 'selected' : '' ?>>En vente</option>
                                        <option value="location" <?= $priceType === 'location' ? 'selected' : '' ?>>En location</option>
                                        <option value="terrain" <?= $priceType === 'terrain' ? 'selected' : '' ?>>Terrain</option>
                                    </select>
                                    <select name="sort" class="nice-select" onchange="this.form.submit()">
                                        <option value="newest" <?= $sortBy === 'newest' ? 'selected' : '' ?>>Plus récents</option>
                                        <option value="price_asc" <?= $sortBy === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                                        <option value="price_desc" <?= $sortBy === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                                    </select>
                                    <?php if ($propertyType): ?><input type="hidden" name="property_type" value="<?= e($propertyType) ?>"><?php endif; ?>
                                    <?php if ($location): ?><input type="hidden" name="location" value="<?= e($location) ?>"><?php endif; ?>
                                    <?php if ($search): ?><input type="hidden" name="search" value="<?= e($search) ?>"><?php endif; ?>
                                </form>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        <!-- Grille -->
                        <div class="tab-pane fade active show" id="liton_product_grid">
                            <div class="ltn__product-tab-content-inner ltn__product-grid-view">
                                <div class="row">
                                    <?php if (empty($properties)): ?>
                                    <div class="col-12 text-center py-60">
                                        <i class="fas fa-home fa-3x text-muted mb-20"></i>
                                        <h3>Aucune propriété trouvée</h3>
                                        <p>Modifiez vos filtres ou revenez bientôt.</p>
                                        <a href="<?= SITE_URL ?>/shop.php" class="btn theme-btn-1 mt-20">Voir toutes les propriétés</a>
                                    </div>
                                    <?php else: ?>
                                    <?php foreach ($properties as $prop): ?>
                                    <div class="col-xl-4 col-sm-6 col-12">
                                        <div class="ltn__product-item ltn__product-item-4 ltn__product-item-5 text-center---">
                                            <div class="product-img">
                                                <a href="<?= SITE_URL ?>/property-details.php?slug=<?= e($prop['slug']) ?>">
                                                    <img src="<?= e(imageUrl($prop['image'])) ?>" alt="<?= e($prop['title']) ?>">
                                                </a>
                                            </div>
                                            <div class="product-info">
                                                <div class="product-badge">
                                                    <ul>
                                                        <li class="sale-badg">En <?= e($prop['price_type']) ?></li>
                                                    </ul>
                                                </div>
                                                <h2 class="product-title">
                                                    <a href="<?= SITE_URL ?>/property-details.php?slug=<?= e($prop['slug']) ?>"><?= e($prop['title']) ?></a>
                                                </h2>
                                                <div class="product-img-location">
                                                    <ul>
                                                        <li><a href="#"><i class="flaticon-pin"></i> <?= e($prop['location']) ?></a></li>
                                                    </ul>
                                                </div>
                                                <ul class="ltn__list-item-2--- ltn__list-item-2-before--- ltn__plot-brief">
                                                    <?php if ($prop['bedrooms']): ?>
                                                    <li><span><?= (int)$prop['bedrooms'] ?></span> chambres</li>
                                                    <?php endif; ?>
                                                    <?php if ($prop['bathrooms']): ?>
                                                    <li><span><?= (int)$prop['bathrooms'] ?></span> douches</li>
                                                    <?php endif; ?>
                                                    <?php if ($prop['area']): ?>
                                                    <li><span><?= (int)$prop['area'] ?></span> m²</li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                            <div class="product-info-bottom">
                                                <div class="product-price">
                                                    <span><?= formatPrice((float)$prop['price'], $prop['price_type'] === 'location' ? '/Mois' : '') ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Liste -->
                        <div class="tab-pane fade" id="liton_product_list">
                            <div class="ltn__product-tab-content-inner ltn__product-list-view">
                                <div class="row">
                                    <?php foreach ($properties as $prop): ?>
                                    <div class="col-lg-12">
                                        <div class="ltn__product-item ltn__product-item-4 ltn__product-item-5">
                                            <div class="product-img">
                                                <a href="<?= SITE_URL ?>/property-details.php?slug=<?= e($prop['slug']) ?>">
                                                    <img src="<?= e(imageUrl($prop['image'])) ?>" alt="<?= e($prop['title']) ?>">
                                                </a>
                                            </div>
                                            <div class="product-info">
                                                <div class="product-badge"><ul><li class="sale-badg">En <?= e($prop['price_type']) ?></li></ul></div>
                                                <h2 class="product-title">
                                                    <a href="<?= SITE_URL ?>/property-details.php?slug=<?= e($prop['slug']) ?>"><?= e($prop['title']) ?></a>
                                                </h2>
                                                <div class="product-img-location">
                                                    <ul><li><a href="#"><i class="flaticon-pin"></i> <?= e($prop['location']) ?></a></li></ul>
                                                </div>
                                                <p><?= e(truncate($prop['description'] ?? '', 150)) ?></p>
                                            </div>
                                            <div class="product-info-bottom">
                                                <div class="product-price">
                                                    <span><?= formatPrice((float)$prop['price'], $prop['price_type'] === 'location' ? '/Mois' : '') ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="ltn__pagination-area text-center">
                        <div class="ltn__pagination">
                            <ul>
                                <?php if ($pagination['has_prev']): ?>
                                <li><a href="?page=<?= $pagination['prev_page'] ?>&price_type=<?= e($priceType) ?>&sort=<?= e($sortBy) ?>"><i class="fas fa-angle-double-left"></i></a></li>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="<?= $i === $pagination['current'] ? 'active' : '' ?>">
                                    <a href="?page=<?= $i ?>&price_type=<?= e($priceType) ?>&sort=<?= e($sortBy) ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                                <?php if ($pagination['has_next']): ?>
                                <li><a href="?page=<?= $pagination['next_page'] ?>&price_type=<?= e($priceType) ?>&sort=<?= e($sortBy) ?>"><i class="fas fa-angle-double-right"></i></a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
