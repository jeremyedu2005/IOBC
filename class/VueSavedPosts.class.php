<?php
/**
 * VueSavedPosts.class.php
 * Affiche les posts sauvegardés par l'utilisateur
 */
require_once(__DIR__ . '/../config.php');

class VueSavedPosts
{
    private $cnxDB;
    private $userId;
    private $saved_posts;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?login");
            exit;
        }
        
        $this->userId = $_SESSION['user_id'];
        
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->cnxDB = new PDO($dsn, DB_USER, DB_PASS);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->loadSavedPosts();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $this->saved_posts = [];
        }
    }

    private function loadSavedPosts()
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT p.id, p.content, p.created_at, p.author_id,
                       u.username, u.display_name, u.avatar_url,
                       COUNT(DISTINCT pl.user_id) as like_count,
                       COUNT(DISTINCT c.id) as comment_count,
                       GROUP_CONCAT(pm.media_url) as media_urls,
                       sp.created_at as saved_at
                FROM saved_posts sp
                INNER JOIN posts p ON sp.post_id = p.id
                LEFT JOIN users u ON p.author_id = u.id
                LEFT JOIN post_media pm ON p.id = pm.post_id
                LEFT JOIN post_likes pl ON p.id = pl.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                WHERE sp.user_id = :user_id
                GROUP BY p.id
                ORDER BY sp.created_at DESC
                LIMIT 50
            ");
            $stmt->execute([':user_id' => $this->userId]);
            $this->saved_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error loading saved posts: " . $e->getMessage());
            $this->saved_posts = [];
        }
    }

    public function __toString()
    {
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        require_once(__DIR__ . '/../lang/lang.php');
        $tr = loadLang($lang) ?? [];

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Security::generateCSRFToken()); ?>">
    <title>Saved Posts - claque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        h1, h2, h3, h4, h5, h6 { font-family: 'Baloo', cursive; }
        a { text-decoration: none !important; }
        
        .main-container { display: flex; }
        .content { flex: 1; }
        
        .page-header {
            background: linear-gradient(135deg, #FFE8CC 0%, #FFD4A3 100%);
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 25px;
        }
        
        .page-header h1 {
            font-size: 28px;
            margin: 0 0 8px 0;
            color: #333;
        }
        
        .page-header p {
            margin: 0;
            color: #666;
        }
        
        .posts-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
        
        .post-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s;
            cursor: pointer;
            display: flex;
            gap: 15px;
        }
        
        .post-card:hover {
            box-shadow: 0 4px 15px rgba(248, 96, 21, 0.15);
        }
        
        .post-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
            background: #f5f5f5;
            flex-shrink: 0;
        }
        
        .post-no-image {
            width: 200px;
            height: 150px;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .post-content {
            padding: 15px;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .post-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .post-author-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .post-author-info {
            flex: 1;
        }
        
        .post-author-name {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        
        .post-author-username {
            font-size: 12px;
            color: #999;
        }
        
        .post-text {
            color: #333;
            font-size: 14px;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .post-footer {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #999;
        }
        
        .post-stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .post-date {
            font-size: 11px;
            color: #ccc;
            margin-top: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state h2 {
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .post-card {
                flex-direction: column;
            }
            
            .post-image, .post-no-image {
                width: 100%;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <?php require_once(__DIR__ . '/../inc/header.inc.php'); ?>
    
    <div class="main-container">
        <?php require_once(__DIR__ . '/../inc/sidebar-left.inc.php'); ?>
        
        <main class="content" style="padding: 20px;">
            <!-- Header -->
            <div class="page-header">
                <h1>📌 Saved Posts</h1>
                <p><?= count($this->saved_posts) ?> recettes sauvegardées</p>
            </div>
            
            <!-- Posts List -->
            <?php if (count($this->saved_posts) > 0): ?>
                <div class="posts-list">
                    <?php foreach ($this->saved_posts as $post): ?>
                        <div class="post-card" onclick="window.location='?post=<?= $post['id'] ?>'">
                            <?php if ($post['media_urls']): ?>
                                <img src="<?= htmlspecialchars(explode(',', $post['media_urls'])[0]) ?>" 
                                     alt="Post" class="post-image">
                            <?php else: ?>
                                <div class="post-no-image">
                                    <i class="fas fa-image" style="font-size: 48px; color: #ddd;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="post-content">
                                <div>
                                    <div class="post-header">
                                        <img src="<?= htmlspecialchars($post['avatar_url'] ?: 'https://via.placeholder.com/36') ?>" 
                                             alt="<?= htmlspecialchars($post['username']) ?>" 
                                             class="post-author-avatar">
                                        <div class="post-author-info">
                                            <div class="post-author-name"><?= htmlspecialchars($post['display_name'] ?? $post['username']) ?></div>
                                            <div class="post-author-username">@<?= htmlspecialchars($post['username']) ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="post-text"><?= htmlspecialchars(substr($post['content'], 0, 150)) ?></div>
                                </div>
                                
                                <div>
                                    <div class="post-footer">
                                        <div class="post-stat">
                                            <i class="fas fa-heart"></i>
                                            <span><?= $post['like_count'] ?></span>
                                        </div>
                                        <div class="post-stat">
                                            <i class="fas fa-comments"></i>
                                            <span><?= $post['comment_count'] ?></span>
                                        </div>
                                    </div>
                                    <div class="post-date">Sauvegardé: <?= date('d M Y', strtotime($post['saved_at'])) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-bookmark"></i>
                    <h2>Aucun post sauvegardé</h2>
                    <p>Sauvegarde des recettes pour les retrouver plus tard</p>
                </div>
            <?php endif; ?>
        </main>
        
        <?php require_once(__DIR__ . '/../inc/sidebar-right.inc.php'); ?>
    </div>
    
    <?php require_once(__DIR__ . '/../inc/footer.inc.php'); ?>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
