<?php
/**
 * VueModifProfil.class.php
 * Formulaire de modification du profil personnel
 */
require_once(__DIR__ . '/../config.php');

class VueModifProfil
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
        $message = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
            $message = $this->modifierProfil();
        }

        return $this->afficherFormulaire($message);
    }

    private function modifierProfil()
    {
        $username     = trim($_POST['username'] ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $bio          = trim($_POST['bio'] ?? '');
        $location     = trim($_POST['location'] ?? '');
        $avatar_url   = trim($_POST['avatar_url'] ?? '');

        try {
            $stmt = $this->cnxDB->prepare("
                UPDATE users 
                SET username = :username, 
                    display_name = :display_name, 
                    bio = :bio, 
                    location = :location, 
                    avatar_url = :avatar_url 
                WHERE id = :id
            ");

            $stmt->execute([
                ':username'     => $username,
                ':display_name' => $display_name,
                ':bio'          => $bio,
                ':location'     => $location,
                ':avatar_url'   => $avatar_url,
                ':id'           => $this->userId
            ]);

            $_SESSION['username'] = $username;

            return "<p style='color:green; padding:10px; background:#d4edda; border-radius:8px;'>✅ Profil mis à jour avec succès !</p>";

        } catch (PDOException $e) {
            return "<p style='color:red; padding:10px; background:#f8d7da; border-radius:8px;'>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }

    private function afficherFormulaire($message)
    {
        $u = $this->user;

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
    <title><?= isset($tr['edit_profile']) ? $tr['edit_profile'] : 'Modifier mon profil' ?> - claque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/modif-profil.css">
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
                <h2 class="section-title"><i class="fas fa-user-edit"></i> <?= isset($tr['edit_profile']) ? $tr['edit_profile'] : 'Modifier mon profil' ?></h2>
            </div>

            <div class="profile-form-card" style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                <?= $message ?>

                <form action="" method="post">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-at"></i> <?= isset($tr['username']) ? $tr['username'] : 'Username' ?> :</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($u['username'] ?? '') ?>" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>

                    <div class="form-group">
                        <label for="display_name"><i class="fas fa-user"></i> <?= isset($tr['display_name']) ? $tr['display_name'] : 'Nom affiché' ?> :</label>
                        <input type="text" id="display_name" name="display_name" value="<?= htmlspecialchars($u['display_name'] ?? '') ?>" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>

                    <div class="form-group">
                        <label for="bio"><i class="fas fa-comment"></i> <?= isset($tr['bio']) ? $tr['bio'] : 'Bio' ?> :</label>
                        <textarea id="bio" name="bio" rows="4" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; font-family: 'Cabin', sans-serif;"><?= htmlspecialchars($u['bio'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="location"><i class="fas fa-map-marker-alt"></i> <?= isset($tr['location']) ? $tr['location'] : 'Localisation' ?> :</label>
                        <input type="text" id="location" name="location" value="<?= htmlspecialchars($u['location'] ?? '') ?>" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>

                    <div class="form-group">
                        <label for="avatar_url"><i class="fas fa-image"></i> <?= isset($tr['avatar_url']) ? $tr['avatar_url'] : 'URL Avatar' ?> :</label>
                        <input type="text" id="avatar_url" name="avatar_url" value="<?= htmlspecialchars($u['avatar_url'] ?? '') ?>" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>

                    <button type="submit" name="update_profile" style="background: #F86015; color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 14px;">
                        <i class="fas fa-save"></i> <?= isset($tr['save']) ? $tr['save'] : 'Enregistrer' ?>
                    </button>
                </form>
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