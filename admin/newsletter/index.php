<?php
define('KASA_LOADED', true);
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/auth.php';

requireAdminLogin();
$adminTitle = 'Newsletter';

// Envoi de newsletter
$sent = 0;
$sendError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_newsletter'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $sendError = 'Requête invalide.';
    } else {
        $subject = sanitize($_POST['subject'] ?? '');
        $body    = $_POST['body'] ?? '';
        if (empty($subject) || empty($body)) {
            $sendError = 'Sujet et contenu requis.';
        } else {
            try {
                $subscribers = db()->query("SELECT email, token FROM newsletter WHERE status = 'active'")->fetchAll();
                foreach ($subscribers as $sub) {
                    $email       = $sub['email'];
                    $mailSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
                    $footer = "\n\n---\nPour vous désinscrire : " . SITE_URL . "/api/newsletter.php?action=unsubscribe&email=" . urlencode($email) . "&token=" . $sub['token'];
                    $headers = "From: " . SITE_NAME . " <" . SITE_EMAIL . ">\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
                    if (@mail($email, $mailSubject, strip_tags($body) . $footer, $headers)) {
                        $sent++;
                    }
                }
                setFlash('success', "Newsletter envoyée à $sent abonné(s) !");
                redirect(SITE_URL . '/admin/newsletter/index.php');
            } catch (PDOException $e) {
                $sendError = 'Erreur : ' . $e->getMessage();
            }
        }
    }
}

$currentPage = max(1, (int) getParam('page', 1));
$perPage = 20; $offset = ($currentPage - 1) * $perPage;
$status = getParam('status', 'active');

try {
    $count = db()->prepare("SELECT COUNT(*) FROM newsletter WHERE status = ?");
    $count->execute([$status]);
    $total = (int) $count->fetchColumn();
    $stmt  = db()->prepare("SELECT * FROM newsletter WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$status, $perPage, $offset]);
    $subscribers = $stmt->fetchAll();
    $totalActive = (int) db()->query("SELECT COUNT(*) FROM newsletter WHERE status='active'")->fetchColumn();
} catch (PDOException $e) { $subscribers = []; $total = 0; $totalActive = 0; }

$totalPages = (int) ceil($total / $perPage);
include __DIR__ . '/../includes/admin-header.php';
?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;align-items:start;">
    <!-- Liste abonnés -->
    <div>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Abonnés (<?= $total ?> <?= $status === 'active' ? 'actifs' : 'inactifs' ?>)</h2>
                <div>
                    <a href="?status=active" class="btn btn-sm <?= $status === 'active' ? 'btn-primary' : 'btn-secondary' ?>">Actifs</a>
                    <a href="?status=inactive" class="btn btn-sm <?= $status === 'inactive' ? 'btn-primary' : 'btn-secondary' ?>">Inactifs</a>
                </div>
            </div>
            <div class="table-responsive">
                <table>
                    <thead><tr><th>Email</th><th>Statut</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if (empty($subscribers)): ?>
                        <tr><td colspan="4" style="text-align:center;color:#999;padding:40px;">Aucun abonné</td></tr>
                        <?php else: ?>
                        <?php foreach ($subscribers as $sub): ?>
                        <tr>
                            <td><?= e($sub['email']) ?></td>
                            <td><span class="badge badge-<?= $sub['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e($sub['status']) ?></span></td>
                            <td><?= formatDateShort($sub['created_at']) ?></td>
                            <td>
                                <?php if ($sub['status'] === 'active'): ?>
                                <a href="?deactivate=<?= $sub['id'] ?>" class="btn btn-danger btn-sm btn-delete" title="Désinscrire">Désinscrire</a>
                                <?php else: ?>
                                <a href="?activate=<?= $sub['id'] ?>" class="btn btn-success btn-sm" title="Réactiver">Réactiver</a>
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
    </div>

    <!-- Envoyer une newsletter -->
    <div>
        <div class="stat-card" style="margin-bottom:20px;">
            <div class="stat-icon purple"><i class="fas fa-paper-plane"></i></div>
            <div class="stat-info"><h3><?= $totalActive ?></h3><p>Abonnés actifs</p></div>
        </div>
        <div class="card">
            <div class="card-header"><h2 class="card-title">Envoyer une newsletter</h2></div>
            <?php if ($sendError): ?><div class="alert alert-danger"><?= e($sendError) ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="send_newsletter" value="1">
                <div class="form-group">
                    <label class="form-label">Sujet *</label>
                    <input type="text" name="subject" class="form-control" required placeholder="Objet de la newsletter">
                </div>
                <div class="form-group">
                    <label class="form-label">Message *</label>
                    <textarea name="body" class="form-control" rows="10" required placeholder="Contenu de la newsletter..."></textarea>
                </div>
                <p style="font-size:12px;color:#999;margin-bottom:12px;">
                    Sera envoyée à <strong><?= $totalActive ?></strong> abonné(s) actif(s).
                </p>
                <button type="submit" class="btn btn-primary" style="width:100%;" onclick="return confirm('Envoyer à <?= $totalActive ?> abonné(s) ?')">
                    <i class="fas fa-paper-plane"></i> Envoyer
                </button>
            </form>
        </div>
    </div>
</div>

<?php
// Traitement rapide désactivation/activation
if (isset($_GET['deactivate'])) {
    $sid = (int) $_GET['deactivate'];
    db()->prepare('UPDATE newsletter SET status=? WHERE id=?')->execute(['inactive', $sid]);
    redirect(SITE_URL . '/admin/newsletter/index.php');
}
if (isset($_GET['activate'])) {
    $sid = (int) $_GET['activate'];
    db()->prepare('UPDATE newsletter SET status=? WHERE id=?')->execute(['active', $sid]);
    redirect(SITE_URL . '/admin/newsletter/index.php');
}
?>

<?php include __DIR__ . '/../includes/admin-footer.php'; ?>
