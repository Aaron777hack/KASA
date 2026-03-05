<?php
define('KASA_LOADED', true);
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/auth.php';
logoutAdmin();
redirect(SITE_URL . '/admin/login.php');
