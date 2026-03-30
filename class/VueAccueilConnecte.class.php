<?php
/**
 * VueAccueilConnecte.class.php
 * Page d'accueil pour utilisateurs connectés - Données réelles BDD
 * Version corrigée pour schéma sans colonne "id" dans post_likes
 */

class VueAccueilConnecte
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
        
        $dsn  = "mysql:host=mysql-digbeu.alwaysdata.net;dbname=digbeu_iobc;charset=utf8mb4";
        $user = "digbeu_jeremy";
        $pass = "toto123&*";
 
        try {
            $this->cnxDB = new PDO($dsn, $user, $pass);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $this->cnxDB->prepare("
                SELECT id, username, display_name, avatar_url, karma_score, language_pref
                FROM users 
                WHERE id = :id
            ");
            $stmt->execute([':id' => $this->userId]);
            $this->user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$this->user) {
                session_destroy();
                header("Location: index.php?login");
                exit;
            }
            
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
 
    public function __toString()
    {
        return $this->afficherContenu();
    }
    
    /**
     * Récupérer les posts tendance (CORRIGÉ - pas de pl.id)
     */
    private function getTrendingPosts($limit = 6)
    {
        $stmt = $this->cnxDB->prepare("
            SELECT 
                p.id, p.content, p.created_at,
                u.username, u.display_name, u.avatar_url,
                COUNT(DISTINCT pl.user_id) as like_count,
                COUNT(DISTINCT c.id) as comment_count,
                pm.media_url,
                (SELECT COUNT(*) FROM post_likes WHERE user_id = :user_id AND post_id = p.id) as is_liked
            FROM posts p
            JOIN users u ON p.author_id = u.id
            LEFT JOIN post_likes pl ON p.id = pl.post_id
            LEFT JOIN comments c ON p.id = c.post_id
            LEFT JOIN post_media pm ON p.id = pm.post_id AND pm.order_index = 0
            GROUP BY p.id, p.content, p.created_at, u.username, u.display_name, u.avatar_url, pm.media_url
            ORDER BY like_count DESC, p.created_at DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $this->userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les communautés populaires
     */
    private function getTrendingCommunities($limit = 4)
    {
        $stmt = $this->cnxDB->prepare("
            SELECT 
                c.id, c.name, c.description_en, c.cover_url,
                COUNT(cm.user_id) as member_count,
                (SELECT COUNT(*) FROM community_members WHERE user_id = :user_id AND community_id = c.id) as is_member
            FROM communities c
            LEFT JOIN community_members cm ON c.id = cm.community_id
            GROUP BY c.id, c.name, c.description_en, c.cover_url
            ORDER BY member_count DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':user_id', $this->userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les événements à venir
     */
    private function getUpcomingEvents($limit = 3)
    {
        $stmt = $this->cnxDB->prepare("
            SELECT 
                e.id, e.title, e.description, e.start_time, e.event_type,
                u.username as organizer_name,
                c.name as community_name
            FROM events e
            JOIN users u ON e.organizer_id = u.id
            LEFT JOIN communities c ON e.community_id = c.id
            WHERE e.start_time > NOW()
            ORDER BY e.start_time ASC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer les badges de l'utilisateur
     */
    private function getUserBadges()
    {
        $stmt = $this->cnxDB->prepare("
            SELECT b.name, b.icon_url
            FROM badges b
            JOIN user_badges ub ON b.id = ub.badge_id
            WHERE ub.user_id = :user_id
        ");
        $stmt->execute([':user_id' => $this->userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    private function afficherContenu()
    {
        $posts = $this->getTrendingPosts();
        $communities = $this->getTrendingCommunities();
        $events = $this->getUpcomingEvents();
        $badges = $this->getUserBadges();
        
        $displayName = htmlspecialchars($this->user['display_name'] ?? $this->user['username']);
        $avatarUrl = $this->user['avatar_url'] ?? '';
        $initial = strtoupper(substr($this->user['username'], 0, 1));
        $karma = $this->user['karma_score'] ?? 0;
 
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAM - Accueil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --cream: #FFE8CC; --orange: #FF7318; --pink: #D81B60;
            --blue: #4A90E2; --green: #2ECC71; --brown: #634444;
            --white: #FFFFFF; --gray-light: #F5F5F5;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--cream); color: var(--brown);
        }
        .header {
            background: var(--white); padding: 15px 40px;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky; top: 0; z-index: 1000;
        }
        .logo { font-size: 28px; font-weight: 800; color: var(--brown); text-decoration: none; }
        .search-bar { flex: 1; max-width: 500px; margin: 0 40px; position: relative; }
        .search-bar input {
            width: 100%; padding: 12px 45px 12px 20px;
            border: 2px solid var(--cream); border-radius: 25px;
            font-size: 14px; outline: none; background: var(--gray-light);
        }
        .search-bar i {
            position: absolute; right: 15px; top: 50%;
            transform: translateY(-50%); color: var(--orange);
        }
        .header-actions { display: flex; align-items: center; gap: 20px; }
        .user-menu { display: flex; align-items: center; gap: 12px; cursor: pointer; }
        .avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, var(--orange), var(--pink));
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700;
        }
        .avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
        .karma-badge {
            background: var(--orange); color: white;
            padding: 4px 12px; border-radius: 15px;
            font-size: 13px; font-weight: 600;
        }
        .container {
            display: flex; max-width: 1400px; margin: 0 auto; padding: 30px 20px;
        }
        .sidebar-left { width: 260px; padding-right: 30px; position: sticky; top: 90px; height: calc(100vh - 90px); overflow-y: auto; }
        .main-content { flex: 1; max-width: 800px; }
        .sidebar-right { width: 320px; padding-left: 30px; position: sticky; top: 90px; height: calc(100vh - 90px); overflow-y: auto; }
        .nav-menu { list-style: none; }
        .nav-menu li { margin-bottom: 8px; }
        .nav-menu a {
            display: flex; align-items: center; gap: 15px;
            padding: 14px 18px; color: var(--brown);
            text-decoration: none; border-radius: 12px;
            transition: all 0.3s; font-weight: 500;
        }
        .nav-menu a:hover, .nav-menu a.active {
            background: var(--orange); color: white; transform: translateX(5px);
        }
        .nav-menu i { font-size: 20px; width: 25px; text-align: center; }
        .welcome-banner {
            background: linear-gradient(135deg, var(--orange), var(--pink));
            border-radius: 20px; padding: 30px;
            color: white; margin-bottom: 30px;
        }
        .welcome-banner h1 { font-size: 26px; margin-bottom: 8px; }
        .section-title {
            font-size: 22px; font-weight: 700;
            margin: 30px 0 20px; display: flex;
            justify-content: space-between; align-items: center;
        }
        .posts-grid {
            display: grid; grid-template-columns: repeat(2, 1fr);
            gap: 20px; margin-bottom: 30px;
        }
        .post-card {
            background: white; border-radius: 20px;
            overflow: hidden; box-shadow: var(--shadow);
            transition: transform 0.3s;
        }
        .post-card:hover { transform: translateY(-5px); }
        .post-image {
            width: 100%; height: 200px;
            background: linear-gradient(135deg, var(--cream), #FFD4A3);
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        .post-image img { width: 100%; height: 100%; object-fit: cover; }
        .post-image i { font-size: 50px; color: var(--orange); opacity: 0.6; }
        .post-content { padding: 18px; }
        .post-author {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 12px; font-size: 14px;
        }
        .post-author .avatar-small {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--orange); color: white;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 12px;
        }
        .post-text { color: #666; font-size: 14px; margin-bottom: 12px; line-height: 1.5; }
        .post-stats {
            display: flex; justify-content: space-between;
            align-items: center; color: #999; font-size: 13px;
        }
        .post-actions { display: flex; gap: 15px; align-items: center; }
        .post-actions i { cursor: pointer; transition: all 0.2s; }
        .post-actions i:hover { color: var(--pink); transform: scale(1.2); }
        .post-actions i.liked { color: var(--pink); }
        .community-card {
            background: white; border-radius: 16px;
            padding: 20px; margin-bottom: 15px; box-shadow: var(--shadow);
        }
        .community-header {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 10px;
        }
        .community-name { font-weight: 700; font-size: 16px; }
        .community-members { color: #888; font-size: 13px; }
        .btn-join {
            background: var(--orange); color: white;
            border: none; padding: 8px 20px; border-radius: 20px;
            cursor: pointer; font-weight: 600; transition: all 0.3s;
        }
        .btn-join:hover { background: var(--pink); transform: scale(1.05); }
        .btn-joined { background: var(--green); }
        .event-card {
            background: white; border-radius: 16px;
            padding: 20px; margin-bottom: 15px;
            box-shadow: var(--shadow); border-left: 4px solid var(--blue);
        }
        .event-title { font-weight: 700; margin-bottom: 8px; }
        .event-meta { color: #888; font-size: 13px; margin-bottom: 5px; }
        .event-type {
            display: inline-block; padding: 3px 10px;
            border-radius: 12px; font-size: 11px;
            font-weight: 600; margin-top: 8px;
        }
        .type-public { background: #E3F2FD; color: var(--blue); }
        .type-private { background: #FCE4EC; color: var(--pink); }
        .type-shared { background: #E8F5E9; color: var(--green); }
        .badges-container { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px; }
        .badge {
            background: var(--gray-light); padding: 8px 15px;
            border-radius: 20px; font-size: 13px;
            display: flex; align-items: center; gap: 6px;
        }
        @media (max-width: 1024px) { .sidebar-right { display: none; } }
        @media (max-width: 768px) {
            .sidebar-left { display: none; }
            .posts-grid { grid-template-columns: 1fr; }
            .header { padding: 15px 20px; }
            .search-bar { display: none; }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="index.php?accueil" class="logo">🍳 KAM</a>
        <div class="search-bar">
            <input type="text" placeholder="Rechercher des recettes, utilisateurs...">
            <i class="fas fa-search"></i>
        </div>
        <div class="header-actions">
            <div class="user-menu">
                <span class="karma-badge"><i class="fas fa-star"></i> <?= $karma ?></span>
                <?php if ($avatarUrl): ?>
                    <div class="avatar"><img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar"></div>
                <?php else: ?>
                    <div class="avatar"><?= $initial ?></div>
                <?php endif; ?>
                <span><?= $displayName ?></span>
            </div>
            <a href="index.php?logout" style="color: var(--pink); text-decoration: none; font-weight: 600;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar-left">
            <ul class="nav-menu">
                <li><a href="index.php?accueil" class="active"><i class="fas fa-home"></i> Accueil</a></li>
                <li><a href="#"><i class="fas fa-compass"></i> Découvrir</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Communautés</a></li>
                <li><a href="#"><i class="fas fa-calendar-alt"></i> Événements</a></li>
                <li><a href="#"><i class="fas fa-envelope"></i> Messages</a></li>
                <li><a href="#"><i class="fas fa-bookmark"></i> Enregistrés</a></li>
                <li><a href="#"><i class="fas fa-user-circle"></i> Profil</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <div class="welcome-banner">
                <h1>👋 Bonjour, <?= $displayName ?> !</h1>
                <p>Prêt à découvrir de nouvelles recettes aujourd'hui ?</p>
            </div>

            <h2 class="section-title">
                <span>🔥 Recettes tendance</span>
                <a href="#" style="color: var(--orange); text-decoration: none; font-size: 14px;">Voir tout →</a>
            </h2>
            
            <div class="posts-grid">
                <?php if (empty($posts)): ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #888; padding: 40px;">
                        Aucune recette pour le moment. Soyez le premier à partager !
                    </p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <div class="post-image">
                            <?php if ($post['media_url']): ?>
                                <img src="<?= htmlspecialchars($post['media_url']) ?>" alt="Recipe">
                            <?php else: ?>
                                <i class="fas fa-utensils"></i>
                            <?php endif; ?>
                        </div>
                        <div class="post-content">
                            <div class="post-author">
                                <div class="avatar-small">
                                    <?= strtoupper(substr($post['username'], 0, 1)) ?>
                                </div>
                                <span><?= htmlspecialchars($post['display_name']) ?></span>
                            </div>
                            <?php if ($post['content']): ?>
                                <p class="post-text"><?= htmlspecialchars(substr($post['content'], 0, 100)) ?>...</p>
                            <?php endif; ?>
                            <div class="post-stats">
                                <div class="post-actions">
                                    <i class="<?= $post['is_liked'] ? 'fas' : 'far' ?> fa-heart <?= $post['is_liked'] ? 'liked' : '' ?>"></i>
                                    <span><?= $post['like_count'] ?></span>
                                    <i class="far fa-comment"></i>
                                    <span><?= $post['comment_count'] ?></span>
                                </div>
                                <span><i class="far fa-clock"></i> <?= date('d/m', strtotime($post['created_at'])) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>

        <aside class="sidebar-right">
            <div style="background: white; border-radius: 16px; padding: 20px; margin-bottom: 25px; box-shadow: var(--shadow);">
                <h3 style="font-size: 16px; margin-bottom: 15px;"><i class="fas fa-award" style="color: var(--orange);"></i> Mes badges</h3>
                <?php if (empty($badges)): ?>
                    <p style="color: #888; font-size: 13px;">Participez pour débloquer des badges !</p>
                <?php else: ?>
                    <div class="badges-container">
                        <?php foreach ($badges as $badge): ?>
                            <div class="badge">
                                <i class="fas fa-check-circle" style="color: var(--green);"></i>
                                <?= htmlspecialchars($badge['name']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div style="background: white; border-radius: 16px; padding: 20px; margin-bottom: 25px; box-shadow: var(--shadow);">
                <h3 style="font-size: 16px; margin-bottom: 15px;"><i class="fas fa-fire" style="color: var(--orange);"></i> Communautés populaires</h3>
                <?php foreach ($communities as $community): ?>
                <div class="community-card">
                    <div class="community-header">
                        <div>
                            <div class="community-name">r/<?= htmlspecialchars($community['name']) ?></div>
                            <div class="community-members"><?= $community['member_count'] ?> membres</div>
                        </div>
                        <button class="btn-join <?= $community['is_member'] ? 'btn-joined' : '' ?>">
                            <?= $community['is_member'] ? 'Rejoint ✓' : 'Rejoindre' ?>
                        </button>
                    </div>
                    <?php if ($community['description_en']): ?>
                        <p style="font-size: 13px; color: #666; margin-top: 8px;">
                            <?= htmlspecialchars(substr($community['description_en'], 0, 80)) ?>...
                        </p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="background: white; border-radius: 16px; padding: 20px; box-shadow: var(--shadow);">
                <h3 style="font-size: 16px; margin-bottom: 15px;"><i class="fas fa-calendar-check" style="color: var(--blue);"></i> Événements à venir</h3>
                <?php if (empty($events)): ?>
                    <p style="color: #888; font-size: 13px;">Aucun événement prévu pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="event-title"><?= htmlspecialchars($event['title']) ?></div>
                        <div class="event-meta">
                            <i class="far fa-clock"></i> <?= date('d/m/Y à H:i', strtotime($event['start_time'])) ?>
                        </div>
                        <?php if ($event['community_name']): ?>
                            <div class="event-meta">
                                <i class="fas fa-users"></i> <?= htmlspecialchars($event['community_name']) ?>
                            </div>
                        <?php endif; ?>
                        <span class="event-type type-<?= $event['event_type'] ?>">
                            <?= ucfirst($event['event_type']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
?>