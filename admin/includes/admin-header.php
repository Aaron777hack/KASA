<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle ?? 'Admin') ?> | KASA Admin</title>
    <link rel="shortcut icon" href="<?= SITE_URL ?>/img/favicon.png">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; color: #333; }
        /* Layout */
        .admin-wrapper { display: flex; min-height: 100vh; }
        /* Sidebar */
        .sidebar { width: 260px; background: #1a2035; color: #fff; flex-shrink: 0; position: fixed; top: 0; left: 0; height: 100vh; overflow-y: auto; z-index: 100; }
        .sidebar-logo { padding: 24px 20px; border-bottom: 1px solid rgba(255,255,255,.1); text-align: center; }
        .sidebar-logo img { height: 50px; filter: brightness(0) invert(1); }
        .sidebar-logo span { display: block; font-size: 12px; color: rgba(255,255,255,.5); margin-top: 6px; }
        .sidebar-menu { padding: 12px 0; }
        .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 24px; color: rgba(255,255,255,.7); text-decoration: none; font-size: 14px; transition: all .2s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,90,60,.2); color: #ff5a3c; border-left: 3px solid #ff5a3c; }
        .sidebar-menu .menu-label { font-size: 10px; text-transform: uppercase; letter-spacing: 1.5px; color: rgba(255,255,255,.3); padding: 16px 24px 4px; }
        .sidebar-menu i { width: 18px; text-align: center; }
        /* Main */
        .main-content { margin-left: 260px; flex: 1; display: flex; flex-direction: column; }
        /* Topbar */
        .topbar { background: #fff; padding: 16px 32px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 4px rgba(0,0,0,.08); position: sticky; top: 0; z-index: 50; }
        .topbar h1 { font-size: 20px; font-weight: 700; color: #1a2035; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .admin-avatar { width: 36px; height: 36px; background: #ff5a3c; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 14px; }
        .admin-name { font-size: 14px; font-weight: 600; }
        .btn-logout { padding: 8px 16px; background: #f0f2f5; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; color: #666; text-decoration: none; transition: all .2s; }
        .btn-logout:hover { background: #dc3545; color: #fff; }
        /* Content */
        .page-content { padding: 32px; flex: 1; }
        /* Cards */
        .card { background: #fff; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.06); padding: 24px; margin-bottom: 24px; }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f0f2f5; }
        .card-title { font-size: 16px; font-weight: 700; color: #1a2035; }
        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; border: none; transition: all .2s; }
        .btn-primary { background: #ff5a3c; color: #fff; }
        .btn-primary:hover { background: #e04b2d; }
        .btn-secondary { background: #f0f2f5; color: #555; }
        .btn-secondary:hover { background: #e2e6ea; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; color: #fff; }
        .btn-success:hover { background: #218838; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        /* Table */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid #f0f2f5; font-size: 14px; }
        th { background: #f8f9fa; font-weight: 700; color: #555; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; }
        tr:hover td { background: #fafbfc; }
        /* Forms */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 6px; }
        .form-control { width: 100%; padding: 10px 14px; border: 2px solid #e1e4e8; border-radius: 8px; font-size: 14px; outline: none; transition: border-color .2s; font-family: inherit; }
        .form-control:focus { border-color: #ff5a3c; }
        textarea.form-control { min-height: 150px; resize: vertical; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        /* Stats */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: 10px; padding: 20px 24px; box-shadow: 0 2px 8px rgba(0,0,0,.06); display: flex; align-items: center; gap: 16px; }
        .stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .stat-icon.orange { background: rgba(255,90,60,.1); color: #ff5a3c; }
        .stat-icon.blue { background: rgba(0,123,255,.1); color: #007bff; }
        .stat-icon.green { background: rgba(40,167,69,.1); color: #28a745; }
        .stat-icon.purple { background: rgba(102,16,242,.1); color: #6610f2; }
        .stat-info h3 { font-size: 28px; font-weight: 800; color: #1a2035; line-height: 1; }
        .stat-info p { font-size: 13px; color: #777; margin-top: 4px; }
        /* Badges */
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .badge-success { background: rgba(40,167,69,.15); color: #28a745; }
        .badge-warning { background: rgba(255,193,7,.15); color: #856404; }
        .badge-danger { background: rgba(220,53,69,.15); color: #dc3545; }
        .badge-primary { background: rgba(255,90,60,.15); color: #ff5a3c; }
        .badge-secondary { background: rgba(108,117,125,.15); color: #6c757d; }
        /* Alert */
        .alert { padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        /* Image preview */
        .img-preview { max-width: 200px; border-radius: 8px; margin-top: 10px; }
        /* Pagination */
        .pagination { display: flex; gap: 6px; align-items: center; margin-top: 20px; }
        .page-link { padding: 8px 14px; border: 2px solid #e1e4e8; border-radius: 6px; text-decoration: none; color: #555; font-size: 13px; font-weight: 600; transition: all .2s; }
        .page-link:hover, .page-link.active { background: #ff5a3c; border-color: #ff5a3c; color: #fff; }
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform .3s; }
            .main-content { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <img src="<?= SITE_URL ?>/img/logo-4.png" alt="KASA">
            <span>Administration</span>
        </div>
        <nav class="sidebar-menu">
            <div class="menu-label">Principal</div>
            <a href="<?= SITE_URL ?>/admin/index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' && dirname($_SERVER['PHP_SELF']) === dirname('/admin/index.php') ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>
            <div class="menu-label">Contenu</div>
            <a href="<?= SITE_URL ?>/admin/articles/index.php" class="<?= str_contains($_SERVER['PHP_SELF'], '/articles/') ? 'active' : '' ?>">
                <i class="fas fa-newspaper"></i> Articles
            </a>
            <a href="<?= SITE_URL ?>/admin/properties/index.php" class="<?= str_contains($_SERVER['PHP_SELF'], '/properties/') ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Propriétés
            </a>
            <div class="menu-label">Communication</div>
            <a href="<?= SITE_URL ?>/admin/messages/index.php" class="<?= str_contains($_SERVER['PHP_SELF'], '/messages/') ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i> Messages
                <?php
                try {
                    $newCount = db()->query("SELECT COUNT(*) FROM contacts WHERE status = 'new'")->fetchColumn();
                    if ($newCount > 0) echo '<span style="background:#ff5a3c;color:#fff;padding:2px 7px;border-radius:12px;font-size:11px;margin-left:auto;">' . $newCount . '</span>';
                } catch (Exception $e) {}
                ?>
            </a>
            <a href="<?= SITE_URL ?>/admin/newsletter/index.php" class="<?= str_contains($_SERVER['PHP_SELF'], '/newsletter/') ? 'active' : '' ?>">
                <i class="fas fa-paper-plane"></i> Newsletter
            </a>
            <div class="menu-label">Site</div>
            <a href="<?= SITE_URL ?>/index.php" target="_blank">
                <i class="fas fa-external-link-alt"></i> Voir le site
            </a>
            <a href="<?= SITE_URL ?>/admin/logout.php">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </nav>
    </aside>

    <!-- Contenu principal -->
    <div class="main-content">
        <div class="topbar">
            <h1><?= e($adminTitle ?? 'Administration') ?></h1>
            <div class="topbar-right">
                <?php $admin = getCurrentAdmin(); ?>
                <div class="admin-avatar"><?= strtoupper(substr($admin['name'], 0, 1)) ?></div>
                <span class="admin-name"><?= e($admin['name']) ?></span>
                <a href="<?= SITE_URL ?>/admin/logout.php" class="btn-logout">Déconnexion</a>
            </div>
        </div>
        <div class="page-content">
<?php
// Afficher les messages flash
$flash = getFlash();
if ($flash):
?>
<div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'danger' ?>">
    <?= e($flash['message']) ?>
</div>
<?php endif; ?>
