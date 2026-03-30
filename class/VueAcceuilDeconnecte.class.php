<?php
// VueAcceuilDeconnecte.class.php
// Page d'accueil pour les visiteurs NON connectés

class VueAcceuilDeconnecte
{
    /**
     * Affichage automatique : echo new VueAcceuilDeconnecte();
     */
    public function __toString()
    {
        // Récupère l'utilisateur depuis la session si connecté
        $utilisateur = isset($_SESSION['username']) 
            ? (object)['pseudo' => $_SESSION['username']] 
            : null;
            
        return $this->afficherContenu($utilisateur);
    }
 
    /**
     * Génère le HTML de la page d'accueil déconnecté
     * @param object|null $utilisateur
     */
    private function afficherContenu($utilisateur)
    {
        // Données de démonstration (à remplacer par un modèle plus tard)
        $recettes = [
            ['titre' => 'Spicy Dumplings', 'auteur' => 'AlbanianKitchen', 'vues' => '12.5K', 'badge' => 'Hot'],
            ['titre' => 'Pasta Carbonara', 'auteur' => 'ItalianChef', 'vues' => '8.3K', 'badge' => 'New'],
            ['titre' => 'Sushi Platter', 'auteur' => 'TokyoFood', 'vues' => '15.2K', 'badge' => 'Top'],
        ];
        
        $groupes = [
            ['nom' => 'AlbanianKitchen', 'membres' => '57K', 'initiale' => 'A'],
            ['nom' => 'StreetFood', 'membres' => '268K', 'initiale' => 'S'],
            ['nom' => 'VeganRecipes', 'membres' => '130K', 'initiale' => 'V'],
            ['nom' => 'GourmetCooking', 'membres' => '85K', 'initiale' => 'G'],
        ];
        
        ob_start(); // Bufferisation pour éviter les problèmes d'headers
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>KAM - Partagez vos recettes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --cream: #FFE8CC;
            --orange: #FF7318;
            --pink: #D81B60;
            --blue: #4A90E2;
            --green: #2ECC71;
            --brown: #634444;
            --white: #FFFFFF;
            --gray-light: #F5F5F5;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-hover: 0 8px 30px rgba(0,0,0,0.15);
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--cream);
            color: var(--brown);
            line-height: 1.6;
            padding-bottom: 80px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Header Desktop */
        .header-desktop {
            background-color: var(--white);
            padding: 15px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo {
            font-size: 32px;
            font-weight: 800;
            color: var(--brown);
            text-decoration: none;
            letter-spacing: -1px;
        }

        .search-bar-desktop {
            flex: 1;
            max-width: 500px;
            margin: 0 40px;
            position: relative;
        }

        .search-bar-desktop input {
            width: 100%;
            padding: 12px 45px 12px 20px;
            border: 2px solid var(--cream);
            border-radius: 25px;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
            background-color: var(--gray-light);
        }

        .search-bar-desktop input:focus {
            border-color: var(--orange);
            background-color: var(--white);
            box-shadow: 0 0 0 4px rgba(255, 115, 24, 0.1);
        }

        .search-bar-desktop i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--orange);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-notif {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--cream);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-notif:hover {
            background-color: var(--orange);
            color: var(--white);
            transform: scale(1.1);
        }

        .badge-notif {
            position: absolute;
            top: -2px;
            right: -2px;
            background-color: var(--pink);
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 10px;
            font-weight: 700;
        }

        .btn-connect {
            background-color: var(--orange);
            color: var(--white);
            padding: 10px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(255, 115, 24, 0.3);
        }

