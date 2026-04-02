<?php
/**
 * VueProfilPersonnel.class.php
 * Affichage du profil personnel de l'utilisateur connecté
 */
require_once(__DIR__ . '/../config.php');

class VueProfilPersonnel
{
    private $cnxDB;
    private $userId;
    private $user;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?login");
            exit;
        }

        $this->userId = (int)$_SESSION['user_id'];

        // Connexion BDD
        $dsn  = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
        $user = DB_USER;
        $pass = DB_PASS;

        try {
            $this->cnxDB = new PDO($dsn, $user, $pass);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $this->cnxDB->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $this->userId]);
            $this->user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$this->user) {
                die("Utilisateur introuvable.");
            }

        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public function __toString()
    {
        return $this->afficherProfil();
    }

    private function afficherProfil()
    {
        $u = $this->user;
        $name = htmlspecialchars($u['display_name'] ?? $u['username']);

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
    <title><?= isset($tr['profile']) ? $tr['profile'] : 'Mon Profil' ?> - claque</title>
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
                <h2 class="section-title"><i class="fas fa-user-circle"></i> <?= isset($tr['my_profile']) ? $tr['my_profile'] : 'Mon Profil' ?></h2>
            </div>

            <!-- Profile Card -->
            <div class="profile-card" style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px;">
                <div style="display: flex; gap: 30px; align-items: flex-start;">
                    <div style="flex-shrink: 0;">
                        <img src="<?= htmlspecialchars($u['avatar_url'] ?? 'https://via.placeholder.com/150') ?>" 
                             alt="Photo de profil" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #F86015;">
                    </div>
                    
                    <div style="flex: 1;">
                        <h1 style="color: #F86015; margin: 0 0 10px 0; font-size: 28px;"><?= $name ?></h1>
                        <p style="color: #666; margin: 5px 0;"><i class="fas fa-at"></i> @<?= htmlspecialchars($u['username']) ?></p>
                        <p style="color: #666; margin: 5px 0;"><i class="fas fa-envelope"></i> <?= htmlspecialchars($u['email']) ?></p>
                        
                        <div style="margin-top: 15px;">
                            <p style="color: #333; font-weight: 600; margin: 0 0 10px 0;"><i class="fas fa-quote-left"></i> Bio</p>
                            <p style="color: #666; font-style: italic;">
                                <?= htmlspecialchars($u['bio'] ?? 'Aucune bio renseignée.') ?>
                            </p>
                        </div>

                        <div style="margin-top: 20px;">
                            <a href="index.php?modifprofil" class="btn btn-primary" style="background: #F86015; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block;">
                                <i class="fas fa-edit"></i> <?= isset($tr['edit_profile']) ? $tr['edit_profile'] : 'Modifier' ?>
                            </a>
                            <a href="index.php?logout" style="background: #D81B60; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; margin-left: 10px;">
                                <i class="fas fa-sign-out-alt"></i> <?= isset($tr['logout']) ? $tr['logout'] : 'Déconnexion' ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- My Recipes Section -->
            <div class="recipes-section" style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <h2 style="color: #F86015; margin-top: 0;"><i class="fas fa-utensils"></i> <?= isset($tr['my_recipes']) ? $tr['my_recipes'] : 'Mes Recettes' ?></h2>
                <p style="color:#888; font-style:italic;"><?= isset($tr['section_dev']) ? $tr['section_dev'] : 'Section en cours de développement' ?></p>
            </div>
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
            <a href="index.php?profile" class="bottom-nav-item active">
                <i class="fas fa-user"></i>
                <span><?= isset($tr['profile']) ? $tr['profile'] : 'Profile' ?></span>
            </a>
            <a href="index.php?communautes" class="bottom-nav-item">
                <i class="fas fa-users"></i>
                <span><?= isset($tr['communities']) ? $tr['communities'] : 'Communities' ?></span>
            </a>
            <a href="index.php?logout" class="bottom-nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span><?= isset($tr['logout']) ? $tr['logout'] : 'Logout' ?></span>
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
}
?>