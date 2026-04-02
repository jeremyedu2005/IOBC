<?php
/**
 * VueProfilPublic.class.php
 * Affiche le profil public d'un utilisateur avec ses posts (style Instagram)
 */
require_once(__DIR__ . '/../config.php');

class VueProfilPublic
{
    private $cnxDB;
    private $user;
    private $user_id;
    private $posts;
    private $followers_count;
    private $friends_count;
    private $mentors;
    private $communities;
    private $isOwnProfile = false;

    public function __construct($username = null)
    {
        if (!$username && isset($_GET['username'])) {
            $username = $_GET['username'];
        }
        if (!$username && isset($_GET['user'])) {
            $username = $_GET['user'];
        }

        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->cnxDB = new PDO($dsn, DB_USER, DB_PASS);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            if ($username) {
                // Récupérer l'utilisateur
                $stmt = $this->cnxDB->prepare("
                    SELECT id, username, display_name, avatar_url, bio, age_group, location 
                    FROM users WHERE username = :username LIMIT 1
                ");
                $stmt->execute([':username' => $username]);
                $this->user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($this->user) {
                    $this->user_id = $this->user['id'];
                    // Vérifier si c'est le profil de l'utilisateur connecté
                    $this->isOwnProfile = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $this->user_id);
                    $this->loadUserPosts();
                    $this->loadStats();
                    $this->loadMentors();
                    $this->loadCommunities();
                }
            }
        } catch (PDOException $e) {
            error_log("Database error in VueProfilPublic: " . $e->getMessage());
        }
    }

    private function loadUserPosts()
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT p.id, p.content, p.created_at, p.author_id,
                       COUNT(DISTINCT pl.user_id) as like_count,
                       COUNT(DISTINCT c.id) as comment_count,
                       GROUP_CONCAT(pm.media_url) as media_urls
                FROM posts p
                LEFT JOIN post_media pm ON p.id = pm.post_id
                LEFT JOIN post_likes pl ON p.id = pl.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                WHERE p.author_id = :user_id
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT 20
            ");
            $stmt->execute([':user_id' => $this->user_id]);
            $this->posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->posts = [];
        }
    }

    private function loadStats()
    {
        try {
            // Followers count
            $stmt = $this->cnxDB->prepare("
                SELECT COUNT(*) as count FROM follows WHERE following_id = :user_id
            ");
            $stmt->execute([':user_id' => $this->user_id]);
            $this->followers_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

            // Friends count (suivis)
            $stmt = $this->cnxDB->prepare("
                SELECT COUNT(*) as count FROM follows WHERE follower_id = :user_id
            ");
            $stmt->execute([':user_id' => $this->user_id]);
            $this->friends_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (PDOException $e) {
            $this->followers_count = 0;
            $this->friends_count = 0;
        }
    }

    private function loadMentors()
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT u.id, u.username, u.display_name, u.avatar_url, u.age_group
                FROM user_mentors um
                JOIN users u ON um.mentor_id = u.id
                WHERE um.mentee_id = :user_id
                LIMIT 5
            ");
            $stmt->execute([':user_id' => $this->user_id]);
            $this->mentors = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (PDOException $e) {
            $this->mentors = [];
        }
    }

    private function loadCommunities()
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT c.id, c.name, c.cover_url,
                       COUNT(DISTINCT cm.user_id) as member_count
                FROM community_members cm
                JOIN communities c ON cm.community_id = c.id
                WHERE cm.user_id = :user_id
                GROUP BY c.id
                LIMIT 8
            ");
            $stmt->execute([':user_id' => $this->user_id]);
            $this->communities = $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
        } catch (PDOException $e) {
            $this->communities = [];
        }
    }

    public function __toString()
    {
        if (!$this->user) {
            return "<div style='padding: 50px; text-align: center; color: #c00;'><i class='fas fa-user-slash'></i> Utilisateur non trouvé</div>";
        }

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
    <title><?= htmlspecialchars($this->user['display_name'] ?? $this->user['username']) ?> - claque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        body { background-color: #FEF5F1; font-family: 'Cabin', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Baloo', cursive; }
        a { text-decoration: none !important; }

        .profile-header {
            background: white;
            padding: 30px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .profile-hero {
            display: flex;
            gap: 30px;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #F86015;
            flex-shrink: 0;
            box-shadow: 0 4px 15px rgba(248, 96, 21, 0.2);
        }

        .profile-info h1 {
            font-size: 32px;
            margin: 0 0 8px 0;
            color: #333;
        }

        .profile-meta {
            display: flex;
            gap: 15px;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .username {
            color: #999;
            font-size: 14px;
        }

        .age-group {
            background: #FFE8CC;
            color: #F86015;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .location {
            color: #666;
            font-size: 13px;
        }

        .profile-bio {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
            margin: 15px 0;
        }

        .profile-stats {
            display: flex;
            gap: 30px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #E5D7CC;
        }

        .stat {
            text-align: center;
        }

        .stat-num {
            display: block;
            font-weight: 700;
            font-size: 22px;
            color: #F86015;
        }

        .stat-label {
            display: block;
            font-size: 11px;
            color: #999;
            margin-top: 3px;
        }

        .profile-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .action-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid #F86015;
        }

        .action-btn.primary {
            background: #F86015;
            color: white;
        }

        .action-btn.secondary {
            background: white;
            color: #F86015;
        }

        .section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .section-title {
            color: #F86015;
            font-size: 18px;
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'Baloo', cursive;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }

        .card {
            background: white;
            border: 1px solid #E5D7CC;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s;
        }

        .card:hover {
            box-shadow: 0 4px 15px rgba(248, 96, 21, 0.15);
            transform: translateY(-2px);
        }

        .card-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
            background: #f5f5f5;
        }

        .card-content {
            padding: 12px;
        }

        .card-title {
            font-weight: 600;
            color: #333;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .card-meta {
            font-size: 11px;
            color: #999;
        }

        .mentor-card {
            display: flex;
            gap: 12px;
            padding: 15px;
            border: 1px solid #E5D7CC;
            border-radius: 12px;
            align-items: center;
        }

        .mentor-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            background: #F86015;
            flex-shrink: 0;
        }

        .mentor-info h4 {
            margin: 0 0 3px 0;
            font-size: 13px;
            color: #333;
        }

        .mentor-info p {
            margin: 0;
            font-size: 11px;
            color: #999;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
        }

        .post-card {
            background: white;
            border: 1px solid #E5D7CC;
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
        }

        .post-card:hover {
            box-shadow: 0 4px 15px rgba(248, 96, 21, 0.15);
        }

        .post-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .post-body {
            padding: 15px;
        }

        .post-title {
            color: #333;
            font-size: 13px;
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .post-stats {
            display: flex;
            gap: 12px;
            font-size: 11px;
            color: #999;
            padding-top: 8px;
            border-top: 1px solid #E5D7CC;
        }
    </style>
</head>
<body>
    <?php require_once(__DIR__ . '/../inc/header.inc.php'); ?>

    <div class="main-container">
        <?php require_once(__DIR__ . '/../inc/sidebar-left.inc.php'); ?>

        <main class="content" style="padding: 20px; flex: 1;">
            
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-hero">
                    <img src="<?= htmlspecialchars($this->user['avatar_url'] ?: 'https://via.placeholder.com/150') ?>" 
                         alt="<?= htmlspecialchars($this->user['username']) ?>" 
                         class="profile-avatar">
                    
                    <div class="profile-info">
                        <h1><?= htmlspecialchars($this->user['display_name'] ?? $this->user['username']) ?></h1>
                        
                        <div class="profile-meta">
                            <span class="username">@<?= htmlspecialchars($this->user['username']) ?></span>
                            <?php if ($this->user['age_group']): ?>
                                <span class="age-group"><?= htmlspecialchars($this->user['age_group']) ?></span>
                            <?php endif; ?>
                            <?php if ($this->user['location']): ?>
                                <span class="location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($this->user['location']) ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if ($this->user['bio']): ?>
                            <div class="profile-bio"><?= htmlspecialchars($this->user['bio']) ?></div>
                        <?php endif; ?>

                        <div class="profile-stats">
                            <div class="stat">
                                <span class="stat-num"><?= count($this->posts) ?></span>
                                <span class="stat-label">Recettes</span>
                            </div>
                            <div class="stat">
                                <span class="stat-num"><?= $this->friends_count ?></span>
                                <span class="stat-label">En suivant</span>
                            </div>
                            <div class="stat">
                                <span class="stat-num"><?= $this->followers_count ?></span>
                                <span class="stat-label">Followers</span>
                            </div>
                        </div>

                        <div class="profile-actions">
                            <?php if ($this->isOwnProfile): ?>
                                <!-- Own Profile: Show Edit & Create Post -->
                                <a href="?modifprofil" class="action-btn primary" style="display: inline-block;">
                                    <i class="fas fa-edit"></i> Éditer le profil
                                </a>
                                <a href="?creerpost" class="action-btn secondary" style="display: inline-block;">
                                    <i class="fas fa-plus-circle"></i> Créer un post
                                </a>
                            <?php else: ?>
                                <!-- Other Profile: Show Message, Connect, Follow -->
                                <button class="action-btn primary" onclick="window.location='?messages&user=<?= $this->user['id'] ?>'">
                                    <i class="fas fa-message"></i> Message
                                </button>
                                <button class="action-btn secondary" onclick="connectUser(<?= $this->user['id'] ?>)">
                                    <i class="fas fa-user-plus"></i> Connect
                                </button>
                                <button class="action-btn secondary" onclick="followUser(<?= $this->user['id'] ?>)">
                                    <i class="fas fa-star"></i> Follow
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mentors Section -->
            <?php if (!empty($this->mentors)): ?>
                <div class="section">
                    <h2 class="section-title">
                        <i class="fas fa-user-tie"></i> Mentors
                    </h2>
                    <div class="grid-container">
                        <?php foreach ($this->mentors as $mentor): ?>
                            <a href="?profile&user=<?= htmlspecialchars($mentor['username']) ?>" style="text-decoration: none;">
                                <div class="mentor-card">
                                    <img src="<?= htmlspecialchars($mentor['avatar_url'] ?: 'https://via.placeholder.com/50') ?>" 
                                         alt="<?= htmlspecialchars($mentor['display_name']) ?>" 
                                         class="mentor-avatar">
                                    <div class="mentor-info">
                                        <h4><?= htmlspecialchars($mentor['display_name']) ?></h4>
                                        <p class="age-group" style="background: #FFE8CC; color: #F86015; padding: 2px 6px; border-radius: 4px; display: inline-block;">
                                            <?= htmlspecialchars($mentor['age_group']) ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Communities Section -->
            <?php if (!empty($this->communities)): ?>
                <div class="section">
                    <h2 class="section-title">
                        <i class="fas fa-users"></i> Communautés
                    </h2>
                    <div class="grid-container">
                        <?php foreach ($this->communities as $community): ?>
                            <div class="card">
                                <img src="<?= htmlspecialchars($community['cover_url'] ?: 'https://via.placeholder.com/200') ?>" 
                                     alt="<?= htmlspecialchars($community['name']) ?>" 
                                     class="card-image">
                                <div class="card-content">
                                    <div class="card-title"><?= htmlspecialchars($community['name']) ?></div>
                                    <div class="card-meta">
                                        <i class="fas fa-users"></i> <?= $community['member_count'] ?>K Members
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Posts Section -->
            <div class="section">
                <h2 class="section-title">
                    <i class="fas fa-flower"></i> Fleurs de <?= htmlspecialchars($this->user['display_name'] ?? $this->user['username']) ?>
                </h2>
                
                <?php if (!empty($this->posts)): ?>
                    <div class="posts-grid">
                        <?php foreach ($this->posts as $post): ?>
                            <a href="?post&id=<?= $post['id'] ?>" style="text-decoration: none; color: inherit;">
                                <div class="post-card">
                                    <?php if ($post['media_urls']): ?>
                                        <img src="<?= htmlspecialchars(explode(',', $post['media_urls'])[0]) ?>" 
                                             alt="Post" class="post-image">
                                    <?php else: ?>
                                        <div class="post-image">
                                            <i class="fas fa-image" style="font-size: 48px; color: #ddd;"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="post-body">
                                        <div class="post-title"><?= htmlspecialchars(substr($post['content'], 0, 85)) ?></div>
                                        <div class="post-stats">
                                            <span><i class="fas fa-heart"></i> <?= $post['like_count'] ?></span>
                                            <span><i class="fas fa-comment"></i> <?= $post['comment_count'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-flower" style="font-size: 48px; color: #ddd; margin-bottom: 15px;"></i>
                        <p>Aucune fleur partagée pour le moment 🌼</p>
                    </div>
                <?php endif; ?>
            </div>

        </main>

        <?php require_once(__DIR__ . '/../inc/sidebar-right.inc.php'); ?>
    </div>

    <?php require_once(__DIR__ . '/../inc/footer.inc.php'); ?>
        <script>
            function connectUser(userId) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                fetch('api/connect.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrf},
                    body: JSON.stringify({user_id: userId})
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        alert('Demande de connexion envoyée!');
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                });
            }

            function followUser(userId) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                fetch('api/follow.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-Token': csrf},
                    body: JSON.stringify({user_id: userId})
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        alert(data.message || 'Suivi!');
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                });
            }
        </script></body>
</html>
        <?php
        return ob_get_clean();
    }
}