        .btn-connect:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 115, 24, 0.4);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--orange), var(--pink));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        /* Layout */
        .main-container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Sidebar Gauche */
        .sidebar-left {
            width: 260px;
            padding: 30px 20px;
            position: sticky;
            top: 70px;
            height: calc(100vh - 70px);
            overflow-y: auto;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-menu li {
            margin-bottom: 8px;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 18px;
            color: var(--brown);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 15px;
        }

        .nav-menu a:hover, .nav-menu a.active {
            background-color: var(--orange);
            color: var(--white);
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(255, 115, 24, 0.3);
        }

        .nav-menu i {
            font-size: 20px;
            width: 25px;
            text-align: center;
        }

        /* Contenu Principal */
        .content {
            flex: 1;
            padding: 30px;
            max-width: 900px;
        }

        /* Hero Banner */
        .hero-banner {
            background: linear-gradient(135deg, var(--orange), var(--pink));
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 35px;
            color: var(--white);
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-banner h1 {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .hero-banner p {
            font-size: 15px;
            opacity: 0.95;
        }

        /* Section Title */
        .section-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 25px;
            color: var(--brown);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .see-all {
            color: var(--orange);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: gap 0.3s;
        }

        .see-all:hover {
            gap: 10px;
        }

        /* Recettes */
        .recipes-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .recipe-card {
            background-color: var(--white);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: all 0.3s;
            cursor: pointer;
            animation: fadeIn 0.6s ease-out;
        }

        .recipe-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .recipe-image {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, var(--cream), #FFD4A3);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .recipe-image i {
            font-size: 50px;
            color: var(--orange);
            opacity: 0.6;
        }

        .recipe-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background-color: var(--white);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            color: var(--orange);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .recipe-info {
            padding: 18px;
        }

        .recipe-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 8px;
            color: var(--brown);
        }

        .recipe-author {
            font-size: 13px;
            color: #888;
            margin-bottom: 12px;
        }

        .recipe-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .recipe-stats {
            display: flex;
            gap: 12px;
            font-size: 13px;
            color: #999;
        }

        .recipe-stats i {
            margin-right: 4px;
        }

        .recipe-heart {
            color: var(--pink);
            cursor: pointer;
            font-size: 20px;
            transition: transform 0.2s;
        }

        .recipe-heart:hover {
            transform: scale(1.2);
        }

        .recipe-heart.liked {
            animation: pulse 0.3s;
        }

        /* Promo Sections */
        .promo-container {
            margin-bottom: 40px;
        }

        .promo-section {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            border-radius: 24px;
            overflow: hidden;
            margin-bottom: 25px;
            box-shadow: var(--shadow);
            transition: all 0.3s;
            animation: fadeIn 0.6s ease-out;
        }

        .promo-section:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
        }

        .promo-image {
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .promo-image::before {
            content: '';
            position: absolute;
            width: 150%;
            height: 150%;
            background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
            top: -50%;
            left: -50%;
        }

        .promo-image i {
            font-size: 80px;
            color: rgba(255,255,255,0.9);
            position: relative;
            z-index: 1;
        }

        .promo-content {
            padding: 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: var(--white);
        }

        .promo-pink { background: linear-gradient(135deg, var(--pink), #E91E63); }
        .promo-blue { background: linear-gradient(135deg, var(--blue), #5BA3F5); }
        .promo-green { background: linear-gradient(135deg, var(--green), #58D68D); }

        .promo-title {
            font-size: 26px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .promo-text {
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 20px;
            opacity: 0.95;
        }

        .btn-promo {
            background-color: var(--white);
            color: var(--brown);
            padding: 12px 28px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 700;
            width: fit-content;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn-promo:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        /* Sidebar Droite */
        .sidebar-right {
            width: 300px;
            padding: 30px 20px;
            position: sticky;
            top: 70px;
            height: calc(100vh - 70px);
            overflow-y: auto;
        }

        .trending-box {
            background-color: var(--white);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow);
        }

        .trending-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--brown);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .trending-title i {
            color: var(--orange);
        }

        .trending-item {
            padding: 15px 0;
            border-bottom: 1px solid var(--gray-light);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .trending-item:hover {
            background-color: var(--cream);
            margin: 0 -10px;
            padding-left: 10px;
            padding-right: 10px;
            border-radius: 10px;
        }

        .trending-item:last-child {
            border-bottom: none;
        }

        .trending-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--orange), var(--pink));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        .trending-info {
            flex: 1;
        }

        .trending-name {
            font-weight: 700;
            color: var(--brown);
            font-size: 14px;
            margin-bottom: 3px;
        }

        .trending-members {
            font-size: 12px;
            color: #888;
        }

        /* Header Mobile */
        .header-mobile {
            display: none;
            background-color: var(--white);
            padding: 12px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-mobile-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .logo-mobile {
            font-size: 24px;
            font-weight: 800;
            color: var(--brown);
        }

        .btn-connect-mobile {
            background-color: var(--orange);
            color: var(--white);
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .search-mobile {
            position: relative;
        }

        .search-mobile input {
            width: 100%;
            padding: 10px 40px 10px 15px;
            border: 2px solid var(--cream);
            border-radius: 20px;
            font-size: 13px;
            background-color: var(--gray-light);
        }

        .search-mobile i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--orange);
        }

        /* Bottom Navigation */
        .bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: var(--white);
            box-shadow: 0 -4px 20px rgba(0,0,0,0.08);
            z-index: 1000;
            padding: 8px 0;
            padding-bottom: env(safe-area-inset-bottom, 0);
        }

        .bottom-nav-items {
            display: flex;
            justify-content: space-around;
            align-items: center;
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            text-decoration: none;
            color: #999;
            transition: all 0.3s;
            min-width: 60px;
            border-radius: 12px;
        }

        .bottom-nav-item i {
            font-size: 22px;
            margin-bottom: 4px;
        }

        .bottom-nav-item span {
            font-size: 11px;
            font-weight: 600;
        }

        .bottom-nav-item:hover,
        .bottom-nav-item.active {
            color: var(--orange);
            background-color: var(--cream);
        }

        .bottom-nav-item.active i {
            font-weight: 900;
            transform: scale(1.1);
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .sidebar-right { display: none; }
        }

        @media (max-width: 900px) {
            .recipes-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .sidebar-left { display: none; }
            .header-desktop { display: none; }
            .header-mobile { display: block; }
            .bottom-nav { display: block; }
            
            body { padding-bottom: 80px; }
            
            .content { padding: 20px 15px; }
            
            .recipes-grid { 
                grid-template-columns: 1fr; 
                gap: 15px;
            }
            
            .promo-section {
                grid-template-columns: 1fr;
            }
            
            .promo-image {
                padding: 40px;
                min-height: 180px;
            }
            
            .promo-content {
                padding: 25px;
            }
            
            .hero-banner {
                padding: 25px;
            }
            
            .hero-banner h1 {
                font-size: 22px;
            }
        }

        /* Loading animation */
        .skeleton {
            background: linear-gradient(90deg, var(--cream) 25%, #e0e0e0 50%, var(--cream) 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>
    <!-- Header Desktop -->
    <header class="header-desktop">
        <a href="#" class="logo">🍳 KAM</a>
        
        <div class="search-bar-desktop">
            <input type="text" placeholder="Rechercher des recettes, ingrédients...">
            <i class="fas fa-search"></i>
        </div>
        
        <div class="header-actions">
            <div class="btn-notif">
                <i class="fas fa-bell"></i>
                <span class="badge-notif">3</span>
            </div>
            <?php if ($utilisateur): ?>
                <div class="user-avatar">
                    <?= strtoupper(substr($utilisateur->pseudo, 0, 1)) ?>
                </div>
            <?php else: ?>
                <a href="index.php?login" class="btn-connect">Connexion</a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Header Mobile -->
    <header class="header-mobile">
        <div class="header-mobile-top">
            <div class="logo-mobile">🍳 KAM</div>
            <?php if ($utilisateur): ?>
                <div class="user-avatar" style="width:35px;height:35px;font-size:14px;">
                    <?= strtoupper(substr($utilisateur->pseudo, 0, 1)) ?>
                </div>
            <?php else: ?>
                <a href="index.php?login" class="btn-connect-mobile">Connexion</a>
            <?php endif; ?>
        </div>
        <div class="search-mobile">
            <input type="text" placeholder="Rechercher...">
            <i class="fas fa-search"></i>
        </div>
    </header>

    <div class="main-container">
        <!-- Sidebar Gauche (Desktop) -->
        <aside class="sidebar-left">
            <ul class="nav-menu">
                <li><a href="#" class="active"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#"><i class="fas fa-compass"></i> Discover</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Groups</a></li>
                <li><a href="#"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="#"><i class="fas fa-calendar-alt"></i> Calendar</a></li>
                <li><a href="#"><i class="fas fa-bookmark"></i> Saved</a></li>
                <li><a href="#"><i class="fas fa-user-circle"></i> Profile</a></li>
            </ul>
        </aside>

        <!-- Contenu Principal -->
        <main class="content">
            <!-- Hero Banner -->
            <div class="hero-banner">
                <div class="hero-content">
                    <h1>👋 Bienvenue sur KAM</h1>
                    <p>Partagez vos recettes, découvrez de nouvelles saveurs et cuisinez ensemble !</p>
                </div>
            </div>

            <!-- Trendy Recipes -->
            <h2 class="section-title">
                Trendy recipes
                <a href="#" class="see-all">See all <i class="fas fa-arrow-right"></i></a>
            </h2>
            
            <div class="recipes-grid">
                <?php
                $recettes = [
                    ['titre' => 'Spicy Dumplings', 'auteur' => 'AlbanianKitchen', 'vues' => '12.5K', 'badge' => 'Hot'],
                    ['titre' => 'Pasta Carbonara', 'auteur' => 'ItalianChef', 'vues' => '8.3K', 'badge' => 'New'],
                    ['titre' => 'Sushi Platter', 'auteur' => 'TokyoFood', 'vues' => '15.2K', 'badge' => 'Top'],
                ];
                foreach ($recettes as $recette):
                ?>
                <div class="recipe-card">
                    <div class="recipe-image">
                        <i class="fas fa-utensils"></i>
                        <?php if (isset($recette['badge'])): ?>
                        <span class="recipe-badge"><?= $recette['badge'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="recipe-info">
                        <h3 class="recipe-title"><?= htmlspecialchars($recette['titre']) ?></h3>
                        <p class="recipe-author">Par <?= htmlspecialchars($recette['auteur']) ?></p>
                        <div class="recipe-meta">
                            <div class="recipe-stats">
                                <span><i class="fas fa-eye"></i> <?= $recette['vues'] ?></span>
                                <span><i class="fas fa-heart"></i> 234</span>
                            </div>
                            <i class="far fa-heart recipe-heart"></i>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Promo Sections -->
            <div class="promo-container">
                <div class="promo-section promo-pink">
                    <div class="promo-image">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div class="promo-content">
                        <h3 class="promo-title">Find inspiration</h3>
                        <p class="promo-text">
                            Explore our interactive map to meet food enthusiasts from your country. 
                            Use filters by language or specific skills.
                        </p>
                        <button class="btn-promo" onclick="window.location.href='index.php?carte'">Start finding</button>
                    </div>
                </div>

                <div class="promo-section promo-blue">
                    <div class="promo-image">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="promo-content">
                        <h3 class="promo-title">Share your heritage</h3>
                        <p class="promo-text">
                            Pass on your cooking secrets, treasured by you and your family. 
                            Your cultural tradition is a valuable bridge.
                        </p>
                        <button class="btn-promo">Start sharing</button>
                    </div>
                </div>

                <div class="promo-section promo-green">
                    <div class="promo-image">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="promo-content">
                        <h3 class="promo-title">Cook together</h3>
                        <p class="promo-text">
                            Join public workshops or book private sessions with professional chefs. 
                            Learn new techniques.
                        </p>
                        <button class="btn-promo">Join now</button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Sidebar Droite (Desktop) -->
        <aside class="sidebar-right">
            <div class="trending-box">
                <h3 class="trending-title">
                    <i class="fas fa-fire"></i>
                    Trending groups
                </h3>
                
                <?php
                $groupes = [
                    ['nom' => 'AlbanianKitchen', 'membres' => '57K', 'initiale' => 'A'],
                    ['nom' => 'StreetFood', 'membres' => '268K', 'initiale' => 'S'],
                    ['nom' => 'VeganRecipes', 'membres' => '130K', 'initiale' => 'V'],
                    ['nom' => 'GourmetCooking', 'membres' => '85K', 'initiale' => 'G'],
                ];
                foreach ($groupes as $groupe):
                ?>
                <div class="trending-item">
                    <div class="trending-icon"><?= $groupe['initiale'] ?></div>
                    <div class="trending-info">
                        <div class="trending-name"><?= htmlspecialchars($groupe['nom']) ?></div>
                        <div class="trending-members"><?= $groupe['membres'] ?> Members</div>
                    </div>
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
            <a href="#" class="bottom-nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Calendar</span>
            </a>
            <a href="#" class="bottom-nav-item">
                <i class="fas fa-search"></i>
                <span>Search</span>
            </a>
            <a href="#" class="bottom-nav-item">
                <i class="fas fa-comment-alt"></i>
                <span>Messages</span>
            </a>
            <a href="#" class="bottom-nav-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <script>
        // Animation des coeurs
        document.querySelectorAll('.recipe-heart').forEach(heart => {
            heart.addEventListener('click', function(e) {
                e.stopPropagation();
                this.classList.toggle('far');
                this.classList.toggle('fas');
                this.classList.toggle('liked');
                
                // Effet de confetti simple
                if (this.classList.contains('fas')) {
                    createParticles(this);
                }
            });
        });

        // Création de particules
        function createParticles(element) {
            const rect = element.getBoundingClientRect();
            for (let i = 0; i < 6; i++) {
                const particle = document.createElement('div');
                particle.style.position = 'fixed';
                particle.style.left = rect.left + rect.width/2 + 'px';
                particle.style.top = rect.top + rect.height/2 + 'px';
                particle.style.width = 8 + 'px';
                particle.style.height = 8 + 'px';
                particle.style.background = '#D81B60';
                particle.style.borderRadius = '50%';
                particle.style.pointerEvents = 'none';
                particle.style.zIndex = 9999;
                document.body.appendChild(particle);
                
                const angle = (i / 6) * Math.PI * 2;
                const velocity = 50;
                const tx = Math.cos(angle) * velocity;
                const ty = Math.sin(angle) * velocity;
                
                particle.animate([
                    { transform: 'translate(0,0) scale(1)', opacity: 1 },
                    { transform: `translate(${tx}px, ${ty}px) scale(0)`, opacity: 0 }
                ], {
                    duration: 600,
                    easing: 'cubic-bezier(0, .9, .57, 1)',
                }).onfinish = () => particle.remove();
            }
        }

        // Navigation active
        document.querySelectorAll('.bottom-nav-item, .nav-menu a').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.bottom-nav-item').forEach(i => i.classList.remove('active'));
                document.querySelectorAll('.nav-menu a').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.promo-section, .recipe-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'all 0.6s ease-out';
            observer.observe(el);
        });
    </script>
</body>
</html>
<?php
        return ob_get_clean();
    }
}
?>