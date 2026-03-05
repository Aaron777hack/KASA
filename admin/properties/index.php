<?php
define('KASA_LOADED', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdminLogin();
$adminTitle = 'Gestion des Propriétés';

$currentPage = max(1, (int) getParam('page', 1));
$perPage = 15; $offset = ($currentPage - 1) * $perPage;
$search = getParam('search', '');
$status = getParam('status', '');
$type   = getParam('type', '');

$where  = ['1=1']; $params = [];
if ($search) { $where[] = 'title LIKE ?'; $params[] = "%$search%"; }
if ($status) { $where[] = 'status = ?'; $params[] = $status; }
if ($type)   { $where[] = 'price_type = ?'; $params[] = $type; }
$whereSQL = implode(' AND ', $where);

try {
    $stmtCount = db()->prepare("SELECT COUNT(*) FROM properties WHERE $whereSQL");
    $stmtCount->execute($params);
    $total = (int)$stmtCount->fetchColumn();
    $stmt = db()->prepare("SELECT * FROM properties WHERE $whereSQL ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $properties = $stmt->fetchAll();
} catch (PDOException $e) { $properties = []; $total = 0; }

$totalPages = (int) ceil($total / $perPage);
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Propriétés (<?= $total ?>)</h2>
        <a href="<?= SITE_URL ?>/admin/properties/create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle propriété</a>
    </div>
    <form method="GET" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <input type="text" name="search" class="form-control" placeholder="Rechercher..." value="<?= e($search) ?>" style="max-width:220px;">
        <select name="type" class="form-control" style="max-width:160px;" onchange="this.form.submit()">
            <option value="">Tous types</option>
            <option value="vente" <?= $type === 'vente' ? 'selected' : '' ?>>Vente</option>
            <option value="location" <?= $type === 'location' ? 'selected' : '' ?>>Location</option>
            <option value="terrain" <?= $type === 'terrain' ? 'selected' : '' ?>>Terrain</option>
        </select>
        <select name="status" class="form-control" style="max-width:160px;" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            <option value="disponible" <?= $status === 'disponible' ? 'selected' : '' ?>>Disponible</option>
            <option value="vendu" <?= $status === 'vendu' ? 'selected' : '' ?>>Vendu</option>
            <option value="loue" <?= $status === 'loue' ? 'selected' : '' ?>>Loué</option>
        </select>
        <button class="btn btn-secondary" type="submit"><i class="fas fa-search"></i></button>
        <?php if ($search || $status || $type): ?><a href="<?= SITE_URL ?>/admin/properties/index.php" class="btn btn-secondary">Reset</a><?php endif; ?>
    </form>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Image</th><th>Titre</th><th>Type</th><th>Prix</th><th>Localisation</th><th>Statut</th><th>Vedette</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($properties)): ?>
                <tr><td colspan="8" style="text-align:center;color:#999;padding:40px;">Aucune propriété</td></tr>
                <?php else: ?>
                <?php foreach ($properties as $prop): ?>
                <tr>
                    <td><img src="<?= e(imageUrl($prop['image'])) ?>" alt="" style="width:60px;height:45px;object-fit:cover;border-radius:4px;"></td>
                    <td><strong><?= e(truncate($prop['title'], 40)) ?></strong><br><small style="color:#999;">KASA-<?= str_pad($prop['id'], 4, '0', STR_PAD_LEFT) ?></small></td>
                    <td><span class="badge badge-<?= $prop['price_type'] === 'vente' ? 'danger' : ($prop['price_type'] === 'location' ? 'success' : 'warning') ?>"><?= e($prop['price_type']) ?></span></td>
                    <td><?= number_format((float)$prop['price'], 0, ',', ' ') ?> FCFA</td>
                    <td><?= e(truncate($prop['location'], 30)) ?></td>
                    <td><span class="badge badge-<?= $prop['status'] === 'disponible' ? 'success' : ($prop['status'] === 'vendu' ? 'danger' : 'warning') ?>"><?= e($prop['status']) ?></span></td>
                    <td><?= $prop['featured'] ? '<span class="badge badge-primary">⭐ Oui</span>' : '<span class="badge badge-secondary">Non</span>' ?></td>
                    <td style="white-space:nowrap;">
                        <a href="<?= SITE_URL ?>/admin/properties/edit.php?id=<?= $prop['id'] ?>" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></a>
                        <a href="<?= SITE_URL ?>/property-details.php?slug=<?= e($prop['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm"><i class="fas fa-eye"></i></a>
                        <a href="<?= SITE_URL ?>/admin/properties/delete.php?id=<?= $prop['id'] ?>" class="btn btn-danger btn-sm btn-delete"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= e($status) ?>&type=<?= e($type) ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
