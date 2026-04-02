<?php
class VuePostDetail
{
    private $post_id;
    private $db;

    public function __construct($post_id = null)
    {
        $this->post_id = $post_id;
        require_once(__DIR__ . '/../inc/poo.inc.php');
        $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function __toString()
    {
        // Charger les traductions
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        require_once(__DIR__ . '/../lang/lang.php');
        $tr = loadLang($lang) ?? [];

        if (!$this->post_id) {
            return '<p style="text-align:center; padding:50px; color:#999;">Post not found</p>';
        }

        // Récupérer le post avec les infos de l'auteur
        $stmt = $this->db->prepare('
            SELECT p.id, p.content, p.created_at, p.author_id,
                   u.username, u.display_name, u.avatar_url, u.bio, u.location, u.age_group,
                   COUNT(DISTINCT pl.user_id) as like_count,
                   COUNT(DISTINCT c.id) as comment_count,
                   GROUP_CONCAT(pm.media_url) as media_urls
            FROM posts p
            LEFT JOIN users u ON p.author_id = u.id
            LEFT JOIN post_likes pl ON p.id = pl.post_id
            LEFT JOIN comments c ON p.id = c.post_id
            LEFT JOIN post_media pm ON p.id = pm.post_id
            WHERE p.id = :post_id
            GROUP BY p.id
            LIMIT 1
        ');
        $stmt->execute([':post_id' => $this->post_id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            return '<p style="text-align:center; padding:50px; color:#999;">Post not found</p>';
        }

        // Parser les tags (stockés en JSON dans le contenu ou comme métadonnées)
        $tags = $this->extractTags($post['content']);

        // Récupérer les commentaires
        $stmtComments = $this->db->prepare('
            SELECT c.id, c.content, c.created_at, c.author_id,
                   u.username, u.display_name, u.avatar_url
            FROM comments c
            LEFT JOIN users u ON c.author_id = u.id
            WHERE c.post_id = :post_id AND c.parent_comment_id IS NULL
            ORDER BY c.created_at DESC
        ');
        $stmtComments->execute([':post_id' => $this->post_id]);
        $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>Post - claque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css"
    <style>
        body {
            background-color: #FEF5F1;
            font-family: 'Cabin', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Baloo', cursive;
        }
        .post-detail-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .post-header {
            padding: 20px;
            border-bottom: 1px solid #E5D7CC;
            display: flex;
            gap: 15px;
        }
        .post-author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #F86015;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 18px;
            flex-shrink: 0;
        }
        .post-author-info {
            flex: 1;
        }
        .post-author-name {
            font-weight: 600;
            color: #634444;
        }
        .post-author-meta {
            font-size: 12px;
            color: #999;
        }
        .post-content {
            padding: 20px;
        }
        .post-title {
            font-size: 24px;
            font-weight: 700;
            color: #F86015;
            margin-bottom: 15px;
            font-family: 'Baloo', cursive;
        }
        .post-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background: #FEF5F1;
            border-radius: 12px;
        }
        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        .stat-value {
            font-size: 14px;
            font-weight: 600;
            color: #F86015;
        }
        .stat-label {
            font-size: 11px;
            color: #999;
            text-transform: uppercase;
        }
        .post-body {
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            margin-bottom: 20px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .post-media {
            margin: 20px 0;
            display: grid;
            gap: 10px;
        }
        .post-media-item {
            border-radius: 12px;
            max-width: 100%;
            max-height: 400px;
            object-fit: cover;
        }
        .post-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #E5D7CC;
        }
        .tag {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #FFE8CC;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: #F86015;
        }
        .tag-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 50%;
            font-size: 12px;
            flex-shrink: 0;
        }
        .post-actions {
            padding: 15px 20px;
            border-top: 1px solid #E5D7CC;
            display: flex;
            gap: 15px;
        }
        .action-btn {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #F86015;
            background: white;
            color: #F86015;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .action-btn:hover {
            background: #F86015;
            color: white;
        }
        .comments-section {
            padding: 20px;
            border-top: 1px solid #E5D7CC;
        }
        .comments-title {
            font-size: 16px;
            font-weight: 700;
            color: #634444;
            margin-bottom: 20px;
            font-family: 'Baloo', cursive;
        }
        .comment {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        .comment:last-child {
            border-bottom: none;
        }
        .comment-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #F86015;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            flex-shrink: 0;
        }
        .comment-body {
            flex: 1;
        }
        .comment-author {
            font-weight: 600;
            color: #634444;
            font-size: 13px;
        }
        .comment-content {
            font-size: 13px;
            color: #333;
            margin-top: 5px;
            line-height: 1.4;
        }
        .comment-time {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php require_once(__DIR__ . '/../inc/header.inc.php'); ?>

    <div class="main-container">
        <?php require_once(__DIR__ . '/../inc/sidebar-left.inc.php'); ?>

        <main class="content">
            <div class="post-detail-container">
                <!-- Post Header -->
                <div class="post-header">
                    <div class="post-author-avatar">
                        <?= strtoupper(substr($post['username'], 0, 1)) ?>
                    </div>
                    <div class="post-author-info">
                        <div class="post-author-name">
                            <a href="index.php?profile&user=<?= $post['author_id'] ?>" style="text-decoration: none; color: inherit;">
                                <?= htmlspecialchars($post['display_name'] ?? $post['username']) ?>
                            </a>
                        </div>
                        <div class="post-author-meta">
                            <strong><?= htmlspecialchars($post['age_group'] ?? 'User') ?></strong> • 
                            <?= htmlspecialchars($post['location'] ?? 'Unknown') ?> • 
                            <?= date('d M Y', strtotime($post['created_at'])) ?>
                        </div>
                    </div>
                </div>

                <!-- Post Content -->
                <div class="post-content">
                    <h2 class="post-title"><?= htmlspecialchars(substr($post['content'], 0, 50)) ?></h2>

                    <!-- Stats -->
                    <div class="post-stats">
                        <div class="stat-item">
                            <span class="stat-value"><?= intval($post['like_count'] ?? 0) ?></span>
                            <span class="stat-label">Likes</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value"><?= intval($post['comment_count'] ?? 0) ?></span>
                            <span class="stat-label">Comments</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-value">15 min</span>
                            <span class="stat-label">Prep Time</span>
                        </div>
                    </div>

                    <!-- Post Body -->
                    <div class="post-body">
                        <?= htmlspecialchars($post['content']) ?>
                    </div>

                    <!-- Media Gallery -->
                    <?php if ($post['media_urls']): ?>
                        <div class="post-media">
                            <?php 
                            $medias = array_filter(explode(',', $post['media_urls']));
                            foreach ($medias as $media): 
                            ?>
                                <img src="<?= htmlspecialchars($media) ?>" alt="Post media" class="post-media-item">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Tags -->
                    <?php if (!empty($tags)): ?>
                        <div class="post-tags">
                            <?php foreach ($tags as $tag): ?>
                                <div class="tag">
                                    <div class="tag-icon">
                                        <?= $this->getTagIcon($tag) ?>
                                    </div>
                                    <span><?= htmlspecialchars($tag) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="post-actions">
                    <button class="action-btn">
                        <i class="fas fa-heart"></i> Like
                    </button>
                    <button class="action-btn">
                        <i class="fas fa-comment"></i> Comment
                    </button>
                    <button class="action-btn">
                        <i class="fas fa-share"></i> Share
                    </button>
                </div>

                <!-- Comments -->
                <?php if (!empty($comments)): ?>
                    <div class="comments-section">
                        <h3 class="comments-title">💬 Comments (<?= count($comments) ?>)</h3>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="comment-avatar">
                                    <?= strtoupper(substr($comment['username'], 0, 1)) ?>
                                </div>
                                <div class="comment-body">
                                    <div class="comment-author">
                                        <?= htmlspecialchars($comment['display_name'] ?? $comment['username']) ?>
                                    </div>
                                    <div class="comment-content">
                                        <?= htmlspecialchars($comment['content']) ?>
                                    </div>
                                    <div class="comment-time">
                                        <?= date('d M Y à H:i', strtotime($comment['created_at'])) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <?php require_once(__DIR__ . '/../inc/sidebar-right.inc.php'); ?>
    </div>

    <?php require_once(__DIR__ . '/../inc/footer.inc.php'); ?>
</body>
</html>
        <?php
        return ob_get_clean();
    }

    private function extractTags($content)
    {
        // Tags reconnus avec leurs icônes
        $knownTags = [
            'Spicy & Savory' => '🌶️',
            'Steamed' => '💨',
            'Family Recipe' => '👨‍👩‍👧',
            'High Protein' => '💪',
            'Healthy' => '🥗',
            'Quick Dinner' => '⚡',
            'Vegan' => '🌿',
            'Gluten Free' => '🚫',
            'Dairy Free' => '🥛',
            'Easy' => '✨',
            'Medium' => '⭐',
            'Hard' => '🔥',
        ];

        $tags = [];
        foreach ($knownTags as $tag => $icon) {
            if (stripos($content, $tag) !== false) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    private function getTagIcon($tag)
    {
        $tagIcons = [
            'Spicy & Savory' => '🌶️',
            'Steamed' => '💨',
            'Family Recipe' => '👨‍👩‍👧',
            'High Protein' => '💪',
            'Healthy' => '🥗',
            'Quick Dinner' => '⚡',
            'Vegan' => '🌿',
            'Gluten Free' => '🚫',
            'Dairy Free' => '🥛',
            'Easy' => '✨',
            'Medium' => '⭐',
            'Hard' => '🔥',
        ];

        return $tagIcons[$tag] ?? '🏷️';
    }
}
?>
