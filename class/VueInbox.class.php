<?php

/**
 * VueInbox - Affiche les conversations de l'utilisateur
 */

class VueInbox
{
    private $cnxDB;
    private $current_user_id;

    public function __construct($cnxDB, $current_user_id = null)
    {
        $this->cnxDB = $cnxDB;
        $this->current_user_id = $current_user_id;
    }

    /**
     * Obtenir les conversations de l'utilisateur
     */
    public function getConversations($limit = 20, $offset = 0)
    {
        if (!$this->current_user_id) return [];
        
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT 
                    CASE 
                        WHEN sender_id = :user_id THEN receiver_id 
                        ELSE sender_id 
                    END as contact_id,
                    u.display_name, u.avatar_url, u.username,
                    MAX(m.created_at) as last_message_time,
                    (SELECT content FROM messages WHERE 
                        (sender_id = :user_id AND receiver_id = u.id) OR 
                        (sender_id = u.id AND receiver_id = :user_id)
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    COUNT(CASE WHEN receiver_id = :user_id AND read_at IS NULL THEN 1 END) as unread_count
                FROM messages m
                JOIN users u ON (
                    CASE WHEN m.sender_id = :user_id THEN m.receiver_id ELSE m.sender_id END = u.id
                )
                WHERE m.sender_id = :user_id OR m.receiver_id = :user_id
                GROUP BY contact_id
                ORDER BY last_message_time DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->execute([
                ':user_id' => $this->current_user_id,
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Compter messages non lus
     */
    public function getUnreadCount()
    {
        if (!$this->current_user_id) return 0;
        
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT COUNT(*) as count FROM messages 
                WHERE receiver_id = :user_id AND read_at IS NULL
            ");
            $stmt->execute([':user_id' => $this->current_user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Afficher l'inbox
     */
    public function __toString()
    {
        try {
            if (!$this->current_user_id) {
                return "<div style='text-align: center; padding: 40px;'><p>Please log in to view messages</p></div>";
            }

            $conversations = $this->getConversations();
            $unread_count = $this->getUnreadCount();

            $html = "<div class='container' style='display: flex; gap: 20px;'>";
            $html .= "<div class='sidebar-left' style='width: 220px; position: sticky; top: 80px; height: fit-content;'>";
            // Include sidebar...
            $html .= "</div>";

            $html .= "<div class='content-main' style='flex: 1;'>";
            
            $html .= "<div style='background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px;
                         display: flex; justify-content: space-between; align-items: center;'>";
            $html .= "<div>";
            $html .= "<h2 style='color: #F86015; margin: 0;'>Messages</h2>";
            if ($unread_count > 0) {
                $html .= "<span style='background: #F86015; color: white; padding: 2px 8px; 
                                 border-radius: 12px; font-size: 12px; font-weight: bold;'>" . 
                         $unread_count . " new</span>";
            }
            $html .= "</div>";
            $html .= "<a href='?compose' style='background: #F86015; color: white; padding: 10px 20px; 
                                                  border-radius: 6px; text-decoration: none; font-weight: bold;'>";
            $html .= "✏️ New Message";
            $html .= "</a>";
            $html .= "</div>";

            if (count($conversations) > 0) {
                $html .= "<div style='display: grid; gap: 1px; background: #eee; border-radius: 8px; overflow: hidden;'>";
                foreach ($conversations as $conv) {
                    $html .= $this->afficherConversationItem($conv);
                }
                $html .= "</div>";
            } else {
                $html .= "<div style='text-align: center; padding: 40px; background: #f5f5f5; border-radius: 12px;'>";
                $html .= "<p style='color: #999; font-size: 16px;'>No messages yet 💬</p>";
                $html .= "<a href='?compose' style='color: #F86015; text-decoration: none; font-weight: bold;'>";
                $html .= "Start a conversation";
                $html .= "</a>";
                $html .= "</div>";
            }

            $html .= "</div>";
            $html .= "</div>";

            return $html;
        } catch (Exception $e) {
            return "<div style='padding: 20px; color: #d9534f; background: #f2dede; border-radius: 4px;'>Error loading messages</div>";
        }
    }

    private function afficherConversationItem($conv)
    {
        $is_unread = $conv['unread_count'] > 0;
        $bg = $is_unread ? "#f0f0f0" : "white";
        
        $html = "<a href='index.php?conversation&user=" . urlencode($conv['contact_id']) . "' 
                     style='background: " . $bg . "; padding: 15px; cursor: pointer; 
                     display: flex; gap: 15px; align-items: center; text-decoration: none; color: inherit;
                     transition: background 0.2s;'
                     onmouseover=\"this.style.background='#FFF5F1'\"
                     onmouseout=\"this.style.background='" . $bg . "'\">";
        
        $html .= "<img src='" . htmlspecialchars($conv['avatar_url'] ?: 'https://via.placeholder.com/50') . "' 
                 alt='avatar' style='width: 50px; height: 50px; border-radius: 50%; object-fit: cover; flex-shrink: 0;'>";
        
        $html .= "<div style='flex: 1;'>";
        $html .= "<h4 style='margin: 0; color: #333; font-weight: " . ($is_unread ? "bold" : "normal") . ";'>";
        $html .= htmlspecialchars($conv['display_name']);
        $html .= "</h4>";
        $html .= "<p style='color: #999; margin: 5px 0 0 0; font-size: 13px; 
                         white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>";
        $html .= htmlspecialchars(substr($conv['last_message'] ?? '', 0, 50));
        $html .= "</p>";
        $html .= "</div>";

        if ($is_unread) {
            $html .= "<span style='background: #F86015; color: white; width: 24px; height: 24px; 
                             border-radius: 50%; display: flex; align-items: center; justify-content: center;
                             font-size: 12px; font-weight: bold; margin-left: 10px;'>" . 
                     $conv['unread_count'] . "</span>";
        }

        $html .= "<div style='color: #ccc; font-size: 20px; margin-left: 10px; flex-shrink: 0;'>→</div>";
        $html .= "</a>";

        return $html;
    }
}
?>
