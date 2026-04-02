<?php
class VueAcceuilDeconnecte
{
    public function __toString()
    {
        return $this->afficherContenu();
    }
 
    private function afficherContenu()
    {
        // Données de démonstration avec vraies images
        $recettes = [
            ['titre' => 'Spicy Albanian Dumplings', 'auteur' => 'AlbanianKitchen', 'vues' => '12.5K', 'badge' => 'Hot', 'image' => 'c'],
            ['titre' => 'Authentic Carbonara', 'auteur' => 'ItalianChef', 'vues' => '8.3K', 'badge' => 'New', 'image' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=400'],
            ['titre' => 'Tokyo Sushi Platter', 'auteur' => 'TokyoFood', 'vues' => '15.2K', 'badge' => 'Top', 'image' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=400'],
            ['titre' => 'Vietnamese Pho Bo', 'auteur' => 'HanoiChef', 'vues' => '9.8K', 'badge' => null, 'image' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=400'],
            ['titre' => 'French Croissant', 'auteur' => 'ParisBaker', 'vues' => '11.4K', 'badge' => 'Classic', 'image' => 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=400'],
        ];
        
        $communautes = [
            ['nom' => 'AlbanianKitchen', 'membres' => '57K'],
            ['nom' => 'VietnameseStreetFood', 'membres' => '142K'],
            ['nom' => 'FrenchPastry', 'membres' => '98K'],
            ['nom' => 'MediterraneanCuisine', 'membres' => '76K'],
            ['nom' => 'VeganRecipes', 'membres' => '130K'],
        ];
        
        global $tr, $lang;
        // Fonction helper pour traduire le contenu utilisateur
        $autoTranslate = function($text, $sourceLang = 'auto') {
            global $lang;
            if ($sourceLang !== 'auto' && $sourceLang === $lang) {
                return $text;
            }
            // Appeler la fonction translateText si elle existe
            if (function_exists('translateText')) {
                return translateText($text, $lang, $sourceLang);
            }
            return $text;
        };
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Security::generateCSRFToken()); ?>">
    <title>claque - <?= isset($tr['slogan']) ? $tr['slogan'] : 'Partage Cullinaire' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --cream: #FFE8CC;
            --orange: #FF7318;
            --orange-dark: #E65A0C;
            --pink: #D81B60;
            --pink-dark: #C2185B;
            --blue: #4A90E2;
            --green: #2ECC71;
            --brown: #634444;
            --brown-dark: #4A3232;
            --white: #FFFFFF;
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-400: #9CA3AF;
            --gray-600: #4B5563;
            --shadow: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-lg: 0 8px 24px rgba(0,0,0,0.12);
            --radius: 12px;
            --radius-lg: 20px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--cream);
            color: var(--brown);
            line-height: 1.6;
        }

        a { text-decoration: none; color: inherit; }
        button { cursor: pointer; border: none; font: inherit; }
        img { max-width: 100%; display: block; }

        /* HEADER */
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
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--orange), var(--pink));
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
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
            background: var(--gray-50);
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
        }

        .btn-connect:hover {
            background: var(--orange-dark);
        }

        /* MAIN LAYOUT */
        .main-container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 24px;
            gap: 24px;
        }

        .sidebar-left {
            width: 200px;
            position: sticky;
            top: 80px;
            height: calc(100vh - 100px);
            overflow-y: auto;
        }

        .nav-menu { list-style: none; }
        .nav-item { margin-bottom: 4px; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            color: var(--brown);
            transition: all 0.2s;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--orange);
            color: var(--white);
        }

        .nav-link i { width: 20px; text-align: center; }

        .content {
            flex: 1;
            max-width: 760px;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 24px 0 16px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
        }

        .see-all {
            color: var(--orange);
            font-weight: 600;
            font-size: 13px;
        }

        /* RECIPES WITH IMAGES */
        .recipes-scroll {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            padding: 4px 4px 12px;
            scroll-snap-type: x mandatory;
        }

        .recipes-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .recipes-scroll::-webkit-scrollbar-thumb {
            background: var(--gray-200);
            border-radius: 3px;
        }

        .recipe-card {
            min-width: 240px;
            scroll-snap-align: start;
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.2s;
        }

        .recipe-card:hover {
            transform: translateY(-4px);
        }

        .recipe-image {
            width: 100%;
            height: 160px;
            position: relative;
            overflow: hidden;
        }

        .recipe-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .recipe-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--white);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            color: var(--orange);
        }

        .recipe-info {
            padding: 14px;
        }

        .recipe-title {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .recipe-author {
            font-size: 12px;
            color: var(--gray-600);
            margin-bottom: 10px;
        }

        .recipe-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 10px;
            border-top: 1px solid var(--gray-100);
        }

        .recipe-stats {
            display: flex;
            gap: 12px;
            font-size: 12px;
            color: var(--gray-600);
        }

        .recipe-stats span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .like-btn {
            background: none;
            border: none;
            color: var(--gray-400);
            font-size: 20px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .like-btn:hover {
            color: var(--pink);
            transform: scale(1.2);
        }

        .like-btn.liked {
            color: var(--pink);
        }

        /* PROMO CARDS */
        .promo-section { margin: 32px 0; }
        .promo-title { font-size: 16px; font-weight: 700; margin-bottom: 16px; }

        .promo-card {
            border-radius: var(--radius-lg);
            padding: 24px;
            margin-bottom: 20px;
            color: var(--white);
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            gap: 24px;
            align-items: center;
        }

        .promo-yellow { background: linear-gradient(135deg, #FFB347 0%, var(--orange) 100%); }
        .promo-pink { background: linear-gradient(135deg, var(--pink) 0%, var(--pink-dark) 100%); }
        .promo-blue { background: linear-gradient(135deg, var(--blue) 0%, #5BA3F5 100%); }
        .promo-green { background: linear-gradient(135deg, var(--green) 0%, #58D68D 100%); }

        .promo-content h3 {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .promo-content p {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
            opacity: 0.95;
        }

        .promo-btn {
            background: var(--white);
            color: var(--brown);
            padding: 10px 22px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
            width: fit-content;
        }

        .promo-icon i {
            font-size: 80px;
            opacity: 0.25;
        }

        /* SIDEBAR RIGHT */
        .sidebar-right {
            width: 280px;
            position: sticky;
            top: 80px;
            height: calc(100vh - 100px);
            overflow-y: auto;
        }

        .trending-box {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow);
        }

        .trending-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .trending-title i { color: var(--orange); }

        .trending-item {
            padding: 14px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .trending-item:last-child { border-bottom: none; }

        .trending-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .trending-members {
            font-size: 12px;
            color: var(--gray-600);
        }

        /* MOBILE HEADER */
        .header-mobile {
            display: none;
            background: var(--white);
            padding: 10px 16px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-mobile-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .logo-mobile {
            font-size: 22px;
            font-weight: 800;
        }

        .search-mobile {
            position: relative;
        }

        .search-mobile input {
            width: 100%;
            padding: 8px 36px 8px 14px;
            border: 2px solid var(--gray-200);
            border-radius: 18px;
            font-size: 13px;
            background: var(--gray-50);
        }

        .search-mobile i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
        }

        /* BOTTOM NAV */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--white);
            box-shadow: 0 -2px 12px rgba(0,0,0,0.08);
            z-index: 1000;
            padding: 6px 0 8px;
        }

        .bottom-nav-items {
            display: flex;
            justify-content: space-around;
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 6px 8px;
            color: var(--gray-600);
            font-size: 11px;
            gap: 4px;
        }

        .bottom-nav-item i { font-size: 20px; }
        .bottom-nav-item.active, .bottom-nav-item:hover { color: var(--orange); }

        /* LOGIN MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: var(--radius-lg);
            padding: 40px;
            max-width: 400px;
            width: 100%;
            text-align: center;
            animation: slideUp 0.3s;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .modal-text {
            color: var(--gray-600);
            margin-bottom: 24px;
            line-height: 1.6;
        }

        .modal-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .modal-btn-primary {
            background: var(--orange);
            color: var(--white);
            padding: 14px;
            border-radius: 24px;
            font-weight: 700;
        }

        .modal-btn-secondary {
            background: var(--gray-100);
            color: var(--brown);
            padding: 14px;
            border-radius: 24px;
            font-weight: 600;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 28px;
            color: var(--gray-400);
            cursor: pointer;
        }

        /* FOOTER */
        .footer {
            background: var(--brown-dark);
            color: var(--white);
            padding: 40px 24px 24px;
            margin-top: 40px;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
            margin-bottom: 32px;
        }

        .footer-brand .logo {
            color: var(--white);
            margin-bottom: 16px;
        }

        .footer-desc {
            font-size: 14px;
            color: rgba(255,255,255,0.8);
            margin-bottom: 20px;
        }

        .footer-social {
            display: flex;
            gap: 12px;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
        }

        .social-link:hover {
            background: var(--orange);
        }

        .footer-section h4 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .footer-section ul { list-style: none; }
        .footer-section li { margin-bottom: 10px; }
        .footer-section a {
            color: rgba(255,255,255,0.8);
            font-size: 14px;
        }
        .footer-section a:hover { color: var(--orange); }

        .footer-bottom {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.1);
            font-size: 13px;
            color: rgba(255,255,255,0.6);
        }

        /* RESPONSIVE */
        @media (max-width: 1100px) {
            .sidebar-right { display: none; }
        }

        @media (max-width: 768px) {
            .header { display: none; }
            .header-mobile { display: block; }
            .sidebar-left { display: none; }
            .bottom-nav { display: flex; }
            
            .main-container {
                padding: 16px;
            }
            
            .content { max-width: 100%; }
            
            .recipe-card { min-width: 200px; }
            .recipe-image { height: 140px; }
            
            .promo-card {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .promo-icon { display: none; }
            
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            body { padding-bottom: 70px; }
        }

        @media (max-width: 480px) {
            .footer-grid { grid-template-columns: 1fr; }
            .recipe-card { min-width: 160px; }
            .recipe-image { height: 120px; }
            .modal-content { padding: 30px 20px; }
        }
    </style>
</head>
<body>
    <!-- Header incluant depuis le composant réutilisable -->
    <?php require_once(__DIR__ . '/../inc/header.inc.php'); ?>

    <div class="main-container">
        <!-- Left Sidebar -->
        <aside class="sidebar-left">
            <ul class="nav-menu">
                <li class="nav-item"><a href="#" class="nav-link active"><i class="fas fa-home"></i> <?= isset($tr['home']) ? $tr['home'] : 'Home' ?></a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="showLoginModal(event)"><i class="fas fa-compass"></i> <?= isset($tr['discover']) ? $tr['discover'] : 'Discover' ?></a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="showLoginModal(event)"><i class="fas fa-users"></i> <?= isset($tr['communities']) ? $tr['communities'] : 'Communities' ?></a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="showLoginModal(event)"><i class="fas fa-envelope"></i> <?= isset($tr['messages']) ? $tr['messages'] : 'Messages' ?></a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="showLoginModal(event)"><i class="fas fa-calendar"></i> <?= isset($tr['agenda']) ? $tr['agenda'] : 'Agenda' ?></a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="showLoginModal(event)"><i class="fas fa-bookmark"></i> <?= isset($tr['saved']) ? $tr['saved'] : 'Saved' ?></a></li>
                <li class="nav-item"><a href="#" class="nav-link" onclick="showLoginModal(event)"><i class="fas fa-user"></i> <?= isset($tr['profile']) ? $tr['profile'] : 'Profile' ?></a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="content">
            <!-- Trending Recipes -->
            <div class="section-header">
                <h2 class="section-title"><?= isset($tr['trending_recipes']) ? $tr['trending_recipes'] : 'Trending recipes' ?></h2>
                <a href="#" class="see-all"><?= isset($tr['see_all']) ? $tr['see_all'] : 'See all' ?> <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="recipes-scroll">
                <?php foreach ($recettes as $recette): ?>
                <div class="recipe-card">
                    <div class="recipe-image">
                        <img src="<?= $recette['image'] ?>" alt="<?= htmlspecialchars($recette['titre']) ?>">
                        <?php if ($recette['badge']): ?>
                            <span class="recipe-badge"><?= $recette['badge'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="recipe-info">
                        <h3 class="recipe-title"><?= htmlspecialchars($recette['titre']) ?></h3>
                        <p class="recipe-author"><?= isset($tr['by']) ? $tr['by'] : 'by' ?> <?= htmlspecialchars($recette['auteur']) ?></p>
                        <div class="recipe-actions">
                            <div class="recipe-stats">
                                <span><i class="fas fa-eye"></i> <?= $recette['vues'] ?></span>
                                <span><i class="fas fa-heart"></i> 234</span>
                            </div>
                            <button class="like-btn" onclick="showLoginModal(event)">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Promo Sections -->
            <section class="promo-section">
                <h3 class="promo-title"><?= isset($tr['promo_title']) ? $tr['promo_title'] : 'Qué peut-on faire sur claque ?' ?></h3>
                
                <div class="promo-card promo-yellow">
                    <div class="promo-content">
                        <h3><?= isset($tr['promo1_title']) ? $tr['promo1_title'] : 'Cook, share, connect.' ?></h3>
                        <p><?= isset($tr['promo1_text']) ? $tr['promo1_text'] : 'Plan your cooking sessions, share technical tips, or simply discuss recipes with people worldwide.' ?></p>
                        <button class="promo-btn" onclick="showLoginModal(event)"><?= isset($tr['learn_more']) ? $tr['learn_more'] : 'Learn more' ?> →</button>
                    </div>
                    <div class="promo-icon"><i class="fas fa-utensils"></i></div>
                </div>

                <div class="promo-card promo-pink">
                    <div class="promo-content">
                        <h3><?= isset($tr['promo2_title']) ? $tr['promo2_title'] : 'Find inspiration' ?></h3>
                        <p><?= isset($tr['promo2_text']) ? $tr['promo2_text'] : 'Explore our interactive map to meet food enthusiasts from France, Albania, Vietnam, and beyond.' ?></p>
                        <button class="promo-btn" onclick="showLoginModal(event)"><?= isset($tr['start_exploring']) ? $tr['start_exploring'] : 'Start exploring' ?></button>
                    </div>
                    <div class="promo-icon"><i class="fas fa-map-marked-alt"></i></div>
                </div>

                <div class="promo-card promo-blue">
                    <div class="promo-content">
                        <h3><?= isset($tr['promo3_title']) ? $tr['promo3_title'] : 'Share your heritage' ?></h3>
                        <p><?= isset($tr['promo3_text']) ? $tr['promo3_text'] : 'Pass on your family recipes and cooking secrets. Your culinary tradition is a valuable bridge between generations.' ?></p>
                        <button class="promo-btn" onclick="showLoginModal(event)"><?= isset($tr['start_sharing']) ? $tr['start_sharing'] : 'Start sharing' ?></button>
                    </div>
                    <div class="promo-icon"><i class="fas fa-heart"></i></div>
                </div>

                <div class="promo-card promo-green">
                    <div class="promo-content">
                        <h3><?= isset($tr['promo4_title']) ? $tr['promo4_title'] : 'Cook together' ?></h3>
                        <p><?= isset($tr['promo4_text']) ? $tr['promo4_text'] : 'Join public workshops or book private sessions. Use our integrated agenda to plan sessions.' ?></p>
                        <button class="promo-btn" onclick="showLoginModal(event)"><?= isset($tr['join_session']) ? $tr['join_session'] : 'Join a session' ?></button>
                    </div>
                    <div class="promo-icon"><i class="fas fa-users"></i></div>
                </div>
            </section>
        </main>

        <!-- Right Sidebar -->
        <aside class="sidebar-right">
            <div class="trending-box">
                <h3 class="trending-title">
                    <i class="fas fa-fire"></i>
                    <?= isset($tr['trending_communities']) ? $tr['trending_communities'] : 'Trending communities' ?>
                </h3>
                
                <?php foreach ($communautes as $communaute): ?>
                <div class="trending-item">
                    <div class="trending-name"><?= htmlspecialchars($communaute['nom']) ?></div>
                    <div class="trending-members"><?= $communaute['membres'] ?> <?= isset($tr['members']) ? $tr['members'] : 'members' ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </aside>
    </div>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="bottom-nav">
        <div class="bottom-nav-items">
            <a href="#" class="bottom-nav-item active">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="#" class="bottom-nav-item" onclick="showLoginModal(event)">
                <i class="fas fa-calendar"></i>
                <span>Agenda</span>
            </a>
            <a href="#" class="bottom-nav-item">
                <i class="fas fa-search"></i>
                <span>Search</span>
            </a>
            <a href="#" class="bottom-nav-item" onclick="showLoginModal(event)">
                <i class="fas fa-comment"></i>
                <span>Messages</span>
            </a>
            <a href="#" class="bottom-nav-item" onclick="showLoginModal(event)">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <!-- Login Modal -->
    <div class="modal-overlay" id="loginModal">
        <div class="modal-content" style="position: relative;">
            <span class="modal-close" onclick="closeLoginModal()">&times;</span>
            <div class="modal-icon">🔐</div>
            <h2 class="modal-title"><?= isset($tr['connect_kami']) ? $tr['connect_kami'] : 'Connect to claque' ?></h2>
            <p class="modal-text">
                <?= isset($tr['login_required']) ? $tr['login_required'] : 'You need to be logged in to access this feature. Join our community to share recipes, connect with cooks worldwide, and start your culinary journey!' ?>
            </p>
            <div class="modal-buttons">
                <a href="index.php?login" class="modal-btn-primary"><?= isset($tr['sign_in']) ? $tr['sign_in'] : 'Sign In' ?></a>
                <a href="index.php?register" class="modal-btn-secondary"><?= isset($tr['create_account']) ? $tr['create_account'] : 'Create Account' ?></a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="index.php" class="logo" style="gap: 12px;">
                        <img src="assets/logo.png" alt="logo" style="width: 40px; height: 40px; object-fit: contain;">
                        <span>claque</span>
                    </a>
                    <p class="footer-desc">
                        Cross-generational platform connecting skills and experiences through cooking.
                    </p>
                    <div class="footer-social">
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-tiktok"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Platform</h4>
                    <ul>
                        <li><a href="#">How it works</a></li>
                        <li><a href="#">Communities</a></li>
                        <li><a href="#">Agenda</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="privacy.php">Privacy Policy (GDPR)</a></li>
                        <li><a href="pages/terms.php">Terms of Service</a></li>
                        <li><a href="class/legal.php">Legal Notice</a></li>
                        <li><a href="#">Cookies</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Partners</h4>
                    <ul>
                        <li><a href="#">IUT Bobigny</a></li>
                        <li><a href="#">Univ. Tirana</a></li>
                        <li><a href="#">HUS VNU</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 claque Platform. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Language Selector
        const langSelector = document.getElementById('langSelector');
        const langBtn = document.getElementById('langBtn');
        const langOptions = document.querySelectorAll('.lang-option');

        langBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            langSelector.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!langSelector.contains(e.target)) {
                langSelector.classList.remove('active');
            }
        });

        langOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                // Permet la vraie navigation vers ?lang=xx
                const lang = option.dataset.lang;
                // Récupère l'URL actuelle sans paramètres
                const url = window.location.pathname + '?lang=' + lang;
                window.location.href = url;
            });
        });

        // Login Modal
        function showLoginModal(e) {
            if (e) e.preventDefault();
            document.getElementById('loginModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginModal() {
            document.getElementById('loginModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        // Close modal on outside click
        document.getElementById('loginModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('loginModal')) {
                closeLoginModal();
            }
        });

        // Bottom Nav Active
        const bottomNavItems = document.querySelectorAll('.bottom-nav-item');
        bottomNavItems.forEach(item => {
            item.addEventListener('click', function(e) {
                if (!this.getAttribute('onclick')) {
                    bottomNavItems.forEach(i => i.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
?>