<?php
/**
 * VueDiscover.class.php
 * Page de découverte - Explore les utilisateurs et recettes
 */
require_once(__DIR__ . '/../config.php');

class VueDiscover
{
    private $cnxDB;
    private $userId;
    private $users;
    private $trending_posts;

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
            
            $this->loadUsers();
            $this->loadTrendingPosts();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $this->users = [];
            $this->trending_posts = [];
        }
    }

    private function loadUsers()
    {
        try {
            // Get users that current user is not following
            $stmt = $this->cnxDB->prepare("
                SELECT u.id, u.username, u.display_name, u.avatar_url, u.bio,
                       COUNT(DISTINCT f.follower_id) as followers,
                       COUNT(DISTINCT p.id) as posts,
                       EXISTS(SELECT 1 FROM follows WHERE follower_id = :user_id AND following_id = u.id) as is_following
                FROM users u
                LEFT JOIN follows f ON u.id = f.following_id
                LEFT JOIN posts p ON u.id = p.author_id
                WHERE u.id != :user_id
                GROUP BY u.id
                ORDER BY RAND()
                LIMIT 12
            ");
            $stmt->execute([':user_id' => $this->userId]);
            $this->users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error loading users: " . $e->getMessage());
            $this->users = [];
        }
    }

    private function loadTrendingPosts()
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT p.id, p.content, p.created_at, p.author_id,
                       u.username, u.display_name, u.avatar_url,
                       COUNT(DISTINCT pl.user_id) as like_count,
                       COUNT(DISTINCT c.id) as comment_count,
                       GROUP_CONCAT(pm.media_url) as media_urls
                FROM posts p
                LEFT JOIN users u ON p.author_id = u.id
                LEFT JOIN post_media pm ON p.id = pm.post_id
                LEFT JOIN post_likes pl ON p.id = pl.post_id
                LEFT JOIN comments c ON p.id = c.post_id
                WHERE p.author_id != :user_id
                GROUP BY p.id
                ORDER BY like_count DESC, p.created_at DESC
                LIMIT 20
            ");
            $stmt->execute([':user_id' => $this->userId]);
            $this->trending_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error loading posts: " . $e->getMessage());
            $this->trending_posts = [];
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Security::generateCSRFToken()); ?>">
    <title>Découvrir - claque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <style>
        h1, h2, h3, h4, h5, h6 { font-family: 'Baloo', cursive; }
        a { text-decoration: none !important; }
        
        .main-container { display: flex; }
        .content { flex: 1; }
        
        .discover-header {
            background: linear-gradient(135deg, #F86015, #FF9D5C);
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .discover-header h1 {
            font-size: 28px;
            margin: 0 0 8px 0;
        }
        
        .discover-header p {
            margin: 0;
            opacity: 0.95;
        }
        
        .section {
            margin-bottom: 40px;
        }
        
        .section-title {
            color: #F86015;
            font-size: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .user-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s;
        }
        
        .user-card:hover {
            box-shadow: 0 4px 15px rgba(248, 96, 21, 0.15);
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            border: 3px solid #F86015;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .user-username {
            color: #999;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .user-bio {
            color: #666;
            font-size: 13px;
            margin-bottom: 12px;
            min-height: 40px;
        }
        
        .user-stats {
            display: flex;
            justify-content: space-around;
            margin-bottom: 12px;
            padding: 12px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        
        .user-stat {
            text-align: center;
        }
        
        .user-stat-num {
            font-weight: 700;
            color: #F86015;
            font-size: 16px;
        }
        
        .user-stat-label {
            font-size: 11px;
            color: #999;
        }
        
        .btn-follow {
            width: 100%;
            background: #F86015;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-follow:hover {
            background: #E65A0C;
        }
        
        .btn-follow.following {
            background: #ccc;
        }
        
        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }
        
        .post-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: box-shadow 0.3s;
            cursor: pointer;
        }
        
        .post-card:hover {
            box-shadow: 0 4px 15px rgba(248, 96, 21, 0.15);
        }
        
        .post-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #f5f5f5;
        }
        
        .post-content {
            padding: 15px;
        }
        
        .post-author {
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
            margin-bottom: 12px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .post-stats {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #999;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }
        
        .post-stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        #discover-map {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            z-index: 1;
        }

        .map-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 25px;
        }

        .leaflet-popup-content {
            font-weight: 600;
            cursor: pointer;
            color: #F86015;
        }
    </style>
