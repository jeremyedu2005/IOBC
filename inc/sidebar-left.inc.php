<?php
// Navigation latérale gauche - Composant réutilisable
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
$estConnecte = isset($_SESSION['user_id']);
?>

<aside class="sidebar-left">
    <ul class="nav-menu">
        <li class="nav-item">
            <a href="index.php?accueil" class="nav-link <?= $currentPage === 'home' || $currentPage === 'accueil' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> <?= isset($tr['home']) ? $tr['home'] : 'Home' ?>
            </a>
        </li>
        
        <?php if ($estConnecte): ?>
            <li class="nav-item">
                <a href="index.php?discover" class="nav-link <?= $currentPage === 'discover' ? 'active' : '' ?>">
                    <i class="fas fa-compass"></i> <?= isset($tr['discover']) ? $tr['discover'] : 'Discover' ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="index.php?communautes" class="nav-link <?= $currentPage === 'communautes' ? 'active' : '' ?>">
                    <i class="fas fa-users"></i> <?= isset($tr['communities']) ? $tr['communities'] : 'Communities' ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="index.php?messages" class="nav-link <?= $currentPage === 'messages' ? 'active' : '' ?>">
                    <i class="fas fa-envelope"></i> <?= isset($tr['messages']) ? $tr['messages'] : 'Messages' ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="index.php?saved" class="nav-link <?= $currentPage === 'saved' ? 'active' : '' ?>">
                    <i class="fas fa-bookmark"></i> <?= isset($tr['saved']) ? $tr['saved'] : 'Saved Posts' ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="index.php?agenda" class="nav-link <?= $currentPage === 'agenda' ? 'active' : '' ?>">
                    <i class="fas fa-calendar"></i> <?= isset($tr['agenda']) ? $tr['agenda'] : 'Agenda' ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="index.php?profile" class="nav-link <?= $currentPage === 'profile' ? 'active' : '' ?>">
                    <i class="fas fa-user"></i> <?= isset($tr['profile']) ? $tr['profile'] : 'Profile' ?>
                </a>
            </li>
        <?php else: ?>
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showLoginModal(event)">
                    <i class="fas fa-compass"></i> <?= isset($tr['discover']) ? $tr['discover'] : 'Discover' ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showLoginModal(event)">
                    <i class="fas fa-users"></i> <?= isset($tr['communities']) ? $tr['communities'] : 'Communities' ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showLoginModal(event)">
                    <i class="fas fa-envelope"></i> <?= isset($tr['messages']) ? $tr['messages'] : 'Messages' ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showLoginModal(event)">
                    <i class="fas fa-bookmark"></i> <?= isset($tr['saved']) ? $tr['saved'] : 'Saved Posts' ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showLoginModal(event)">
                    <i class="fas fa-calendar"></i> <?= isset($tr['agenda']) ? $tr['agenda'] : 'Agenda' ?>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="showLoginModal(event)">
                    <i class="fas fa-user"></i> <?= isset($tr['profile']) ? $tr['profile'] : 'Profile' ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</aside>
