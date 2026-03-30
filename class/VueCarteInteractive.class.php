<?php
class VueCarteInteractive
{
    public function __construct()
    {
        // On n'a pas besoin de la BDD pour juste afficher la carte (pour le moment)
    }

    public function __toString()
    {
        // On importe les bibliothèques Leaflet (CSS et JS) et on affiche la carte
        return '
        <div class="map-container" style="max-width: 800px; margin: 20px auto; text-align: center; font-family: Arial;">
            <h2>Carte des cuisiniers (Aperçu)</h2>
            <p>Ici, l\'équipe UX/UI pourra placer les marqueurs des utilisateurs.</p>
            
            <div id="map" style="height: 500px; width: 100%; border-radius: 10px; border: 2px solid #ccc;"></div>
        </div>

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

        <script>
            // Initialisation de la carte (Centrée sur Paris par défaut, zoom 12)
            var map = L.map("map").setView([48.8566, 2.3522], 12);

            // Ajout du fond de carte OpenStreetMap
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution: "&copy; <a href=\'https://www.openstreetmap.org/copyright\'>OpenStreetMap</a> contributors"
            }).addTo(map);

            // Exemple de marqueur factice pour montrer à l\'équipe UX/UI
            var marker = L.marker([48.8566, 2.3522]).addTo(map);
            marker.bindPopup("<b>Un senior cuisinier !</b><br>Spécialité : Pâtisserie Française.").openPopup();
        </script>
        ';
    }
}
?>