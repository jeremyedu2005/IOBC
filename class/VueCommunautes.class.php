<?php
require_once(__DIR__ . '/../config.php');

class VueCommunautes
{
    private $cnxDB;
    private $userId;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?login");
            exit;
        }
        $this->userId = $_SESSION['user_id'];

        $dsn  = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
        $user = DB_USER;
        $pass = DB_PASS;

        try {
            $this->cnxDB = new PDO($dsn, $user, $pass);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function __toString()
    {
        $message = "";

        // 1. Gérer la création d'une communauté
        if (isset($_POST['create_community'])) {
            $message = $this->creerCommunaute();
        }

        // 2. Gérer le fait de rejoindre/quitter
        if (isset($_POST['join_community'])) {
            $this->rejoindreCommunaute($_POST['community_id'], 'member');
        }
        if (isset($_POST['leave_community'])) {
            $this->quitterCommunaute($_POST['community_id']);
        }

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

        // Récupérer l'utilisateur pour le header
        $displayName = htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['username'] ?? 'User');

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title><?= isset($tr['communities']) ? $tr['communities'] : 'Communautés' ?> - claque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/profil-public.css">
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
        .community-item {
            border: 1px solid #F86015;
            padding: 15px 25px;
            border-radius: 25px;
            background: white;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        a { text-decoration: none !important; }
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
                <h2 class="section-title"><i class="fas fa-users"></i> <?= isset($tr['communities']) ? $tr['communities'] : 'Communautés' ?></h2>
                <a href="#" class="see-all"><?= isset($tr['see_all']) ? $tr['see_all'] : 'See all' ?> <i class="fas fa-arrow-right"></i></a>
            </div>

            <?= $message ?>

            <!-- Create Community Form -->
            <section class="section-card" style="padding: 25px; background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px;">
                <h3 style="font-size: 18px; margin-bottom: 15px; color: #F86015;"><i class="fas fa-plus-circle"></i> Créer une communauté</h3>
                <form action="" method="post" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="text" name="name" placeholder="Nom (ex: FrenchBakery)" required style="padding: 10px; border-radius: 10px; border: 1px solid #ccc; flex: 1; min-width: 200px;">
                    <input type="text" name="description" placeholder="Courte description" required style="padding: 10px; border-radius: 10px; border: 1px solid #ccc; flex: 2; min-width: 250px;">
                    <button type="submit" name="create_community" style="background: #F86015; color: white; padding: 10px 20px; border: none; border-radius: 10px; cursor: pointer; font-weight: bold;">Créer</button>
                </form>
            </section>

            <!-- Communities List -->
            <section class="section-card" style="padding: 25px; background: white; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h3 style="font-size: 18px; margin-bottom: 20px; color: #F86015;"><i class="fas fa-fire"></i> Explorez les communautés</h3>
                <div class="community-list">
                    <?= $this->afficherListeCommunautes() ?>
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
            <a href="index.php?communautes" class="bottom-nav-item active">
                <i class="fas fa-users"></i>
                <span><?= isset($tr['communities']) ? $tr['communities'] : 'Communities' ?></span>
            </a>
            <a href="index.php?creer-post" class="bottom-nav-item">
                <i class="fas fa-plus-circle"></i>
                <span><?= isset($tr['new_post']) ? $tr['new_post'] : 'Post' ?></span>
            </a>
            <a href="index.php?profile" class="bottom-nav-item">
                <i class="fas fa-user"></i>
                <span><?= isset($tr['profile']) ? $tr['profile'] : 'Profile' ?></span>
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <?php require_once(__DIR__ . '/../inc/footer.inc.php'); ?>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    private function creerCommunaute()
    {
        $name = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['name']); 
        $description = htmlspecialchars(trim($_POST['description']));

        try {
            $stmt = $this->cnxDB->prepare("INSERT INTO communities (name, description_fr, creator_id) VALUES (:name, :desc, :uid)");
            $stmt->execute([':name' => $name, ':desc' => $description, ':uid' => $this->userId]);
            $comm_id = $this->cnxDB->lastInsertId();

            $this->rejoindreCommunaute($comm_id, 'admin');

            return "<p style='color: green; margin-bottom: 15px;'>✅ Communauté r/$name créée avec succès !</p>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { 
                return "<p style='color: red; margin-bottom: 15px;'>❌ Ce nom de communauté existe déjà.</p>";
            }
            return "<p style='color: red; margin-bottom: 15px;'>❌ Erreur : " . $e->getMessage() . "</p>";
        }
    }

    private function rejoindreCommunaute($commId, $role = 'member') {
        try {
            $stmt = $this->cnxDB->prepare("INSERT IGNORE INTO community_members (community_id, user_id, role) VALUES (?, ?, ?)");
            $stmt->execute([$commId, $this->userId, $role]);
        } catch (PDOException $e) { }
    }

    private function quitterCommunaute($commId) {
        try {
            $stmt = $this->cnxDB->prepare("DELETE FROM community_members WHERE community_id = ? AND user_id = ?");
            $stmt->execute([$commId, $this->userId]);
        } catch (PDOException $e) { }
    }

    private function afficherListeCommunautes()
    {
        $stmt = $this->cnxDB->prepare("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM community_members WHERE community_id = c.id) as count_members,
                   (SELECT COUNT(*) FROM community_members WHERE community_id = c.id AND user_id = :uid) as is_member
            FROM communities c
            ORDER BY count_members DESC
        ");
        $stmt->execute([':uid' => $this->userId]);
        $commus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $html = "";
        foreach ($commus as $c) {
            $isMember = (bool)$c['is_member'];
            
            // Si l'utilisateur est membre : on affiche le bouton Entrer + bouton Quitter
            if ($isMember) {
                $btnAction = '
                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="index.php?chat&id=' . $c['id'] . '" style="background:#4A90E2; color:white; padding:5px 15px; border-radius:15px; font-size:13px; font-weight:bold; display:flex; align-items:center;">
                        <i class="fas fa-comments" style="margin-right:5px;"></i> Entrer
                    </a>
                    <form method="post" action="" style="margin:0;">
                        <input type="hidden" name="community_id" value="' . $c['id'] . '">
                        <button type="submit" name="leave_community" style="background:#f5f5f5; color:#333; border:1px solid #ddd; padding:5px 15px; border-radius:15px; cursor:pointer;">Quitter</button>
                    </form>
                </div>';
            } else {
                // Sinon : on affiche seulement le bouton Rejoindre
                $btnAction = '
                <form method="post" action="" style="margin:0;">
                    <input type="hidden" name="community_id" value="' . $c['id'] . '">
                    <button type="submit" name="join_community" style="background:#FF7318; color:white; border:none; padding:5px 15px; border-radius:15px; cursor:pointer; font-weight:bold;">Rejoindre</button>
                </form>';
            }

            $desc = htmlspecialchars($c['description_fr'] ?? '');

            $html .= '
            <div class="community-item" style="border: 1px solid #FF7318; padding: 15px 25px; border-radius: 25px; background: white; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-weight: 800; font-size: 16px; color:#D81B60;">r/' . htmlspecialchars($c['name']) . '</span><br>
                    <span style="font-size: 12px; color: #666;">' . $desc . '</span><br>
                    <span style="font-size: 11px; color: #888; font-weight:bold;">' . $c['count_members'] . ' Membres</span>
                </div>
                ' . $btnAction . '
            </div>';
        }

        if (empty($commus)) {
            $html = "<p style='color: #888;'>Aucune communauté pour le moment. Soyez le premier à en créer une !</p>";
        }

        return $html;
    }
}
?>