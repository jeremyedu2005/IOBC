<?php
require_once(__DIR__ . '/../config.php');

class VueCreerPost
{
    private $cnxDB;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?login");
            exit;
        }

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
            <title><?= isset($tr['new_post']) ? $tr['new_post'] : 'Créer un Post' ?> KAMI</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="css/style.css">
            <link rel="stylesheet" href="css/responsive.css">
            <link rel="stylesheet" href="css/creer-post.css">
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
                        <h2 class="section-title"><i class="fas fa-paper-plane"></i> <?= isset($tr['new_post']) ? $tr['new_post'] : 'Créer un Post' ?></h2>
                    </div>

                    <div class="post-card" style="background: white; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <?php 
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_post'])) {
                            echo $this->traiterPost();
                        }
                        ?>

                        <?= $this->afficherFormulaire() ?>
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
                    <a href="index.php?communautes" class="bottom-nav-item">
                        <i class="fas fa-users"></i>
                        <span><?= isset($tr['communities']) ? $tr['communities'] : 'Communities' ?></span>
                    </a>
                    <a href="index.php?creer-post" class="bottom-nav-item active">
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

    private function afficherFormulaire()
    {
        return '
        <form method="post" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
            <!-- Content Textarea -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="content" style="font-weight: 600; color: #333; font-family: Baloo, cursive; font-size: 16px;">
                    Votre recette ou message
                </label>
                <textarea 
                    id="content" 
                    name="content" 
                    rows="8" 
                    placeholder="Partagez votre passion culinaire... 🍳" 
                    required
                    style="
                        padding: 15px;
                        border: 2px solid #eee;
                        border-radius: 12px;
                        font-family: Cabin, sans-serif;
                        font-size: 15px;
                        resize: vertical;
                        transition: all 0.3s;
                    "
                    onfocus="this.style.borderColor = \"#F86015\"; this.style.boxShadow = \"0 0 8px rgba(248, 96, 21, 0.2)\";"
                    onblur="this.style.borderColor = \"#eee\"; this.style.boxShadow = \"none\";"
                ></textarea>
            </div>

            <!-- Image Upload -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="media" style="font-weight: 600; color: #333; font-family: Baloo, cursive; font-size: 16px;">
                    <i class="fas fa-camera" style="color: #F86015; margin-right: 8px;"></i>Ajouter une photo
                </label>
                <div style="
                    border: 2px dashed #F86015;
                    border-radius: 12px;
                    padding: 30px;
                    text-align: center;
                    background: rgba(248, 96, 21, 0.02);
                    cursor: pointer;
                    transition: all 0.3s;
                " id="dragdrop" ondragover="this.style.background=\'rgba(248, 96, 21, 0.1)\'; return false" ondragleave="this.style.background=\'rgba(248, 96, 21, 0.02)\'; return false">
                    <input 
                        type="file" 
                        id="media" 
                        name="media" 
                        accept="image/png, image/jpeg, image/jpg, image/gif"
                        style="display: none;"
                    >
                    <p style="margin: 0; color: #F86015; font-weight: 600;">
                        <i class="fas fa-image" style="font-size: 24px; margin-bottom: 10px;"></i>
                        <br>Cliquez ou glissez une image ici
                    </p>
                    <p style="margin: 5px 0 0 0; color: #999; font-size: 12px;">PNG, JPG ou GIF (max 5 MB)</p>
                    <div id="preview" style="margin-top: 15px; display: none;">
                        <img id="previewImg" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                    </div>
                </div>
            </div>

            <!-- Tags -->
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <label for="tags" style="font-weight: 600; color: #333; font-family: Baloo, cursive; font-size: 16px;">
                    <i class="fas fa-tags" style="color: #F86015; margin-right: 8px;"></i>Tags (optionnel)
                </label>
                <input 
                    type="text" 
                    id="tags" 
                    name="tags" 
                    placeholder="Ex: vegan, dessert, chocolat, français"
                    style="
                        padding: 12px 15px;
                        border: 2px solid #eee;
                        border-radius: 8px;
                        font-family: Cabin, sans-serif;
                        font-size: 14px;
                        transition: all 0.3s;
                    "
                    onfocus="this.style.borderColor = \"#F86015\"; this.style.boxShadow = \"0 0 8px rgba(248, 96, 21, 0.2)\";"
                    onblur="this.style.borderColor = \"#eee\"; this.style.boxShadow = \"none\";"
                >
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                name="submit_post"
                style="
                    background: linear-gradient(135deg, #F86015, #e55a0f);
                    color: white;
                    border: none;
                    padding: 14px 30px;
                    border-radius: 8px;
                    font-family: Baloo, cursive;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                    transition: all 0.3s;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 10px;
                    margin-top: 10px;
                "
                onmouseover="this.style.transform = \"translateY(-2px)\"; this.style.boxShadow = \"0 8px 16px rgba(248, 96, 21, 0.3)\";"
                onmouseout="this.style.transform = \"translateY(0)\"; this.style.boxShadow = \"none\";"
            >
                <i class="fas fa-paper-plane"></i> Publier sur claque
            </button>
        </form>

        <script>
            const dragdrop = document.getElementById("dragdrop");
            const fileInput = document.getElementById("media");
            const preview = document.getElementById("preview");
            const previewImg = document.getElementById("previewImg");

            dragdrop.addEventListener("click", () => fileInput.click());

            dragdrop.addEventListener("drop", (e) => {
                e.preventDefault();
                if (e.dataTransfer.files[0]) {
                    fileInput.files = e.dataTransfer.files;
                    showPreview(e.dataTransfer.files[0]);
                }
            });

            fileInput.addEventListener("change", (e) => {
                if (e.target.files[0]) {
                    showPreview(e.target.files[0]);
                }
            });

            function showPreview(file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImg.src = e.target.result;
                    preview.style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        </script>
        ';
    }

    private function traiterPost()
    {
        $content = htmlspecialchars(trim($_POST['content'] ?? ''));
        if (empty($content)) {
            return "<div style='background: #ffe6e6; color: #c00; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c00;'><i class='fas fa-exclamation-circle'></i> Le contenu du post ne peut pas être vide</div>";
        }

        $author_id = $_SESSION['user_id'];
        
        try {
            // Define upload directory (absolute path)
            $uploadDir = __DIR__ . '/../uploads/';
            
            // Créer le dossier uploads s'il n'existe pas
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Vérifier que le directory est writable
            if (!is_writable($uploadDir)) {
                throw new Exception('Le répertoire d\'upload n\'a pas les permissions d\'accès en écriture');
            }

            // Début d'une transaction
            $this->cnxDB->beginTransaction();

            // 1. Insérer le texte du post dans la table 'posts'
            $stmt = $this->cnxDB->prepare("INSERT INTO posts (author_id, content, created_at) VALUES (:author_id, :content, NOW())");
            $stmt->execute([
                ':author_id' => $author_id,
                ':content' => $content
            ]);
            
            $post_id = $this->cnxDB->lastInsertId();

            // 2. Gestion de l'image (Upload physique et BDD)
            if (isset($_FILES['media']) && $_FILES['media']['error'] === 0) {
                $maxSize = 5 * 1024 * 1024; // 5 MB
                
                // Vérifier la taille
                if ($_FILES['media']['size'] > $maxSize) {
                    throw new Exception('Fichier trop volumineux (max 5 MB)');
                }

                // Vérifier le type
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!in_array($_FILES['media']['type'], $allowed)) {
                    throw new Exception('Format de fichier non accepté');
                }

                // Créer un nom unique
                $extension = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                $newFilename = "post_" . $post_id . "_" . time() . "." . $extension;
                $destination = $uploadDir . $newFilename;

                // Déplacer le fichier
                if (!move_uploaded_file($_FILES['media']['tmp_name'], $destination)) {
                    throw new Exception('Erreur lors de l\'upload du fichier: ' . error_get_last()['message']);
                }

                // Insérer le lien dans post_media (store relative path for serving in browser)
                $relativePath = 'uploads/' . $newFilename;
                $stmtMedia = $this->cnxDB->prepare("INSERT INTO post_media (post_id, media_url, media_type, order_index) VALUES (:post_id, :url, 'image', 0)");
                $stmtMedia->execute([
                    ':post_id' => $post_id,
                    ':url' => $relativePath
                ]);
            }

            $this->cnxDB->commit();
            return "<div style='background: #e6ffe6; color: #090; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #090;'><i class='fas fa-check-circle'></i> Post publié avec succès ! ✨</div>";

        } catch (Exception $e) {
            if ($this->cnxDB->inTransaction()) {
                $this->cnxDB->rollBack();
            }
            return "<div style='background: #ffe6e6; color: #c00; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c00;'><i class='fas fa-times-circle'></i> Erreur : " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>