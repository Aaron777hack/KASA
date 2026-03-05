<?php
/**
 * Page d'accueil dynamique - KASA Immobilier
 */
define('KASA_LOADED', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$pageTitle = 'Accueil';
$pageDesc  = 'KASA Immobilier - Trouvez votre bien immobilier en Côte d\'Ivoire';

// Propriétés vedettes (featured)
try {
    $stmtFeatured = db()->prepare("
        SELECT * FROM properties WHERE status = 'disponible' AND featured = 1
        ORDER BY created_at DESC LIMIT 6
    ");
    $stmtFeatured->execute();
    $featuredProperties = $stmtFeatured->fetchAll();
} catch (PDOException $e) {
    $featuredProperties = [];
}

// Derniers articles
try {
    $stmtArticles = db()->prepare("
        SELECT a.*, c.name as category_name, c.slug as category_slug
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'published'
        ORDER BY a.created_at DESC LIMIT 3
    ");
    $stmtArticles->execute();
    $latestArticles = $stmtArticles->fetchAll();
} catch (PDOException $e) {
    $latestArticles = [];
}

include __DIR__ . '/includes/header.php';
?>

    <!-- SLIDER -->
    <div class="ltn__slider-area ltn__slider-3 section-bg-1">
        <div class="ltn__slide-one-active slick-slide-arrow-1 slick-slide-dots-1">
            <div class="ltn__slide-item ltn__slide-item-2 ltn__slide-item-3-normal ltn__slide-item-3">
                <div class="ltn__slide-item-inner">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-12 align-self-center">
                                <div class="slide-item-info">
                                    <div class="slide-item-info-inner ltn__slide-animation">
                                        <h6 class="slide-sub-title white-color--- animated"><span><i class="fas fa-home"></i></span> Agence Immobilière</h6>
                                        <h1 class="slide-title animated">Trouvez Votre Bien<br>Immobilier de Rêve</h1>
                                        <div class="slide-brief animated">
                                            <p>Expert en immobilier à Abidjan, Côte d'Ivoire. Vente, location et gestion de biens.</p>
                                        </div>
                                        <div class="btn-wrapper animated">
                                            <a href="<?= SITE_URL ?>/shop.php" class="theme-btn-1 btn btn-effect-1">Voir les propriétés</a>
                                            <a href="<?= SITE_URL ?>/contact.php" class="btn btn-transparent btn-effect-3">Nous contacter</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="slide-item-img">
                                    <img src="<?= SITE_URL ?>/img/slider/21.png" alt="KASA Immobilier">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULAIRE DE RECHERCHE -->
    <div class="ltn__car-dealer-form-area mt--65 mt-120 pb-115---">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__car-dealer-form-tab">
                        <div class="tab-content bg-white box-shadow-1 ltn__border position-relative pb-10">
                            <div class="tab-pane fade active show" id="ltn__form_tab_1_1">
                                <div class="car-dealer-form-inner">
                                    <form action="<?= SITE_URL ?>/shop.php" method="GET" class="ltn__car-dealer-form-box row">
                                        <div class="ltn__car-dealer-form-item col-lg-3 col-md-6">
                                            <select name="location" class="nice-select">
                                                <option value="">Choisir une zone</option>
                                                <option value="cocody">Cocody</option>
                                                <option value="plateau">Plateau</option>
                                                <option value="marcory">Marcory</option>
                                                <option value="bingerville">Bingerville</option>
                                                <option value="yopougon">Yopougon</option>
                                                <option value="abobo">Abobo</option>
                                            </select>
                                        </div>
                                        <div class="ltn__car-dealer-form-item col-lg-3 col-md-6">
                                            <select name="price_type" class="nice-select">
                                                <option value="">Type de transaction</option>
                                                <option value="vente">En vente</option>
                                                <option value="location">En location</option>
                                                <option value="terrain">Terrain</option>
                                            </select>
                                        </div>
                                        <div class="ltn__car-dealer-form-item col-lg-3 col-md-6">
                                            <select name="property_type" class="nice-select">
                                                <option value="">Type de bien</option>
                                                <option value="duplex">Duplex</option>
                                                <option value="villa">Villa</option>
                                                <option value="appartement">Appartement</option>
                                                <option value="terrain">Terrain</option>
                                                <option value="bureau">Bureau</option>
                                            </select>
                                        </div>
                                        <div class="ltn__car-dealer-form-item col-lg-3 col-md-6">
                                            <div class="btn-wrapper text-center mt-0">
                                                <button type="submit" class="btn theme-btn-1 btn-effect-1 text-uppercase">Rechercher</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- À PROPOS -->
    <div class="ltn__about-us-area pt-120 pb-90">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 align-self-center">
                    <div class="about-us-img-wrap about-img-left">
                        <img src="<?= SITE_URL ?>/img/others/7.png" alt="KASA Immobilier">
                    </div>
                </div>
                <div class="col-lg-6 align-self-center">
                    <div class="about-us-info-wrap">
                        <div class="section-title-area ltn__section-title-2--- mb-20">
                            <h6 class="section-subtitle section-subtitle-2 ltn__secondary-color">À Propos de Nous</h6>
                            <h1 class="section-title">Le Leader de l'Immobilier en Côte d'Ivoire<span>.</span></h1>
                            <p>KASA Immobilier est votre partenaire de confiance pour toutes vos transactions immobilières à Abidjan et en Côte d'Ivoire. Vente, location, gestion et investissement.</p>
                        </div>
                        <ul class="ltn__list-item-half clearfix">
                            <li><i class="flaticon-home-2"></i> Design et qualité premium</li>
                            <li><i class="flaticon-mountain"></i> Cadre de vie exceptionnel</li>
                            <li><i class="flaticon-heart"></i> Service client personnalisé</li>
                            <li><i class="flaticon-secure"></i> Sécurité et transparence</li>
                        </ul>
                        <div class="btn-wrapper animated mt-30">
                            <a href="<?= SITE_URL ?>/about.php" class="theme-btn-1 btn btn-effect-1">En savoir plus</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SERVICES -->
    <div class="ltn__feature-area section-bg-1 pt-120 pb-90 mb-120---">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-area ltn__section-title-2--- text-center">
                        <h6 class="section-subtitle section-subtitle-2 ltn__secondary-color">Nos Services</h6>
                        <h1 class="section-title">Ce Que Nous Proposons</h1>
                    </div>
                </div>
            </div>
            <div class="row ltn__custom-gutter--- justify-content-center">
                <div class="col-lg-4 col-sm-6 col-12">
                    <div class="ltn__feature-item ltn__feature-item-6 text-center bg-white box-shadow-1">
                        <div class="ltn__feature-icon"><img src="<?= SITE_URL ?>/img/icons/icon-img/21.png" alt="Achat"></div>
                        <div class="ltn__feature-info">
                            <h3>Acheter un bien</h3>
                            <p>Des milliers de propriétés disponibles à la vente. Trouvez la maison de vos rêves.</p>
                            <a class="ltn__service-btn" href="<?= SITE_URL ?>/shop.php?price_type=vente">Voir les biens <i class="flaticon-right-arrow"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6 col-12">
                    <div class="ltn__feature-item ltn__feature-item-6 text-center bg-white box-shadow-1 active">
                        <div class="ltn__feature-icon"><img src="<?= SITE_URL ?>/img/icons/icon-img/22.png" alt="Location"></div>
                        <div class="ltn__feature-info">
                            <h3>Louer un bien</h3>
                            <p>Une large gamme de propriétés à louer dans tous les quartiers d'Abidjan.</p>
                            <a class="ltn__service-btn" href="<?= SITE_URL ?>/shop.php?price_type=location">Voir les biens <i class="flaticon-right-arrow"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6 col-12">
                    <div class="ltn__feature-item ltn__feature-item-6 text-center bg-white box-shadow-1">
                        <div class="ltn__feature-icon"><img src="<?= SITE_URL ?>/img/icons/icon-img/23.png" alt="Vendre"></div>
                        <div class="ltn__feature-info">
                            <h3>Vendre un bien</h3>
                            <p>Confiez-nous votre bien. Nous vous garantissons une vente rapide au meilleur prix.</p>
                            <a class="ltn__service-btn" href="<?= SITE_URL ?>/contact.php">Nous contacter <i class="flaticon-right-arrow"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PROPRIÉTÉS VEDETTES -->
    <?php if (!empty($featuredProperties)): ?>
    <div class="ltn__product-slider-area ltn__product-gutter pt-115 pb-90 plr--7">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-area ltn__section-title-2--- text-center">
                        <h6 class="section-subtitle section-subtitle-2 ltn__secondary-color">Propriétés</h6>
                        <h1 class="section-title">Nos Biens en Vedette</h1>
                    </div>
                </div>
            </div>
            <div class="row ltn__product-slider-item-four-active-full-width slick-arrow-1">
                <?php foreach ($featuredProperties as $prop): ?>
                <div class="col-lg-12">
                    <div class="ltn__product-item ltn__product-item-4 text-center---">
                        <div class="product-img">
                            <a href="<?= SITE_URL ?>/property-details.php?slug=<?= e($prop['slug']) ?>">
                                <img src="<?= e(imageUrl($prop['image'])) ?>" alt="<?= e($prop['title']) ?>">
                            </a>
                            <div class="product-badge">
                                <ul>
                                    <li class="sale-badge bg-green<?= $prop['price_type'] === 'vente' ? '---' : '' ?>">
                                        En <?= e($prop['price_type']) ?>
                                    </li>
                                </ul>
                            </div>
                            <div class="product-img-location-gallery">
                                <div class="product-img-location">
                                    <ul>
                                        <li><a href="#"><i class="flaticon-pin"></i> <?= e($prop['location']) ?></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-price">
                                <span><?= formatPrice((float)$prop['price'], $prop['price_type'] === 'location' ? '/Mois' : '') ?></span>
                            </div>
                            <h2 class="product-title">
                                <a href="<?= SITE_URL ?>/property-details.php?slug=<?= e($prop['slug']) ?>"><?= e($prop['title']) ?></a>
                            </h2>
                            <div class="product-description">
                                <p><?= e(truncate($prop['description'] ?? '', 100)) ?></p>
                            </div>
                            <ul class="ltn__list-item-2 ltn__list-item-2-before">
                                <?php if ($prop['bedrooms']): ?><li><span><?= (int)$prop['bedrooms'] ?> <i class="flaticon-bed"></i></span> Chambres</li><?php endif; ?>
                                <?php if ($prop['bathrooms']): ?><li><span><?= (int)$prop['bathrooms'] ?> <i class="flaticon-clean"></i></span> Douches</li><?php endif; ?>
                                <?php if ($prop['area']): ?><li><span><?= (int)$prop['area'] ?> <i class="flaticon-square-shape-design-interface-tool-symbol"></i></span> m²</li><?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-50">
                <a href="<?= SITE_URL ?>/shop.php" class="btn theme-btn-1 btn-effect-1">Voir toutes nos propriétés</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- DERNIERS ARTICLES -->
    <?php if (!empty($latestArticles)): ?>
    <div class="ltn__blog-area pt-120 pb-70">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title-area ltn__section-title-2--- text-center">
                        <h6 class="section-subtitle section-subtitle-2 ltn__secondary-color">Actualités</h6>
                        <h1 class="section-title">Derniers Articles</h1>
                    </div>
                </div>
            </div>
            <div class="row ltn__blog-slider-one-active slick-arrow-1 ltn__blog-item-3-normal">
                <?php foreach ($latestArticles as $article): ?>
                <div class="col-lg-12">
                    <div class="ltn__blog-item ltn__blog-item-3">
                        <?php if ($article['image']): ?>
                        <div class="ltn__blog-img">
                            <a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($article['slug']) ?>">
                                <img src="<?= e(imageUrl($article['image'])) ?>" alt="<?= e($article['title']) ?>">
                            </a>
                        </div>
                        <?php endif; ?>
                        <div class="ltn__blog-brief">
                            <div class="ltn__blog-meta">
                                <ul>
                                    <li class="ltn__blog-category">
                                        <a href="#"><?= e($article['category_name'] ?? 'Immobilier') ?></a>
                                    </li>
                                    <li class="ltn__blog-date">
                                        <i class="far fa-calendar-alt"></i><?= formatDate($article['created_at']) ?>
                                    </li>
                                </ul>
                            </div>
                            <h3 class="ltn__blog-title">
                                <a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($article['slug']) ?>"><?= e($article['title']) ?></a>
                            </h3>
                            <div class="ltn__blog-meta-btn">
                                <div class="ltn__blog-btn">
                                    <a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($article['slug']) ?>">Lire la suite <i class="fas fa-arrow-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
