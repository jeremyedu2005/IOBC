<?php
// Sidebar droite - Communautés tendance - Composant réutilisable
// Par défaut, utilise des données de test si aucune variable n'est passée

if (!isset($communautes)) {
    $communautes = [
        ['nom' => 'AlbanianKitchen', 'membres' => '57K'],
        ['nom' => 'VietnameseStreetFood', 'membres' => '142K'],
        ['nom' => 'FrenchPastry', 'membres' => '98K'],
        ['nom' => 'MediterraneanCuisine', 'membres' => '76K'],
        ['nom' => 'VeganRecipes', 'membres' => '130K'],
    ];
}
?>

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
