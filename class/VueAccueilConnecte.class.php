<?php
/**
 * VueAccueilConnecte.class.php
 * Page d'accueil pour utilisateurs connectés
 */
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/VueCommentsWidget.class.php');

class VueAccueilConnecte
{
    private $cnxDB;
    private $userId;
    private $user;
 
    public function __construct()
    {
        // Vérification de la session utilisateur
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?login");
            exit;
        }
        $this->userId = (int)$_SESSION['user_id'];
        
        // Connexion à la base de données
        $dsn  = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
        $user = DB_USER;
        $pass = DB_PASS;
 
        try {
            $this->cnxDB = new PDO($dsn, $user, $pass);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Récupération des informations de l'utilisateur connecté
            $stmt = $this->cnxDB->prepare("SELECT id, username, display_name, avatar_url FROM users WHERE id = :id");
            $stmt->execute([':id' => $this->userId]);
            $this->user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$this->user) {
                session_destroy();
                header("Location: index.php?login");
                exit;
            }
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données.");
        }
    }
 
    public function __toString() { return $this->afficherContenu(); }

    /**
     * Récupère les publications tendance avec stats
     */
    private function getTrendingPosts($limit = 10) {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT 
                    p.id,
                    p.content,
                    p.created_at,
                    u.id as author_id,
                    u.username,
                    u.display_name,
                    pm.media_url,
                    (SELECT COUNT(*) FROM post_likes WHERE post_id = p.id) as like_count,
                    (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count,
                    (SELECT COUNT(*) FROM saved_posts WHERE post_id = p.id) as bookmark_count,
                    CASE WHEN EXISTS(SELECT 1 FROM post_likes WHERE post_id = p.id AND user_id = :user_id) THEN 1 ELSE 0 END as user_liked,
                    CASE WHEN EXISTS(SELECT 1 FROM saved_posts WHERE post_id = p.id AND user_id = :user_id) THEN 1 ELSE 0 END as user_bookmarked
                FROM posts p 
                JOIN users u ON p.author_id = u.id
                LEFT JOIN post_media pm ON p.id = pm.post_id AND pm.order_index = 0
                ORDER BY p.created_at DESC 
                LIMIT :limit
            ");
            $stmt->bindValue(':user_id', $this->userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
 
    private function afficherContenu()
    {
        $posts = $this->getTrendingPosts();
        $displayName = htmlspecialchars($this->user['display_name'] ?? $this->user['username']);
        
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
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Security::generateCSRFToken()); ?>">
    <title>claque - Accueil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <script src="js/comments.js" defer></script>
    <script src="js/following.js" defer></script>
    <script src="js/messages.js" defer></script>
    <style>
        h1, h2, h3, h4, h5, h6 { font-family: 'Baloo', cursive; }
        a { text-decoration: none !important; }
        .welcome-banner {
            background: linear-gradient(135deg, #F86015, #078CDF);
            border-radius: 15px;
            padding: 30px;
            color: white;
            margin-bottom: 25px;
        }
        .welcome-banner h1 { font-size: 28px; margin: 0 0 10px 0; }
        .welcome-banner p { margin: 0; opacity: 0.95; }
        .post-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .post-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .post-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .post-body {
            padding: 15px;
        }
        .post-title { font-weight: 600; color: #F86015; margin: 0 0 8px 0; }
        .post-author { font-size: 13px; color: #999; margin-bottom: 10px; }
        .post-stats {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <?php require_once(__DIR__ . '/../inc/header.inc.php'); ?>

    <div class="main-container">
        <?php require_once(__DIR__ . '/../inc/sidebar-left.inc.php'); ?>

        <main class="content">
            <div class="welcome-banner">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1>👋 <?= $displayName ?></h1>
                        <p>Bienvenue ! Découvrez les recettes tendance 🍳</p>
                    </div>
                    <a href="index.php?creer-post" style="background: white; color: #F86015; padding: 12px 24px; 
                                                          border-radius: 8px; text-decoration: none; font-weight: bold; 
                                                          border: 2px solid white; transition: all 0.3s;">
                        ✨ Create Post
                    </a>
                </div>
            </div>

            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-fire"></i> Recettes Tendance</h2>
            </div>

            <?php if (empty($posts)): ?>
                <div class="post-card">
                    <div style="text-align: center; padding: 50px 20px; color: #999;">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 20px;"></i>
                        <p>Aucun post pour l'instant</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <!-- Post Header -->
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #eee;">
                            <div style="display: flex; align-items: center; gap: 12px; cursor: pointer;" onclick="window.location='index.php?profile&username=<?= urlencode($post['username']) ?>'">
                                <img src="https://i.pravatar.cc/80?u=<?= $post['username'] ?>" style="width: 40px; height: 40px; border-radius: 50%;">
                                <div>
                                    <p style="font-weight: 600; margin: 0;"><?= htmlspecialchars($post['display_name'] ?? $post['username']) ?></p>
                                    <p style="font-size: 12px; color: #999; margin: 0;">@<?= htmlspecialchars($post['username']) ?></p>
                                </div>
                            </div>
                            <span style="font-size: 12px; color: #999;"><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></span>
                        </div>

                        <!-- Post Image -->
                        <?php if ($post['media_url']): ?>
                            <img src="<?= htmlspecialchars($post['media_url']) ?>" class="post-img" onerror="this.src='https://via.placeholder.com/500x300'">
                        <?php endif; ?>

                        <!-- Post Content -->
                        <div style="padding: 15px;">
                            <p style="margin: 0 0 15px 0; color: #333; line-height: 1.5;">
                                <?= htmlspecialchars($post['content']) ?>
                            </p>

                            <!-- Post Stats -->
                            <div class="post-stats" style="display: flex; gap: 20px; padding: 15px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee; font-size: 13px; color: #666;">
                                <span><strong><?= $post['like_count'] ?></strong> Likes</span>
                                <span><strong><?= $post['comment_count'] ?></strong> Comments</span>
                                <span><strong><?= $post['bookmark_count'] ?></strong> Saved</span>
                            </div>

                            <!-- Post Actions -->
                            <div style="display: flex; justify-content: space-around; padding: 15px 0;">
                                <button onclick="toggleLike(<?= $post['id'] ?>, this)" 
                                    style="background: none; border: none; cursor: pointer; color: <?= $post['user_liked'] ? '#F86015' : '#999' ?>; font-size: 14px; transition: all 0.3s;">
                                    <i class="fas fa-heart" style="margin-right: 5px;"></i> Like
                                </button>
                                <button onclick="openComments(<?= $post['id'] ?>)" 
                                    style="background: none; border: none; cursor: pointer; color: #999; font-size: 14px; transition: all 0.3s;">
                                    <i class="fas fa-comment" style="margin-right: 5px;"></i> Comment
                                </button>
                                <button onclick="toggleBookmark(<?= $post['id'] ?>, this)" 
                                    style="background: none; border: none; cursor: pointer; color: <?= $post['user_bookmarked'] ? '#F86015' : '#999' ?>; font-size: 14px; transition: all 0.3s;">
                                    <i class="fas fa-bookmark" style="margin-right: 5px;"></i> Save
                                </button>
                                <button onclick="sharePost(<?= $post['id'] ?>)" 
                                    style="background: none; border: none; cursor: pointer; color: #999; font-size: 14px; transition: all 0.3s;">
                                    <i class="fas fa-share" style="margin-right: 5px;"></i> Share
                                </button>
                            </div>

                            <!-- Comments Section (Expandable) -->
                            <div id="comments-section-<?= $post['id'] ?>" style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px; display: none;">
                                <?php
                                    require_once(__DIR__ . '/../config.php');
                                    $commentsWidget = new VueCommentsWidget($this->cnxDB, $this->userId);
                                    echo $commentsWidget->afficherCommentaires($post['id']);
                                ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>

        <?php require_once(__DIR__ . '/../inc/sidebar-right.inc.php'); ?>
    </div>

    <nav class="bottom-nav">
        <div class="bottom-nav-items">
            <a href="index.php?accueil" class="bottom-nav-item active">
                <i class="fas fa-home"></i>
                <span><?= $tr['home'] ?? 'Home' ?></span>
            </a>
            <a href="index.php?agenda" class="bottom-nav-item">
                <i class="fas fa-calendar"></i>
                <span><?= $tr['agenda'] ?? 'Agenda' ?></span>
            </a>
            <a href="index.php?carte" class="bottom-nav-item">
                <i class="fas fa-map-marked-alt"></i>
                <span><?= $tr['map'] ?? 'Map' ?></span>
            </a>
            <a href="index.php?profile" class="bottom-nav-item">
                <i class="fas fa-user"></i>
                <span><?= $tr['profile'] ?? 'Profile' ?></span>
            </a>
        </div>
    </nav>

    <?php require_once(__DIR__ . '/../inc/footer.inc.php'); ?>

    <script>
        function toggleLike(postId, button) {
            fetch('api.php?action=toggle_like&post_id=' + postId, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    button.style.color = data.liked ? '#F86015' : '#999';
                    location.reload();
                }
            })
            .catch(e => console.error('Error:', e));
        }

        function toggleBookmark(postId, button) {
            fetch('api.php?action=toggle_bookmark&post_id=' + postId, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'}
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    button.style.color = data.bookmarked ? '#F86015' : '#999';
                    location.reload();
                }
            })
            .catch(e => console.error('Error:', e));
        }

        function openComments(postId) {
            const section = document.getElementById('comments-section-' + postId);
            if (section.style.display === 'none') {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        }

        function sharePost(postId) {
            if (navigator.share) {
                navigator.share({
                    title: 'claque Recipe',
                    text: 'Check out this recipe on claque!',
                    url: window.location.href
                });
            } else {
                alert('Partage non supporté sur ce navigateur');
            }
        }
    </script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
?>