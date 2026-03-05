<?php
/**
 * Dashboard Admin - KASA Immobilier
 */
define('KASA_LOADED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/auth.php';

requireAdminLogin();

$adminTitle = 'Tableau de bord';

// Statistiques
try {
    $stats = [
        'articles'    => db()->query("SELECT COUNT(*) FROM articles")->fetchColumn(),
        'published'   => db()->query("SELECT COUNT(*) FROM articles WHERE status='published'")->fetchColumn(),
        'properties'  => db()->query("SELECT COUNT(*) FROM properties")->fetchColumn(),
        'disponible'  => db()->query("SELECT COUNT(*) FROM properties WHERE status='disponible'")->fetchColumn(),
        'messages'    => db()->query("SELECT COUNT(*) FROM contacts")->fetchColumn(),
        'new_messages'=> db()->query("SELECT COUNT(*) FROM contacts WHERE status='new'")->fetchColumn(),
        'newsletter'  => db()->query("SELECT COUNT(*) FROM newsletter WHERE status='active'")->fetchColumn(),
    ];
} catch (PDOException $e) {
    $stats = array_fill_keys(['articles','published','properties','disponible','messages','new_messages','newsletter'], 0);
}

// Derniers messages
try {
    $latestMessages = db()->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    $latestMessages = [];
}

// Dernières propriétés
try {
    $latestProperties = db()->query("SELECT * FROM properties ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    $latestProperties = [];
}

include __DIR__ . '/includes/admin-header.php';
?>

<!-- STATS -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-newspaper"></i></div>
        <div class="stat-info">
            <h3><?= (int)$stats['articles'] ?></h3>
            <p>Articles (<?= (int)$stats['published'] ?> publiés)</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-home"></i></div>
        <div class="stat-info">
            <h3><?= (int)$stats['properties'] ?></h3>
            <p>Propriétés (<?= (int)$stats['disponible'] ?> disponibles)</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-envelope"></i></div>
        <div class="stat-info">
            <h3><?= (int)$stats['messages'] ?></h3>
            <p>Messages (<?= (int)$stats['new_messages'] ?> nouveaux)</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-paper-plane"></i></div>
        <div class="stat-info">
            <h3><?= (int)$stats['newsletter'] ?></h3>
            <p>Abonnés newsletter</p>
        </div>
    </div>
</div>

<!-- ACTIONS RAPIDES -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">Actions Rapides</h2>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="<?= SITE_URL ?>/admin/articles/create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvel article</a>
        <a href="<?= SITE_URL ?>/admin/properties/create.php" class="btn btn-success"><i class="fas fa-plus"></i> Nouvelle propriété</a>
        <a href="<?= SITE_URL ?>/admin/messages/index.php" class="btn btn-secondary"><i class="fas fa-envelope"></i> Voir les messages</a>
        <a href="<?= SITE_URL ?>/admin/newsletter/index.php" class="btn btn-secondary"><i class="fas fa-paper-plane"></i> Newsletter</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
    <!-- Derniers messages -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Derniers Messages</h2>
            <a href="<?= SITE_URL ?>/admin/messages/index.php" class="btn btn-secondary btn-sm">Voir tous</a>
        </div>
        <?php if (empty($latestMessages)): ?>
        <p style="color:#999;text-align:center;padding:20px;">Aucun message reçu</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>Nom</th><th>Sujet</th><th>Date</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($latestMessages as $msg): ?>
                    <tr>
                        <td><?= e($msg['name']) ?></td>
                        <td><?= e(truncate($msg['service'] ?: $msg['message'], 30)) ?></td>
                        <td><?= formatDateShort($msg['created_at']) ?></td>
                        <td>
                            <?php $badgeClass = match($msg['status']) { 'new' => 'primary', 'read' => 'secondary', 'replied' => 'success', default => 'secondary' }; ?>
                            <span class="badge badge-<?= $badgeClass ?>"><?= e($msg['status']) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Dernières propriétés -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Dernières Propriétés</h2>
            <a href="<?= SITE_URL ?>/admin/properties/index.php" class="btn btn-secondary btn-sm">Voir toutes</a>
        </div>
        <?php if (empty($latestProperties)): ?>
        <p style="color:#999;text-align:center;padding:20px;">Aucune propriété ajoutée</p>
        <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>Titre</th><th>Type</th><th>Prix</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($latestProperties as $prop): ?>
                    <tr>
                        <td><?= e(truncate($prop['title'], 30)) ?></td>
                        <td><?= e($prop['price_type']) ?></td>
                        <td><?= number_format((float)$prop['price'], 0, ',', ' ') ?></td>
                        <td>
                            <?php $badgeClass = match($prop['status']) { 'disponible' => 'success', 'vendu' => 'danger', 'loue' => 'warning', default => 'secondary' }; ?>
                            <span class="badge badge-<?= $badgeClass ?>"><?= e($prop['status']) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/admin-footer.php'; ?>
