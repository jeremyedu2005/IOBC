<?php
/**
 * Header Component - Réutilisable sur toutes les pages
 */
global $tr, $lang;
$currentLang = isset($lang) ? $lang : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
?>
<style>
    :root {
        --cream: #FFE8CC;
        --orange: #FF7318;
        --orange-dark: #E65A0C;
        --pink: #D81B60;
        --brown: #634444;
        --white: #FFFFFF;
        --gray-100: #F3F4F6;
        --gray-200: #E5E7EB;
        --gray-400: #9CA3AF;
        --gray-600: #4B5563;
        --shadow: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
    }

    .header {
        background: var(--white);
        padding: 12px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: var(--shadow);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .logo {
        font-size: 24px;
        font-weight: 800;
        color: var(--brown);
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        text-decoration: none;
    }

    .logo-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        overflow: hidden;
    }

    .logo-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .search-bar {
        flex: 1;
        max-width: 480px;
        margin: 0 32px;
        position: relative;
    }

    .search-bar input {
        width: 100%;
        padding: 10px 40px 10px 16px;
        border: 2px solid var(--gray-200);
        border-radius: 24px;
        font-size: 14px;
        background: var(--gray-100);
    }

    .search-bar input:focus {
        outline: none;
        border-color: var(--orange);
        background: var(--white);
    }

    .search-bar i {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-400);
        pointer-events: none;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .lang-selector {
        position: relative;
    }

    .lang-btn {
        background: var(--gray-100);
        padding: 8px 14px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 13px;
        display: flex;
        align-items: center;
        gap: 6px;
        border: none;
        cursor: pointer;
    }

    .lang-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 8px;
        background: var(--white);
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        display: none;
        min-width: 160px;
    }

    .lang-selector.active .lang-dropdown {
        display: block;
    }

    .lang-option {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        font-size: 14px;
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .lang-option:hover, .lang-option.active {
        background: var(--cream);
        color: var(--orange);
    }

    .btn-connect {
        background: var(--orange);
        color: var(--white);
        padding: 10px 24px;
        border-radius: 24px;
        font-weight: 600;
        font-size: 14px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-connect:hover {
        background: var(--orange-dark);
    }

    .header-user-menu {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-left: 12px;
    }

    .user-profile-short {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--orange);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        cursor: pointer;
    }

    .user-menu {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 8px;
        background: var(--white);
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        display: none;
        min-width: 200px;
    }

    .header-user-menu.active .user-menu {
        display: block;
    }

    .user-menu-item {
        display: block;
        padding: 10px 16px;
        font-size: 14px;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid var(--gray-100);
    }

    .user-menu-item:hover {
        background: var(--cream);
        color: var(--orange);
    }

    .user-menu-item:last-child {
        border-bottom: none;
    }

    @media (max-width: 768px) {
        .search-bar {
            max-width: 200px;
            margin: 0 16px;
        }

        .header {
            padding: 10px 16px;
        }

        .logo span {
            font-size: 18px;
        }
    }
</style>

<!-- Desktop Header -->
<header class="header">
    <a href="index.php" class="logo">
        <div class="logo-icon">
            <img src="<?php echo ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/assets/logo.png'; ?>" alt="logo" style="height: 40px;"><img src="<?php echo ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/assets/claque.png'; ?>" alt="claque" style="height: 40px; margin-left: 5px;">
        </div>
    </a>
    
    <div class="search-bar">
        <input type="text" placeholder="<?= isset($tr['search_placeholder']) ? $tr['search_placeholder'] : 'Cherchez des recettes, compétences...' ?>">
        <i class="fas fa-search"></i>
    </div>
    
    <div class="header-actions">
        <div class="lang-selector" id="langSelector">
            <button class="lang-btn" id="langBtn">
                <i class="fas fa-globe"></i> 
                <span id="langCode"><?= strtoupper($currentLang) ?></span>
            </button>
            <div class="lang-dropdown" id="langDropdown">
                <?php
                $langs = [
                    'en' => '🇬🇧 English',
                    'fr' => '🇫🇷 Français',
                    'vi' => '🇻🇳 Tiếng Việt',
                    'sq' => '🇦🇱 Shqip',
                ];
                foreach ($langs as $code => $label) {
                    $active = ($currentLang === $code) ? 'active' : '';
                    echo '<a href="?lang=' . $code . '" class="lang-option ' . $active . '" data-lang="' . $code . '">' . $label . '</a>';
                }
                ?>
            </div>
        </div>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <!-- User Connecté -->
            <div class="header-user-menu" id="userMenu">
                <div class="user-profile-short" id="userMenuBtn">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                </div>
                <div class="user-menu">
                    <a href="index.php?profile" class="user-menu-item">👤 <?= isset($tr['profile']) ? $tr['profile'] : 'Profil' ?></a>
                    <a href="index.php?modifprofil" class="user-menu-item">⚙️ <?= isset($tr['settings']) ? $tr['settings'] : 'Paramètres' ?></a>
                    <a href="index.php?logout" class="user-menu-item" style="color: #D81B60;">🚪 <?= isset($tr['logout']) ? $tr['logout'] : 'Déconnexion' ?></a>
                </div>
            </div>
        <?php else: ?>
            <!-- Utilisateur Non Connecté -->
            <a href="index.php?login" class="btn-connect"><?= isset($tr['login']) ? $tr['login'] : 'Se connecter' ?></a>
        <?php endif; ?>
    </div>
</header>

<script>
    // Language selector
    document.getElementById('langSelector').addEventListener('click', function(e) {
        e.stopPropagation();
        this.classList.toggle('active');
    });

    // User menu
    const userMenu = document.getElementById('userMenu');
    if (userMenu) {
        document.getElementById('userMenuBtn').addEventListener('click', function(e) {
            e.stopPropagation();
            userMenu.classList.toggle('active');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.getElementById('langSelector').classList.remove('active');
        if (userMenu) userMenu.classList.remove('active');
    });
</script>
