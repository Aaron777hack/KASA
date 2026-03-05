<?php
/**
 * Page blog - Liste des articles
 */
define('KASA_LOADED', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$pageTitle = 'Actualités';
$pageDesc  = 'Toutes les actualités immobilières de KASA - Conseils, tendances et nouveautés';

// Paramètres de filtre et pagination
$currentPage   = max(1, (int) getParam('page', 1));
$categorySlug  = getParam('category', '');
$search        = getParam('search', '');

// Construire la requête
$where  = ["a.status = 'published'"];
$params = [];

if ($categorySlug) {
    $where[]  = 'c.slug = ?';
    $params[] = $categorySlug;
}
if ($search) {
    $where[]  = '(a.title LIKE ? OR a.excerpt LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereSQL = implode(' AND ', $where);

// Compter le total
try {
    $stmtCount = db()->prepare("
        SELECT COUNT(*) FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE $whereSQL
    ");
    $stmtCount->execute($params);
    $total = (int) $stmtCount->fetchColumn();
} catch (PDOException $e) {
    $total = 0;
}

$pagination = getPagination($total, $currentPage);

// Récupérer les articles
try {
    $stmtArticles = db()->prepare("
        SELECT a.*, c.name as category_name, c.slug as category_slug
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE $whereSQL
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmtArticles->execute(array_merge($params, [$pagination['per_page'], $pagination['offset']]));
    $articles = $stmtArticles->fetchAll();
} catch (PDOException $e) {
    $articles = [];
}

// Catégories pour le sidebar
try {
    $stmtCats = db()->prepare("
        SELECT c.*, COUNT(a.id) as article_count
        FROM categories c
        LEFT JOIN articles a ON c.id = a.category_id AND a.status = 'published'
        WHERE c.type = 'article'
        GROUP BY c.id
        ORDER BY c.name
    ");
    $stmtCats->execute();
    $categoriesMenu = $stmtCats->fetchAll();
} catch (PDOException $e) {
    $categoriesMenu = [];
}

// Articles récents (sidebar)
try {
    $stmtRecent = db()->prepare("
        SELECT id, title, slug, image, created_at FROM articles
        WHERE status = 'published' ORDER BY created_at DESC LIMIT 5
    ");
    $stmtRecent->execute();
    $recentArticles = $stmtRecent->fetchAll();
} catch (PDOException $e) {
    $recentArticles = [];
}

include __DIR__ . '/includes/header.php';
?>

    <!-- BREADCRUMB -->
    <div class="ltn__breadcrumb-area text-left bg-overlay-white-30 bg-image" data-bs-bg="<?= SITE_URL ?>/img/bg/14.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__breadcrumb-inner">
                        <h1 class="page-title">Actualités Immobilières</h1>
                        <div class="ltn__breadcrumb-list">
                            <ul>
                                <li><a href="<?= SITE_URL ?>/index.php"><span class="ltn__secondary-color"><i class="fas fa-home"></i></span> Accueil</a></li>
                                <li>Actualités</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BLOG AREA -->
    <div class="ltn__blog-area mb-120">
        <div class="container">
            <div class="row">
                <!-- Articles -->
                <div class="col-lg-8">
                    <?php if (empty($articles)): ?>
                    <div class="text-center py-60">
                        <i class="fas fa-newspaper fa-3x text-muted mb-20"></i>
                        <h3>Aucun article trouvé</h3>
                        <p>Revenez bientôt pour de nouveaux contenus.</p>
                    </div>
                    <?php else: ?>
                    <div class="ltn__blog-list-wrap">
                        <?php foreach ($articles as $article): ?>
                        <div class="ltn__blog-item ltn__blog-item-5">
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
                                        <?php if ($article['category_name']): ?>
                                        <li class="ltn__blog-category">
                                            <a href="<?= SITE_URL ?>/blog.php?category=<?= e($article['category_slug']) ?>"><?= e($article['category_name']) ?></a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <h3 class="ltn__blog-title">
                                    <a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($article['slug']) ?>"><?= e($article['title']) ?></a>
                                </h3>
                                <div class="ltn__blog-meta">
                                    <ul>
                                        <li><a href="#"><i class="far fa-eye"></i> <?= (int)$article['views'] ?> vues</a></li>
                                        <li class="ltn__blog-date"><i class="far fa-calendar-alt"></i><?= formatDate($article['created_at']) ?></li>
                                    </ul>
                                </div>
                                <?php if ($article['excerpt']): ?>
                                <p><?= e($article['excerpt']) ?></p>
                                <?php endif; ?>
                                <div class="ltn__blog-meta-btn">
                                    <div class="ltn__blog-meta">
                                        <ul>
                                            <li class="ltn__blog-author">
                                                <a href="#"><i class="far fa-user"></i> Par: <?= e($article['author']) ?></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="ltn__blog-btn">
                                        <a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($article['slug']) ?>"><i class="fas fa-arrow-right"></i> Lire la suite</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="ltn__pagination-area text-center">
                        <div class="ltn__pagination">
                            <ul>
                                <?php if ($pagination['has_prev']): ?>
                                <li><a href="?page=<?= $pagination['prev_page'] ?><?= $categorySlug ? '&category=' . e($categorySlug) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-angle-double-left"></i></a></li>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="<?= $i === $pagination['current'] ? 'active' : '' ?>">
                                    <a href="?page=<?= $i ?><?= $categorySlug ? '&category=' . e($categorySlug) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
                                </li>
                                <?php endfor; ?>
                                <?php if ($pagination['has_next']): ?>
                                <li><a href="?page=<?= $pagination['next_page'] ?><?= $categorySlug ? '&category=' . e($categorySlug) : '' ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><i class="fas fa-angle-double-right"></i></a></li>
                                <?php endif; ?>
                            </ul>
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
                            <form action="" method="GET">
                                <input type="text" name="search" placeholder="Rechercher..." value="<?= e($search) ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                        <!-- Catégories -->
                        <?php if (!empty($categoriesMenu)): ?>
                        <div class="widget ltn__menu-widget ltn__menu-widget-2--- ltn__menu-widget-2-color-2---">
                            <h4 class="ltn__widget-title ltn__widget-title-border-2">Catégories</h4>
                            <ul class="go-top">
                                <?php foreach ($categoriesMenu as $cat): ?>
                                <li>
                                    <a href="<?= SITE_URL ?>/blog.php?category=<?= e($cat['slug']) ?>">
                                        <?= e($cat['name']) ?> <span>(<?= (int)$cat['article_count'] ?>)</span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
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
                                            <h6><a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($recent['slug']) ?>"><?= e(truncate($recent['title'], 60)) ?></a></h6>
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
