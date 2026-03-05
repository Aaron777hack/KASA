<?php
define('KASA_LOADED', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdminLogin();
$adminTitle = 'Messages Reçus';

// Action rapide : marquer comme lu
if (isset($_GET['mark']) && isset($_GET['id'])) {
    $action = $_GET['mark'];
    $msgId  = (int) $_GET['id'];
    $validStatuses = ['read', 'replied', 'new'];
    if (in_array($action, $validStatuses) && $msgId) {
        db()->prepare('UPDATE contacts SET status = ? WHERE id = ?')->execute([$action, $msgId]);
    }
    redirect(SITE_URL . '/admin/messages/index.php');
}

// Détail d'un message
$viewMsg = null;
if (isset($_GET['view'])) {
    $viewId = (int) $_GET['view'];
    $stmtV  = db()->prepare('SELECT * FROM contacts WHERE id = ?');
    $stmtV->execute([$viewId]);
    $viewMsg = $stmtV->fetch();
    if ($viewMsg && $viewMsg['status'] === 'new') {
        db()->prepare('UPDATE contacts SET status = ? WHERE id = ?')->execute(['read', $viewId]);
        $viewMsg['status'] = 'read';
    }
}

$currentPage = max(1, (int) getParam('page', 1));
$perPage = 20; $offset = ($currentPage - 1) * $perPage;
$status  = getParam('status', '');
$where = ['1=1']; $params = [];
if ($status) { $where[] = 'status = ?'; $params[] = $status; }
$whereSQL = implode(' AND ', $where);

try {
    $stmtCount = db()->prepare("SELECT COUNT(*) FROM contacts WHERE $whereSQL");
    $stmtCount->execute($params);
    $total = (int) $stmtCount->fetchColumn();
    $stmt = db()->prepare("SELECT * FROM contacts WHERE $whereSQL ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute(array_merge($params, [$perPage, $offset]));
    $messages = $stmt->fetchAll();
} catch (PDOException $e) { $messages = []; $total = 0; }

$totalPages = (int) ceil($total / $perPage);
include __DIR__ . '/../includes/admin-header.php';
?>

<?php if ($viewMsg): ?>
<!-- Détail du message -->
<div class="card" style="max-width:800px;">
    <div class="card-header">
        <h2 class="card-title">Message de <?= e($viewMsg['name']) ?></h2>
        <a href="<?= SITE_URL ?>/admin/messages/index.php" class="btn btn-secondary btn-sm">← Retour</a>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <tr><td style="padding:10px;font-weight:700;width:130px;color:#666;">Nom</td><td style="padding:10px;"><?= e($viewMsg['name']) ?></td></tr>
        <tr style="background:#f8f9fa;"><td style="padding:10px;font-weight:700;color:#666;">Email</td><td style="padding:10px;"><a href="mailto:<?= e($viewMsg['email']) ?>"><?= e($viewMsg['email']) ?></a></td></tr>
        <tr><td style="padding:10px;font-weight:700;color:#666;">Téléphone</td><td style="padding:10px;"><?= e($viewMsg['phone'] ?: '—') ?></td></tr>
        <tr style="background:#f8f9fa;"><td style="padding:10px;font-weight:700;color:#666;">Service</td><td style="padding:10px;"><?= e($viewMsg['service'] ?: '—') ?></td></tr>
        <tr><td style="padding:10px;font-weight:700;color:#666;">Date</td><td style="padding:10px;"><?= formatDate($viewMsg['created_at']) ?></td></tr>
        <tr style="background:#f8f9fa;"><td style="padding:10px;font-weight:700;color:#666;vertical-align:top;">Message</td><td style="padding:10px;"><?= nl2br(e($viewMsg['message'])) ?></td></tr>
    </table>
    <div style="padding:20px;display:flex;gap:12px;flex-wrap:wrap;">
        <a href="mailto:<?= e($viewMsg['email']) ?>?subject=Re: <?= urlencode($viewMsg['service'] ?: 'Votre demande') ?>" class="btn btn-primary">
            <i class="fas fa-reply"></i> Répondre par email
        </a>
        <a href="<?= SITE_URL ?>/admin/messages/index.php?mark=replied&id=<?= $viewMsg['id'] ?>" class="btn btn-success">
            <i class="fas fa-check"></i> Marquer comme répondu
        </a>
        <a href="<?= SITE_URL ?>/admin/messages/index.php?mark=new&id=<?= $viewMsg['id'] ?>" class="btn btn-secondary">
            Marquer non lu
        </a>
    </div>
</div>
<?php else: ?>
<!-- Liste des messages -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Messages (<?= $total ?>)</h2>
        <div>
            <a href="<?= SITE_URL ?>/admin/messages/index.php?status=new" class="btn btn-secondary btn-sm <?= $status === 'new' ? 'btn-primary' : '' ?>">Nouveaux</a>
            <a href="<?= SITE_URL ?>/admin/messages/index.php?status=replied" class="btn btn-secondary btn-sm">Répondus</a>
            <a href="<?= SITE_URL ?>/admin/messages/index.php" class="btn btn-secondary btn-sm">Tous</a>
        </div>
    </div>
    <div class="table-responsive">
        <table>
            <thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Service</th><th>Message</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr><td colspan="8" style="text-align:center;color:#999;padding:40px;">Aucun message</td></tr>
                <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <tr style="<?= $msg['status'] === 'new' ? 'font-weight:700;background:#fff8f6;' : '' ?>">
                    <td><?= e($msg['name']) ?></td>
                    <td><a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a></td>
                    <td><?= e($msg['phone'] ?: '—') ?></td>
                    <td><?= e(truncate($msg['service'] ?: '—', 25)) ?></td>
                    <td><?= e(truncate($msg['message'], 60)) ?></td>
                    <td><?= formatDateShort($msg['created_at']) ?></td>
                    <td>
                        <?php $bc = match($msg['status']) { 'new' => 'primary', 'read' => 'secondary', 'replied' => 'success', default => 'secondary' }; ?>
                        <span class="badge badge-<?= $bc ?>"><?= e($msg['status']) ?></span>
                    </td>
                    <td style="white-space:nowrap;">
                        <a href="?view=<?= $msg['id'] ?>" class="btn btn-secondary btn-sm" title="Voir"><i class="fas fa-eye"></i></a>
                        <a href="mailto:<?= e($msg['email']) ?>" class="btn btn-primary btn-sm" title="Répondre"><i class="fas fa-reply"></i></a>
                        <?php if ($msg['status'] !== 'replied'): ?>
                        <a href="?mark=replied&id=<?= $msg['id'] ?>" class="btn btn-success btn-sm" title="Marquer répondu"><i class="fas fa-check"></i></a>
                        <?php endif; ?>
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
        <a href="?page=<?= $i ?>&status=<?= e($status) ?>" class="page-link <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
