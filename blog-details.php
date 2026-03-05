<?php
/**
 * Page détail article - KASA Immobilier
 */
define('KASA_LOADED', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$slug = getParam('slug', '');
if (!$slug) {
    redirect(SITE_URL . '/blog.php');
}

// Récupérer l'article
try {
    $stmt = db()->prepare("
        SELECT a.*, c.name as category_name, c.slug as category_slug
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE a.slug = ? AND a.status = 'published'
    ");
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
} catch (PDOException $e) {
    $article = null;
}

if (!$article) {
    header('HTTP/1.0 404 Not Found');
    redirect(SITE_URL . '/blog.php');
}

// Incrémenter les vues
incrementViews($article['id'], 'articles');

$pageTitle = $article['title'];
$pageDesc  = $article['excerpt'] ?? truncate($article['content'] ?? '', 160);

// Articles récents (sidebar)
try {
    $stmtRecent = db()->prepare("
        SELECT id, title, slug, image, created_at FROM articles
        WHERE status = 'published' AND id != ?
        ORDER BY created_at DESC LIMIT 5
    ");
    $stmtRecent->execute([$article['id']]);
    $recentArticles = $stmtRecent->fetchAll();
} catch (PDOException $e) {
    $recentArticles = [];
}

// Articles connexes
try {
    $stmtRelated = db()->prepare("
        SELECT id, title, slug, image, created_at
        FROM articles
        WHERE status = 'published' AND id != ? AND category_id = ?
        ORDER BY created_at DESC LIMIT 3
    ");
    $stmtRelated->execute([$article['id'], $article['category_id']]);
    $relatedArticles = $stmtRelated->fetchAll();
} catch (PDOException $e) {
    $relatedArticles = [];
}

include __DIR__ . '/includes/header.php';
?>

    <!-- BREADCRUMB -->
    <div class="ltn__breadcrumb-area text-left bg-overlay-white-30 bg-image" data-bs-bg="<?= SITE_URL ?>/img/bg/14.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__breadcrumb-inner">
                        <h1 class="page-title"><?= e($article['title']) ?></h1>
                        <div class="ltn__breadcrumb-list">
                            <ul>
                                <li><a href="<?= SITE_URL ?>/index.php"><span class="ltn__secondary-color"><i class="fas fa-home"></i></span> Accueil</a></li>
                                <li><a href="<?= SITE_URL ?>/blog.php">Actualités</a></li>
                                <li><?= e(truncate($article['title'], 40)) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ARTICLE DETAIL -->
    <div class="ltn__blog-details-wrap">
        <div class="container">
            <div class="row">
                <!-- Contenu article -->
                <div class="col-lg-8">
                    <div class="ltn__blog-details-wrap">
                        <div class="ltn__page-details-inner ltn__blog-details-inner">
                            <?php if ($article['image']): ?>
                            <div class="mb-60">
                                <img src="<?= e(imageUrl($article['image'])) ?>" alt="<?= e($article['title']) ?>" class="w-100">
                            </div>
                            <?php endif; ?>

                            <div class="ltn__blog-meta">
                                <ul>
                                    <?php if ($article['category_name']): ?>
                                    <li class="ltn__blog-category">
                                        <a href="<?= SITE_URL ?>/blog.php?category=<?= e($article['category_slug']) ?>"><?= e($article['category_name']) ?></a>
                                    </li>
                                    <?php endif; ?>
                                    <li class="ltn__blog-date">
                                        <i class="far fa-calendar-alt"></i><?= formatDate($article['created_at']) ?>
                                    </li>
                                    <li><a href="#"><i class="far fa-eye"></i> <?= (int)$article['views'] ?> vues</a></li>
                                    <li><a href="#"><i class="far fa-user"></i> <?= e($article['author']) ?></a></li>
                                </ul>
                            </div>

                            <h2><?= e($article['title']) ?></h2>

                            <?php if ($article['excerpt']): ?>
                            <blockquote>
                                <p><?= e($article['excerpt']) ?></p>
                            </blockquote>
                            <?php endif; ?>

                            <div class="blog-content">
                                <?= $article['content'] /* HTML safe car saisi par admin */ ?>
                            </div>

                            <!-- Partage social -->
                            <div class="tagcloud-widget widget ltn__tagcloud-widget mt-50">
                                <div class="ltn__social-media mt-20">
                                    <ul>
                                        <li><strong>Partager :</strong></li>
                                        <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(SITE_URL . '/blog-details.php?slug=' . $article['slug']) ?>" target="_blank" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                                        <li><a href="https://twitter.com/intent/tweet?url=<?= urlencode(SITE_URL . '/blog-details.php?slug=' . $article['slug']) ?>&text=<?= urlencode($article['title']) ?>" target="_blank" title="Twitter"><i class="fab fa-twitter"></i></a></li>
                                        <li><a href="https://wa.me/?text=<?= urlencode($article['title'] . ' - ' . SITE_URL . '/blog-details.php?slug=' . $article['slug']) ?>" target="_blank" title="WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Articles connexes -->
                    <?php if (!empty($relatedArticles)): ?>
                    <div class="related-post-area mb-100">
                        <div class="section-title-area ltn__section-title-2--- mb-40">
                            <h4 class="section-title">Articles Similaires</h4>
                        </div>
                        <div class="row">
                            <?php foreach ($relatedArticles as $rel): ?>
                            <div class="col-md-4">
                                <div class="ltn__blog-item ltn__blog-item-3">
                                    <?php if ($rel['image']): ?>
                                    <div class="ltn__blog-img">
                                        <a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($rel['slug']) ?>">
                                            <img src="<?= e(imageUrl($rel['image'])) ?>" alt="<?= e($rel['title']) ?>">
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    <div class="ltn__blog-brief">
                                        <h6><a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($rel['slug']) ?>"><?= e(truncate($rel['title'], 60)) ?></a></h6>
                                        <div class="ltn__blog-meta"><ul><li class="ltn__blog-date"><i class="far fa-calendar-alt"></i><?= formatDate($rel['created_at']) ?></li></ul></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <aside class="sidebar-area blog-sidebar ltn__right-sidebar">
                        <!-- Recherche -->
                        <div class="widget ltn__search-widget">
                            <h4 class="ltn__widget-title ltn__widget-title-border-2">Rechercher</h4>
                            <form action="<?= SITE_URL ?>/blog.php" method="GET">
                                <input type="text" name="search" placeholder="Rechercher...">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                        <!-- Articles récents -->
                        <?php if (!empty($recentArticles)): ?>
                        <div class="widget ltn__popular-post-widget">
                            <h4 class="ltn__widget-title ltn__widget-title-border-2">Articles Récents</h4>
                            <ul>
                                <?php foreach ($recentArticles as $recent): ?>
                                <li>
                                    <div class="popular-post-widget-item clearfix">
                                        <?php if ($recent['image']): ?>
                                        <div class="popular-post-widget-img">
                                            <a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($recent['slug']) ?>">
                                                <img src="<?= e(imageUrl($recent['image'])) ?>" alt="<?= e($recent['title']) ?>">
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        <div class="popular-post-widget-brief">
                                            <h6><a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($recent['slug']) ?>"><?= e(truncate($recent['title'], 55)) ?></a></h6>
                                            <div class="ltn__blog-meta"><ul><li class="ltn__blog-date"><i class="far fa-calendar-alt"></i><?= formatDate($recent['created_at']) ?></li></ul></div>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
        </div>
    </div>

<?php include __DIR__ . '/includes/footer.php'; ?>
