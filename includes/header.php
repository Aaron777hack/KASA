<?php
/**
 * En-tête commun du site KASA
 * Variables attendues : $pageTitle (string), $pageDesc (string, optionnel)
 */
$pageTitle = $pageTitle ?? SITE_NAME;
$pageDesc  = $pageDesc ?? 'KASA Immobilier - Le leader de l\'immobilier en Côte d\'Ivoire';

// Page courante pour surligner le menu actif
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!doctype html>
<html class="no-js" lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= e($pageTitle) ?> | KASA Immobilier</title>
    <meta name="description" content="<?= e($pageDesc) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="<?= SITE_URL ?>/img/favicon.png" type="image/x-icon" />
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/font-icons.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/plugins.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/responsive.css">
</head>
<body>

<?php $flash = getFlash(); if ($flash): ?>
<div class="alert-floating alert-<?= e($flash['type']) ?>" style="position:fixed;top:20px;right:20px;z-index:9999;padding:15px 25px;border-radius:5px;box-shadow:0 4px 12px rgba(0,0,0,.15);background:<?= $flash['type'] === 'success' ? '#28a745' : '#dc3545' ?>;color:#fff;font-weight:600;">
    <?= e($flash['message']) ?>
</div>
<script>setTimeout(function(){document.querySelector('.alert-floating')?.remove()},4000);</script>
<?php endif; ?>

<div class="body-wrapper">

    <!-- HEADER -->
    <header class="ltn__header-area ltn__header-5 ltn__header-transparent--- gradient-color-4---">
        <!-- Top bar -->
        <div class="ltn__header-top-area section-bg-6 top-area-color-white---">
            <div class="container">
                <div class="row">
                    <div class="col-md-7">
                        <div class="ltn__top-bar-menu">
                            <ul>
                                <li><a href="mailto:<?= e(SITE_EMAIL) ?>"><i class="icon-mail"></i> <?= e(SITE_EMAIL) ?></a></li>
                                <li><a href="<?= SITE_URL ?>/contact.php"><i class="icon-placeholder"></i> <?= e(SITE_ADDRESS) ?></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="top-bar-right text-end">
                            <div class="ltn__top-bar-menu">
                                <ul>
                                    <li>
                                        <div class="ltn__social-media">
                                            <ul>
                                                <li><a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                                                <li><a href="#" title="Twitter"><i class="fab fa-twitter"></i></a></li>
                                                <li><a href="#" title="Instagram"><i class="fab fa-instagram"></i></a></li>
                                                <li><a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Header middle -->
        <div class="ltn__header-middle-area ltn__header-sticky ltn__sticky-bg-white">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="site-logo-wrap">
                            <div class="site-logo">
                                <a href="<?= SITE_URL ?>/index.php"><img src="<?= SITE_URL ?>/img/logo-4.png" height="80" alt="KASA Immobilier"></a>
                            </div>
                        </div>
                    </div>
                    <div class="col header-menu-column">
                        <div class="header-menu d-none d-xl-block">
                            <nav>
                                <div class="ltn__main-menu">
                                    <ul>
                                        <li class="<?= in_array($currentPage, ['index']) ? 'menu-active' : '' ?>">
                                            <a href="<?= SITE_URL ?>/index.php">Accueil</a>
                                        </li>
                                        <li class="<?= $currentPage === 'about' ? 'menu-active' : '' ?>">
                                            <a href="<?= SITE_URL ?>/about.php">KASA Immobilier</a>
                                        </li>
                                        <li class="<?= in_array($currentPage, ['shop', 'property-details']) ? 'menu-active' : '' ?>">
                                            <a href="<?= SITE_URL ?>/shop.php">Nos propriétés</a>
                                        </li>
                                        <li class="<?= in_array($currentPage, ['blog', 'blog-details']) ? 'menu-active' : '' ?>">
                                            <a href="<?= SITE_URL ?>/blog.php">Actualités</a>
                                        </li>
                                        <li class="<?= $currentPage === 'contact' ? 'menu-active' : '' ?>">
                                            <a href="<?= SITE_URL ?>/contact.php">Contact</a>
                                        </li>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                    </div>
                    <div class="col--- ltn__header-options ltn__header-options-2 mb-sm-20">
                        <div class="mobile-menu-toggle d-xl-none">
                            <a href="#ltn__utilize-mobile-menu" class="ltn__utilize-toggle">
                                <svg viewBox="0 0 800 600">
                                    <path d="M300,220 C300,220 520,220 540,220 C740,220 640,540 520,420 C440,340 300,200 300,200" id="top"></path>
                                    <path d="M300,320 L540,320" id="middle"></path>
                                    <path d="M300,210 C300,210 520,210 540,210 C740,210 640,530 520,410 C440,330 300,190 300,190" id="bottom" transform="translate(480, 320) scale(1, -1) translate(-480, -318)"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Menu mobile -->
    <div id="ltn__utilize-mobile-menu" class="ltn__utilize ltn__utilize-mobile-menu">
        <div class="ltn__utilize-menu-inner ltn__scrollbar">
            <div class="ltn__utilize-menu-head">
                <div class="site-logo">
                    <a href="<?= SITE_URL ?>/index.php"><img src="<?= SITE_URL ?>/img/logo.png" alt="Logo"></a>
                </div>
                <button class="ltn__utilize-close">×</button>
            </div>
            <div class="ltn__utilize-menu">
                <ul>
                    <li><a href="<?= SITE_URL ?>/index.php">Accueil</a></li>
                    <li><a href="<?= SITE_URL ?>/about.php">KASA Immobilier</a></li>
                    <li><a href="<?= SITE_URL ?>/shop.php">Nos propriétés</a></li>
                    <li><a href="<?= SITE_URL ?>/blog.php">Actualités</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="ltn__social-media-2">
                <ul>
                    <li><a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                    <li><a href="#" title="Twitter"><i class="fab fa-twitter"></i></a></li>
                    <li><a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
                    <li><a href="#" title="Instagram"><i class="fab fa-instagram"></i></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="ltn__utilize-overlay"></div>