</head>
<body>
    <?php require_once(__DIR__ . '/../inc/header.inc.php'); ?>
    
    <div class="main-container">
        <?php require_once(__DIR__ . '/../inc/sidebar-left.inc.php'); ?>
        
        <main class="content" style="padding: 20px;">
            <!-- Header -->
            <div class="discover-header">
                <h1>🔍 Découvrez</h1>
                <p>Explorez de nouvelles recettes et des contributeurs passionnants</p>
            </div>
            
            <!-- Suggested Users -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-users"></i> Utilisateurs suggérés
                </div>
                
                <?php if (count($this->users) > 0): ?>
                    <div class="users-grid">
                        <?php foreach ($this->users as $user): ?>
                            <div class="user-card">
                                <img src="<?= htmlspecialchars($user['avatar_url'] ?: 'https://via.placeholder.com/80') ?>" 
                                     alt="<?= htmlspecialchars($user['username']) ?>" 
                                     class="user-avatar"
                                     onclick="window.location='?profil&username=<?= htmlspecialchars($user['username']) ?>'">
                                
                                <div class="user-name"><?= htmlspecialchars($user['display_name'] ?? $user['username']) ?></div>
                                <div class="user-username">@<?= htmlspecialchars($user['username']) ?></div>
                                
                                <?php if ($user['bio']): ?>
                                    <div class="user-bio"><?= htmlspecialchars(substr($user['bio'], 0, 60)) ?></div>
                                <?php endif; ?>
                                
                                <div class="user-stats">
                                    <div class="user-stat">
                                        <div class="user-stat-num"><?= $user['followers'] ?></div>
                                        <div class="user-stat-label">Followers</div>
                                    </div>
                                    <div class="user-stat">
                                        <div class="user-stat-num"><?= $user['posts'] ?></div>
                                        <div class="user-stat-label">Posts</div>
                                    </div>
                                </div>
                                
                                <button class="btn-follow <?= $user['is_following'] ? 'following' : '' ?>" 
                                        onclick="toggleFollow(<?= $user['id'] ?>, this)"
                                        data-user="<?= $user['id'] ?>">
                                    <?= $user['is_following'] ? 'Following ✓' : 'Follow' ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users-slash"></i>
                        <p>Aucun utilisateur trouvé</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Interactive Map Section -->
            <div class="map-section">
                <div class="section-title">
                    <i class="fas fa-map-location-dot"></i> Explorez sur la carte
                </div>
                <div id="discover-map"></div>
                <p style="text-align: center; color: #999; font-size: 13px; margin-top: 10px;">
                    Découvrez les chefs et passionnés de cuisine près de vous
                </p>
            </div>
            
            <!-- Trending Posts -->
            <div class="section">
                <div class="section-title">
                    <i class="fas fa-fire"></i> Tendances
                </div>
                
                <?php if (count($this->trending_posts) > 0): ?>
                    <div class="posts-grid">
                        <?php foreach ($this->trending_posts as $post): ?>
                            <div class="post-card" onclick="window.location='?post=<?= $post['id'] ?>'">
                                <?php if ($post['media_urls']): ?>
                                    <img src="<?= htmlspecialchars(explode(',', $post['media_urls'])[0]) ?>" 
                                         alt="Post" class="post-image">
                                <?php else: ?>
                                    <div class="post-image" style="display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="font-size: 48px; color: #ddd;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="post-content">
                                    <div class="post-author">
                                        <img src="<?= htmlspecialchars($post['avatar_url'] ?: 'https://via.placeholder.com/36') ?>" 
                                             alt="<?= htmlspecialchars($post['username']) ?>" 
                                             class="post-author-avatar">
                                        <div class="post-author-info">
                                            <div class="post-author-name"><?= htmlspecialchars($post['display_name'] ?? $post['username']) ?></div>
                                            <div class="post-author-username">@<?= htmlspecialchars($post['username']) ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="post-text"><?= htmlspecialchars(substr($post['content'], 0, 100)) ?></div>
                                    
                                    <div class="post-stats">
                                        <div class="post-stat">
                                            <i class="fas fa-heart"></i>
                                            <span><?= $post['like_count'] ?></span>
                                        </div>
                                        <div class="post-stat">
                                            <i class="fas fa-comments"></i>
                                            <span><?= $post['comment_count'] ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-posts"></i>
                        <p>Aucune recette trouvée</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
        
        <?php require_once(__DIR__ . '/../inc/sidebar-right.inc.php'); ?>
    </div>
    
    <?php require_once(__DIR__ . '/../inc/footer.inc.php'); ?>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <script>
        // Initialize map when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('discover-map').setView([48.8566, 2.3522], 5); // Paris center
            
            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            // Sample markers for chefs/users
            const markers = [
                { lat: 48.8566, lng: 2.3522, name: 'Paris Chef', specialty: 'French Cuisine' },
                { lat: 45.5017, lng: -73.5673, name: 'Montreal Master', specialty: 'Pastry' },
                { lat: 43.2965, lng: 5.3698, name: 'Marseille Cook', specialty: 'Mediterranean' }
            ];
            
            markers.forEach(marker => {
                const popupContent = '<strong>' + marker.name + '</strong><br>' + marker.specialty + '<br><small style="color:#F86015; cursor:pointer;">Voir le profil</small>';
                L.marker([marker.lat, marker.lng])
                    .bindPopup(popupContent)
                    .addTo(map)
                    .on('popupopen', function() {
                        const popupElement = this.getPopup().getElement();
                        const profileLink = popupElement.querySelector('small');
                        if (profileLink) {
                            profileLink.onclick = () => window.location = '?profile&username=' + marker.name.toLowerCase().replace(' ', '');
                        }
                    });
            });
            
            // Invalidate size to ensure proper rendering
            setTimeout(() => map.invalidateSize(), 100);
        });
    </script>
    
    <script src="js/following.js" defer></script>
    <script>
        function toggleFollow(userId, btn) {
            event.stopPropagation();
            
            const isFollowing = btn.classList.contains('following');
            const action = isFollowing ? 'unfollow' : 'follow';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            fetch('api.php?action=' + action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId + '&csrf_token=' + encodeURIComponent(csrfToken)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.classList.toggle('following');
                    btn.textContent = isFollowing ? 'Follow' : 'Following ✓';
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
