<?php
/**
 * Page détail propriété - KASA Immobilier
 */
define('KASA_LOADED', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$slug = getParam('slug', '');
if (!$slug) {
    redirect(SITE_URL . '/shop.php');
}

// Récupérer la propriété
try {
    $stmt = db()->prepare("SELECT * FROM properties WHERE slug = ?");
    $stmt->execute([$slug]);
    $property = $stmt->fetch();
} catch (PDOException $e) {
    $property = null;
}

if (!$property) {
    header('HTTP/1.0 404 Not Found');
    redirect(SITE_URL . '/shop.php');
}

$pageTitle = $property['title'];
$pageDesc  = truncate($property['description'] ?? '', 160);

// Propriétés similaires
try {
    $stmtSimilar = db()->prepare("
        SELECT * FROM properties
        WHERE status = 'disponible' AND id != ? AND price_type = ?
        ORDER BY created_at DESC LIMIT 3
    ");
    $stmtSimilar->execute([$property['id'], $property['price_type']]);
    $similarProperties = $stmtSimilar->fetchAll();
} catch (PDOException $e) {
    $similarProperties = [];
}

// Galerie
$gallery = [];
if ($property['gallery']) {
    $gallery = json_decode($property['gallery'], true) ?? [];
}

include __DIR__ . '/includes/header.php';
?>

    <!-- BREADCRUMB -->
    <div class="ltn__breadcrumb-area text-left bg-overlay-white-30 bg-image" data-bs-bg="<?= SITE_URL ?>/img/bg/14.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__breadcrumb-inner">
                        <h1 class="page-title"><?= e($property['title']) ?></h1>
                        <div class="ltn__breadcrumb-list">
                            <ul>
                                <li><a href="<?= SITE_URL ?>/index.php"><span class="ltn__secondary-color"><i class="fas fa-home"></i></span> Accueil</a></li>
                                <li><a href="<?= SITE_URL ?>/shop.php">Propriétés</a></li>
                                <li><?= e(truncate($property['title'], 40)) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PROPERTY DETAILS -->
    <div class="ltn__shop-details-area pb-10">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="ltn__shop-details-inner ltn__page-details-inner mb-60">
                        <!-- Image principale -->
                        <div class="ltn__property-details-info-wrapper mb-40">
                            <div class="ltn__shop-details-img-gallery">
                                <div class="ltn__slider-tab-menu-active">
                                    <div class="ltn__shop-details-big-img slick-slide-product-thumbnail">
                                        <div>
                                            <img src="<?= e(imageUrl($property['image'])) ?>" alt="<?= e($property['title']) ?>" class="w-100">
                                        </div>
                                        <?php foreach ($gallery as $galleryImg): ?>
                                        <div>
                                            <img src="<?= e(imageUrl($galleryImg)) ?>" alt="<?= e($property['title']) ?>" class="w-100">
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Infos principales -->
                        <label class="badge badge-<?= $property['price_type'] === 'vente' ? 'danger' : 'success' ?>">
                            En <?= e($property['price_type']) ?>
                        </label>

                        <h1><?= e($property['title']) ?></h1>

                        <div class="product-img-location">
                            <ul>
                                <li><a href="#"><i class="flaticon-pin"></i> <?= e($property['location']) ?></a></li>
                            </ul>
                        </div>

                        <div class="ltn__property-details-info-list section-bg-1 clearfix mb-60">
                            <ul>
                                <?php if ($property['property_type']): ?>
                                <li><label>Type :</label> <span><?= e($property['property_type']) ?></span></li>
                                <?php endif; ?>
                                <?php if ($property['bedrooms']): ?>
                                <li><label>Chambres :</label> <span><?= (int)$property['bedrooms'] ?></span></li>
                                <?php endif; ?>
                                <?php if ($property['bathrooms']): ?>
                                <li><label>Douches :</label> <span><?= (int)$property['bathrooms'] ?></span></li>
                                <?php endif; ?>
                                <?php if ($property['area']): ?>
                                <li><label>Surface :</label> <span><?= (int)$property['area'] ?> m²</span></li>
                                <?php endif; ?>
                                <li><label>Statut :</label> <span><?= e(ucfirst($property['status'])) ?></span></li>
                                <li><label>Référence :</label> <span>KASA-<?= str_pad($property['id'], 4, '0', STR_PAD_LEFT) ?></span></li>
                            </ul>
                        </div>

                        <!-- Description -->
                        <?php if ($property['description']): ?>
                        <div class="ltn__property-details-info-wrapper">
                            <h4 class="title-2">Description</h4>
                            <div class="property-description">
                                <?= nl2br(e($property['description'])) ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Prix -->
                    <aside class="sidebar ltn__shop-sidebar ltn__right-sidebar---">
                        <div class="widget ltn__price-filter-widget">
                            <div class="product-price">
                                <h2><?= formatPrice((float)$property['price'], $property['price_type'] === 'location' ? '/Mois' : '') ?></h2>
                            </div>
                        </div>

                        <!-- Formulaire de contact rapide -->
                        <div class="widget">
                            <div class="ltn__form-box contact-form-box box-shadow white-bg">
                                <h4 class="title-2">Renseignements</h4>
                                <form id="property-contact-form" action="<?= SITE_URL ?>/api/contact.php" method="post">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="hidden" name="service" value="Renseignement propriété: <?= e($property['title']) ?>">
                                    <div class="input-item input-item-name ltn__custom-icon">
                                        <input type="text" name="name" placeholder="Votre nom *" required>
                                    </div>
                                    <div class="input-item input-item-email ltn__custom-icon">
                                        <input type="email" name="email" placeholder="Votre email *" required>
                                    </div>
                                    <div class="input-item input-item-phone ltn__custom-icon">
                                        <input type="text" name="phone" placeholder="Numéro de téléphone">
                                    </div>
                                    <div class="input-item input-item-textarea ltn__custom-icon">
                                        <textarea name="message" placeholder="Votre message...">Bonjour, je suis intéressé(e) par la propriété : <?= e($property['title']) ?> (Réf. KASA-<?= str_pad($property['id'], 4, '0', STR_PAD_LEFT) ?>).</textarea>
                                    </div>
                                    <div class="btn-wrapper">
                                        <button class="btn theme-btn-1 btn-effect-1 text-uppercase" type="submit">Envoyer</button>
                                    </div>
                                    <p class="form-messege mb-0 mt-20"></p>
                                </form>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>

    <!-- Propriétés similaires -->
    <?php if (!empty($similarProperties)): ?>
    <div class="ltn__product-slider-area ltn__product-gutter pt-115 pb-90 plr--7">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-area ltn__section-title-2--- text-center">
                        <h6 class="section-subtitle section-subtitle-2 ltn__secondary-color">Similaires</h6>
                        <h1 class="section-title">Propriétés Similaires</h1>
                    </div>
                </div>
            </div>
            <div class="row">
                <?php foreach ($similarProperties as $sim): ?>
                <div class="col-lg-4 col-sm-6">
                    <div class="ltn__product-item ltn__product-item-4 text-center---">
                        <div class="product-img">
                            <a href="<?= SITE_URL ?>/property-details.php?slug=<?= e($sim['slug']) ?>">
                                <img src="<?= e(imageUrl($sim['image'])) ?>" alt="<?= e($sim['title']) ?>">
                            </a>
                        </div>
                        <div class="product-info">
                            <div class="product-price"><span><?= formatPrice((float)$sim['price']) ?></span></div>
                            <h2 class="product-title"><a href="<?= SITE_URL ?>/property-details.php?slug=<?= e($sim['slug']) ?>"><?= e($sim['title']) ?></a></h2>
                            <div class="product-img-location"><ul><li><a href="#"><i class="flaticon-pin"></i> <?= e($sim['location']) ?></a></li></ul></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

<script>
// Formulaire de contact propriété
$('#property-contact-form').submit(function(e) {
    e.preventDefault();
    var $form = $(this);
    var $msg  = $form.find('.form-messege');
    $.post($form.attr('action'), $form.serialize(), function(res) {
        $msg.text(res.message).css('color', res.success ? '#28a745' : '#dc3545');
        if (res.success) {
            $form.find('input:not([type=hidden]), textarea').val('');
        }
    }, 'json').fail(function() {
        $msg.text('Une erreur est survenue.').css('color', '#dc3545');
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
