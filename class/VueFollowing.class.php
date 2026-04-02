<?php

/**
 * VueFollowing - Affiche les utilisateurs suivis par un utilisateur
 */

class VueFollowing
{
    private $cnxDB;
    private $current_user_id;

    public function __construct($cnxDB, $current_user_id = null)
    {
        $this->cnxDB = $cnxDB;
        $this->current_user_id = $current_user_id;
    }

    /**
     * Obtenir les utilisateurs suivis
     */
    public function getFollowing($user_id, $limit = 20, $offset = 0)
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT u.id, u.display_name, u.avatar_url, u.bio, u.username,
                       COUNT(DISTINCT f.follower_id) as follower_count,
                       (SELECT COUNT(*) FROM follows WHERE follower_id = :current_user AND following_id = u.id) as is_followed
                FROM follows f
                JOIN users u ON f.following_id = u.id
                WHERE f.follower_id = :user_id
                GROUP BY u.id
                ORDER BY f.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->execute([
                ':user_id' => $user_id,
                ':current_user' => $this->current_user_id ?? 0,
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Total utilisateurs suivis
     */
    public function getFollowingCount($user_id)
    {
        try {
            $stmt = $this->cnxDB->prepare("SELECT COUNT(*) as total FROM follows WHERE follower_id = :user_id");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Vérifier si user1 suit user2
     */
    public function isFollowing($user1_id, $user2_id)
    {
        try {
            $stmt = $this->cnxDB->prepare("SELECT COUNT(*) as count FROM follows WHERE follower_id = :f AND following_id = :b");
            $stmt->execute([':f' => $user1_id, ':b' => $user2_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Afficher page following
     */
    public function __toString()
    {
        try {
            $user_id = $_GET['user'] ?? $this->current_user_id;
            if (!$user_id) return "<p>User not found</p>";

            // Get user info
            $stmt = $this->cnxDB->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$user) return "<p>User not found</p>";

            $following = $this->getFollowing($user_id);
            $following_count = $this->getFollowingCount($user_id);

            $html = "<div class='container' style='display: flex; gap: 20px;'>";
            $html .= "<div class='sidebar-left' style='width: 220px; position: sticky; top: 80px; height: fit-content;'>";
            // Include sidebar...
            $html .= "</div>";

            $html .= "<div class='content-main' style='flex: 1;'>";
            
            $html .= "<div style='background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;'>";
            $html .= "<h2 style='color: #F86015; margin: 0 0 10px 0;'>Following</h2>";
            $html .= "<p style='color: #999; margin: 0;'>" . $following_count . " following</p>";
            $html .= "</div>";

            if (count($following) > 0) {
                $html .= "<div style='display: grid; gap: 15px;'>";
                foreach ($following as $person) {
                    $html .= $this->afficherFollowingCard($person);
                }
                $html .= "</div>";
            } else {
                $html .= "<div style='text-align: center; padding: 40px; background: #f5f5f5; border-radius: 12px;'>";
                $html .= "<p style='color: #999; font-size: 16px;'>Not following anyone yet 👤</p>";
                $html .= "</div>";
            }

            $html .= "</div>";
            $html .= "</div>";

            return $html;
        } catch (Exception $e) {
            return "<div style='padding: 20px; color: #d9534f; background: #f2dede; border-radius: 4px;'>Error loading following list</div>";
        }
    }

    /**
     * Afficher card following
     */
    private function afficherFollowingCard($person)
    {
        $html = "<div style='background: white; padding: 15px; border-radius: 8px; 
                     display: flex; justify-content: space-between; align-items: center;
                     border: 1px solid #eee;'>";
        
        $html .= "<div style='display: flex; gap: 15px; align-items: center; flex: 1;'>";
        $html .= "<img src='" . htmlspecialchars($person['avatar_url'] ?: 'https://via.placeholder.com/50') . "' 
                 alt='avatar' style='width: 50px; height: 50px; border-radius: 50%; object-fit: cover;'>";
        
        $html .= "<div style='flex: 1;'>";
        $html .= "<h4 style='margin: 0; color: #333;'>";
        $html .= "<a href='?profile&user=" . $person['id'] . "' style='text-decoration: none; color: #F86015;'>";
        $html .= htmlspecialchars($person['display_name']);
        $html .= "</a>";
        $html .= "</h4>";
        $html .= "<small style='color: #999;'>@" . htmlspecialchars($person['username']) . "</small>";
        if ($person['bio']) {
            $html .= "<p style='color: #666; margin: 5px 0 0 0; font-size: 13px;'>" . 
                     htmlspecialchars(substr($person['bio'], 0, 100)) . "</p>";
        }
        $html .= "</div>";
        $html .= "</div>";

        // Unfollow button
        if ($this->current_user_id && $this->current_user_id != $person['id']) {
            $html .= "<button class='btn-unfollow' data-user='" . $person['id'] . "' 
                        style='background: #03A272; color: white; border: none; padding: 8px 15px; 
                               border-radius: 6px; cursor: pointer; font-weight: bold;'>" .
                        "Following ✓" . 
                     "</button>";
        }

        $html .= "</div>";
        return $html;
    }
}
?>
