<?php
class VueCarteInteractive
{
    public function __construct()
    {
        // Pas besoin de BDD pour le moment
    }

    public function __toString()
    {
        // Charger les traductions
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        require_once(__DIR__ . '/../lang/lang.php');
        $tr = loadLang($lang) ?? [];

        // Données des communautés tendance
        $communautes = [
            ['nom' => 'AlbanianKitchen', 'membres' => '57K'],
            ['nom' => 'VietnameseStreetFood', 'membres' => '142K'],
            ['nom' => 'FrenchPastry', 'membres' => '98K'],
            ['nom' => 'MediterraneanCuisine', 'membres' => '76K'],
            ['nom' => 'VeganRecipes', 'membres' => '130K'],
        ];

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title><?= isset($tr['map']) ? $tr['map'] : 'Carte Interactive' ?> - claque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/profil-public.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body {
            background-color: #FEF5F1;
            font-family: 'Cabin', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Baloo', cursive;
        }
        .section-title {
            color: #F86015;
        }
        .nav-link.active {
            color: #F86015;
            border-left: 3px solid #F86015;
        }
        a { text-decoration: none !important; }
        .map-wrapper {
            background-color: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-top: 20px;
            position: relative;
            min-height: 650px;
        }
        #map {
            height: 600px;
            width: 100%;
            border-radius: 15px;
            z-index: 1;
            border: 2px solid #F86015;
            position: relative;
            display: block;
        }
        .leaflet-container {
            background: #f0f0f0 !important;
            border-radius: 15px;
        }
        .leaflet-popup {
            z-index: 10 !important;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once(__DIR__ . '/../inc/header.inc.php'); ?>

    <div class="main-container">
        <!-- Left Sidebar -->
        <?php require_once(__DIR__ . '/../inc/sidebar-left.inc.php'); ?>

        <!-- Main Content -->
        <main class="content">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-globe"></i> <?= isset($tr['discover']) ? $tr['discover'] : 'Découvrir' ?></h2>
            </div>

            <section class="section-card" style="padding: 25px; background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="font-size: 22px; margin-top: 0; color: #F86015;"><i class="fas fa-map-marked-alt"></i> 🌍 <?= isset($tr['meet_chefs']) ? $tr['meet_chefs'] : 'Rencontrez des passionnés près de chez vous' ?></h3>
                <p style="color: #666; margin-bottom: 20px;">
                    <?= isset($tr['explore_map']) ? $tr['explore_map'] : 'Explorez la carte pour trouver des chefs seniors et de jeunes apprentis avec qui cuisiner et échanger vos recettes.' ?>
                </p>
                
                <div class="map-wrapper">
                    <div id="map"></div>
                </div>
            </section>
        </main>

        <!-- Right Sidebar -->
        <?php require_once(__DIR__ . '/../inc/sidebar-right.inc.php'); ?>
    </div>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="bottom-nav">
        <div class="bottom-nav-items">
            <a href="index.php?accueil" class="bottom-nav-item">
                <i class="fas fa-home"></i>
                <span><?= isset($tr['home']) ? $tr['home'] : 'Home' ?></span>
            </a>
            <a href="index.php?carte" class="bottom-nav-item active">
                <i class="fas fa-map-marked-alt"></i>
                <span><?= isset($tr['map']) ? $tr['map'] : 'Carte' ?></span>
            </a>
            <a href="index.php?communautes" class="bottom-nav-item">
                <i class="fas fa-users"></i>
                <span><?= isset($tr['communities']) ? $tr['communities'] : 'Communities' ?></span>
            </a>
            <a href="index.php?profile" class="bottom-nav-item">
                <i class="fas fa-user"></i>
                <span><?= isset($tr['profile']) ? $tr['profile'] : 'Profile' ?></span>
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <?php require_once(__DIR__ . '/../inc/footer.inc.php'); ?>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Attendre que le DOM soit complètement chargé
        function initMap() {
            // Vérifier que le conteneur existe
            var mapContainer = document.getElementById('map');
            if (!mapContainer) {
                console.error('Map container not found');
                return;
            }

            // Initialisation de la carte (Centrée sur la France)
            var map = L.map('map', {
                preferCanvas: true,
                attributionControl: true
            }).setView([46.2276, 2.2137], 5);

            // Ajout du calque visuel OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
                opacity: 0.9
            }).addTo(map);

            // Personnalisation de l'icône du marqueur avec la couleur Orange du thème (claque)
            var customIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background-color:#F86015; width:20px; height:20px; border-radius:50%; border:3px solid white; box-shadow: 0 2px 8px rgba(248, 96, 21, 0.4);'></div>",
                iconSize: [26, 26],
                iconAnchor: [13, 13],
                popupAnchor: [0, -13]
            });

            // Faux utilisateur 1 (Senior / Boomer) - Paris
            var marker1 = L.marker([48.8566, 2.3522], {icon: customIcon}).addTo(map);
            marker1.bindPopup("<div style='text-align:center; min-width: 180px;'><b style='color:#D81B60; font-size:14px;'>👩‍🍳 Chef Senior</b><br><span style='color:#634444; font-size:12px;'>Spécialité : Pâtisserie Française</span><br><br><a href='index.php?profile' style='background:#F86015; color:white; padding:5px 10px; border-radius:10px; text-decoration:none; font-size:12px; display:inline-block; margin-top:5px; border: none; cursor: pointer;'>Voir le profil</a></div>", {maxWidth: 220});

            // Faux utilisateur 2 (Jeune / Gen Z) - Lyon
            var marker2 = L.marker([45.7640, 4.8357], {icon: customIcon}).addTo(map);
            marker2.bindPopup("<div style='text-align:center; min-width: 180px;'><b style='color:#4A90E2; font-size:14px;'>🧑‍🎓 Apprenti</b><br><span style='color:#634444; font-size:12px;'>Spécialité : Street Food Vietnamienne</span><br><br><a href='index.php?profile' style='background:#F86015; color:white; padding:5px 10px; border-radius:10px; text-decoration:none; font-size:12px; display:inline-block; margin-top:5px; border: none; cursor: pointer;'>Voir le profil</a></div>", {maxWidth: 220});

            // Faux utilisateur 3 - Marseille
            var marker3 = L.marker([43.2965, 5.3698], {icon: customIcon}).addTo(map);
            marker3.bindPopup("<div style='text-align:center; min-width: 180px;'><b style='color:#E8A71C; font-size:14px;'>👨‍🍳 Chef Expérimenté</b><br><span style='color:#634444; font-size:12px;'>Spécialité : Cuisine Méditerranéenne</span><br><br><a href='index.php?profile' style='background:#F86015; color:white; padding:5px 10px; border-radius:10px; text-decoration:none; font-size:12px; display:inline-block; margin-top:5px; border: none; cursor: pointer;'>Voir le profil</a></div>", {maxWidth: 220});

            // Invalider le rendu de la carte après 100ms pour s'assurer qu'elle s'affiche correctement
            setTimeout(function() {
                map.invalidateSize();
            }, 100);

            // Redimensionner la carte si la fenêtre change de taille
            window.addEventListener('resize', function() {
                map.invalidateSize();
            });
        }

        // Initialiser la carte quand le DOM est prêt
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initMap);
        } else {
            initMap();
        }
    </script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
?>