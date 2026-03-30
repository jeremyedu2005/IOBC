<?php
class VueCarteInteractive
{
    public function __construct()
    {
    }

    public function __toString()
    {
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAM - Interactive Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* On reprend les variables et le style global de l'accueil */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --cream: #FFE8CC; --orange: #FF7318; --pink: #D81B60;
            --blue: #4A90E2; --green: #2ECC71; --brown: #634444;
            --white: #FFFFFF; --gray-light: #F5F5F5;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--cream);
            color: var(--brown);
        }
        
        /* Header Desktop */
        .header-desktop {
            background-color: var(--white); padding: 15px 40px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky; top: 0; z-index: 1000;
        }
        .logo { font-size: 32px; font-weight: 800; color: var(--brown); text-decoration: none; letter-spacing: -1px; }
        .search-bar-desktop { flex: 1; max-width: 500px; margin: 0 40px; position: relative; }
        .search-bar-desktop input {
            width: 100%; padding: 12px 45px 12px 20px; border: 2px solid var(--cream);
            border-radius: 25px; font-size: 14px; outline: none; background-color: var(--gray-light);
        }
        .search-bar-desktop i { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: var(--orange); }
        .btn-connect {
            background-color: var(--orange); color: var(--white); padding: 10px 25px;
            border: none; border-radius: 25px; cursor: pointer; font-weight: 600; text-decoration: none;
        }

        /* Layout */
        .main-container { display: flex; max-width: 1400px; margin: 0 auto; }
        
        /* Sidebar Gauche */
        .sidebar-left {
            width: 260px; padding: 30px 20px; position: sticky;
            top: 70px; height: calc(100vh - 70px); overflow-y: auto;
        }
        .nav-menu { list-style: none; }
        .nav-menu li { margin-bottom: 8px; }
        .nav-menu a {
            display: flex; align-items: center; gap: 15px; padding: 14px 18px;
            color: var(--brown); text-decoration: none; border-radius: 12px; font-weight: 500;
        }
        .nav-menu a:hover, .nav-menu a.active { background-color: var(--orange); color: var(--white); }
        .nav-menu i { font-size: 20px; width: 25px; text-align: center; }

        /* Contenu de la carte */
        .content { flex: 1; padding: 30px; max-width: 1000px; }
        .section-title { font-size: 24px; font-weight: 700; margin-bottom: 20px; color: var(--brown); }
        
        .map-wrapper {
            background-color: var(--white);
            border-radius: 24px;
            padding: 20px;
            box-shadow: var(--shadow);
        }
        
        /* Configuration de la div Leaflet */
        #map {
            height: 600px;
            width: 100%;
            border-radius: 16px;
            z-index: 1; /* Empêche la carte de passer au-dessus du menu déroulant */
        }
    </style>
</head>
<body>
    <header class="header-desktop">
        <a href="index.php?accueil" class="logo">🍳 KAM</a>
        <div class="search-bar-desktop">
            <input type="text" placeholder="Rechercher des chefs, spécialités...">
            <i class="fas fa-search"></i>
        </div>
        <div>
            <a href="index.php?login" class="btn-connect">Connexion</a>
        </div>
    </header>

    <div class="main-container">
        <aside class="sidebar-left">
            <ul class="nav-menu">
                <li><a href="index.php?accueil"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#" class="active"><i class="fas fa-map-marked-alt"></i> Map</a></li>
                <li><a href="#"><i class="fas fa-compass"></i> Discover</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Groups</a></li>
            </ul>
        </aside>

        <main class="content">
            <h2 class="section-title"> Rencontrez des passionnés de cuisine</h2>
            
            <div class="map-wrapper">
                <div id="map"></div>
            </div>
        </main>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialisation de la carte sur Paris
        var map = L.map('map').setView([48.8566, 2.3522], 12);

        // Ajout du calque visuel OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Personnalisation de l'icône du marqueur avec la couleur Orange du thème
        var customIcon = L.divIcon({
            className: 'custom-div-icon',
            html: "<div style='background-color:#FF7318; width:15px; height:15px; border-radius:50%; border:3px solid white; box-shadow: 0 0 5px rgba(0,0,0,0.5);'></div>",
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });

        // Ajout d'un faux utilisateur
        var marker = L.marker([48.8566, 2.3522], {icon: customIcon}).addTo(map);
        marker.bindPopup("<b style='color:#D81B60;'>Chef Senior</b><br>Spécialité : Pâtisserie<br><i>Recherche jeune apprenti</i>").openPopup();
    </script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
?>