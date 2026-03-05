<?php
define('KASA_LOADED', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdminLogin();
$adminTitle = 'Gestion des Articles';

$currentPage = max(1, (int) getParam('page', 1));
$perPage = 15;
$offset  = ($currentPage - 1) * $perPage;
$search  = getParam('search', '');
$status  = getParam('status', '');

$where  = ['1=1'];
$params = [];
if ($search) { $where[] = 'a.title LIKE ?'; $params[] = "%$search%"; }
if ($status) { $where[] = 'a.status = ?'; $params[] = $status; }
$whereSQL = implode(' AND ', $where);

try {
    $total = (int) db()->prepare("SELECT COUNT(*) FROM articles a WHERE $whereSQL")->execute($params) ? db()->prepare("SELECT COUNT(*) FROM articles a WHERE $whereSQL")->execute($params) : 0;
    $stmtCount = db()->prepare("SELECT COUNT(*) FROM articles a WHERE $whereSQL");
    $stmtCount->execute($params);
    $total = (int) $stmtCount->fetchColumn();

    $stmt = db()->prepare("
        SELECT a.*, c.name as category_name
        FROM articles a
        LEFT JOIN categories c ON a.category_id = c.id
        WHERE $whereSQL
        ORDER BY a.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $articles = $stmt->fetchAll();
} catch (PDOException $e) {
    $articles = []; $total = 0;
}

$totalPages = (int) ceil($total / $perPage);

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Articles (<?= $total ?>)</h2>
        <a href="<?= SITE_URL ?>/admin/articles/create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvel article</a>
    </div>

    <!-- Filtres -->
    <form method="GET" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= e($search) ?>" style="max-width:250px;">
        <select name="status" class="form-control" style="max-width:180px;" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Publié</option>
            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Brouillon</option>
        </select>
        <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
        <?php if ($search || $status): ?><a href="<?= SITE_URL ?>/admin/articles/index.php" class="btn btn-secondary">Réinitialiser</a><?php endif; ?>
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Auteur</th>
                    <th>Statut</th>
                    <th>Vues</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($articles)): ?>
                <tr><td colspan="8" style="text-align:center;color:#999;padding:40px;">Aucun article trouvé</td></tr>
                <?php else: ?>
                <?php foreach ($articles as $article): ?>
                <tr>
                    <td>
                        <?php if ($article['image']): ?>
                        <img src="<?= e(imageUrl($article['image'])) ?>" alt="" style="width:50px;height:40px;object-fit:cover;border-radius:4px;">
                        <?php else: ?>
                        <div style="width:50px;height:40px;background:#f0f2f5;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#ccc;"><i class="fas fa-image"></i></div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= e(truncate($article['title'], 50)) ?></strong></td>
                    <td><?= e($article['category_name'] ?? '—') ?></td>
                    <td><?= e($article['author']) ?></td>
                    <td>
                        <span class="badge badge-<?= $article['status'] === 'published' ? 'success' : 'warning' ?>">
                            <?= $article['status'] === 'published' ? 'Publié' : 'Brouillon' ?>
                        </span>
                    </td>
                    <td><?= (int)$article['views'] ?></td>
                    <td><?= formatDateShort($article['created_at']) ?></td>
                    <td style="white-space:nowrap;">
                        <a href="<?= SITE_URL ?>/admin/articles/edit.php?id=<?= $article['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                        <a href="<?= SITE_URL ?>/blog-details.php?slug=<?= e($article['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a>
                        <a href="<?= SITE_URL ?>/admin/articles/delete.php?id=<?= $article['id'] ?>" class="btn btn-danger btn-sm btn-delete"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($currentPage > 1): ?><a href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&status=<?= e($status) ?>" class="page-link">←</a><?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= e($status) ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($currentPage < $totalPages): ?><a href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&status=<?= e($status) ?>" class="page-link">→</a><?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
