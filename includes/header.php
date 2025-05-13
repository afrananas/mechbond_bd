<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$workshopName = 'MechBond BD'; 
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? sanitize_output($page_title) . ' | ' : '' ?><?= sanitize_output($workshopName) ?></title>
    
  
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    
    
    <link rel="icon" href="<?= BASE_URL ?>/assets/images/mechbond_bd.png">
    
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
</head>
<body class="<?= str_replace('.php', '', $current_page) ?>-page">
    <header class="main-header">
        <div class="header-container">
            <div class="logo-container">
                <a href="<?= BASE_URL ?>/index.php" class="logo-link">
                    <h1><?= sanitize_output($workshopName) ?></h1>
                </a>
            </div>
            
            <input type="checkbox" id="mobile-menu-toggle" class="mobile-menu-toggle">
            <label for="mobile-menu-toggle" class="mobile-menu-button">
                <i class="fas fa-bars"></i>
            </label>
            
            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item <?= ($current_page == 'index.php') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>/index.php" class="nav-link">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item <?= ($current_page == 'about.php') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>/about.php" class="nav-link">
                            <i class="fas fa-info-circle"></i> About
                        </a>
                    </li>
                    <li class="nav-item <?= ($current_page == 'contact.php') ? 'active' : '' ?>">
                        <a href="<?= BASE_URL ?>/contact.php" class="nav-link">
                            <i class="fas fa-envelope"></i> Contact
                        </a>
                    </li>
                    <?php if (isset($_SESSION['admin_logged_in'])): ?>
                        <li class="nav-item <?= ($current_page == 'admin.php') ? 'active' : '' ?>">
                            <a href="<?= BASE_URL ?>/admin/admin.php" class="nav-link admin-link">
                                <i class="fas fa-tools"></i> Admin Panel
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item <?= ($current_page == 'admin_login.php') ? 'active' : '' ?>">
                            <a href="<?= BASE_URL ?>/admin/admin_login.php" class="nav-link admin-link">
                                <i class="fas fa-lock"></i> Admin Login
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    
    <main class="main-content">
