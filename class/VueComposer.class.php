<?php

/**
 * VueComposer - Créer un nouveau message
 */

class VueComposer
{
    private $cnxDB;
    private $current_user_id;

    public function __construct($cnxDB, $current_user_id = null)
    {
        $this->cnxDB = $cnxDB;
        $this->current_user_id = $current_user_id;
    }

    /**
     * Obtenir suggestions d'utilisateurs (pas encore suivis)
     */
    public function getSuggestedUsers($limit = 10)
    {
        if (!$this->current_user_id) return [];
        
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT id, display_name, avatar_url, username, bio
                FROM users
                WHERE id != :user_id
                AND id NOT IN (SELECT following_id FROM follows WHERE follower_id = :user_id)
                ORDER BY RAND()
                LIMIT :limit
            ");
            $stmt->execute([
                ':user_id' => $this->current_user_id,
                ':limit' => (int)$limit
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Afficher la page composer
     */
    public function __toString()
    {
        try {
            if (!$this->current_user_id) {
                return "<div style='text-align: center; padding: 40px;'><p>Please log in to send messages</p></div>";
            }

            $recipients = $this->getSuggestedUsers(15);

            $html = "<div class='container' style='display: flex; gap: 20px;'>";
            $html .= "<div class='sidebar-left' style='width: 220px; position: sticky; top: 80px; height: fit-content;'>";
            // Include sidebar...
            $html .= "</div>";

            $html .= "<div class='content-main' style='flex: 1; max-width: 600px;'>";
            
            $html .= "<div style='background: white; padding: 25px; border-radius: 12px; margin-bottom: 20px;'>";
            $html .= "<h2 style='color: #F86015; margin: 0 0 20px 0;'>✉️ New Message</h2>";

            $html .= "<form id='compose-form' style='display: grid; gap: 15px;'>";
            
            $html .= "<div>";
            $html .= "<label style='display: block; color: #333; font-weight: bold; margin-bottom: 8px;'>Choose Recipient:</label>";
            $html .= "<select name='recipient_id' id='recipient-select' style='width: 100%; padding: 10px; border: 1px solid #ddd; 
                          border-radius: 6px; font-family: Arial; font-size: 14px;'>";
            $html .= "<option value=''>-- Select a user --</option>";
            
            foreach ($recipients as $user) {
                $html .= "<option value='" . $user['id'] . "'>";
                $html .= htmlspecialchars($user['display_name'] ?? $user['username']);
                $html .= "</option>";
            }
            
            $html .= "</select>";
            $html .= "</div>";

            $html .= "<div>";
            $html .= "<label style='display: block; color: #333; font-weight: bold; margin-bottom: 8px;'>Message:</label>";
            $html .= "<textarea name='content' id='message-content' placeholder='Type your message here...' 
                       style='width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; 
                              font-family: Arial; resize: vertical; min-height: 150px;'></textarea>";
            $html .= "<small style='color: #999;'>Max 1000 characters</small>";
            $html .= "</div>";

            $html .= "<div style='display: flex; gap: 10px; justify-content: flex-end;'>";
            $html .= "<button type='button' onclick='window.location = \"?messages\"' 
                     style='background: #ccc; color: #333; border: none; padding: 12px 25px; 
                            border-radius: 6px; cursor: pointer; font-weight: bold;'>";
            $html .= "Cancel";
            $html .= "</button>";
            $html .= "<button type='submit' 
                     style='background: #F86015; color: white; border: none; padding: 12px 25px; 
                            border-radius: 6px; cursor: pointer; font-weight: bold;'>";
            $html .= "Send Message";
            $html .= "</button>";
            $html .= "</div>";

            $html .= "</form>";
            $html .= "</div>";

            // Quick access to contacts
            if (count($recipients) > 0) {
                $html .= "<div style='background: white; padding: 25px; border-radius: 12px;'>";
                $html .= "<h3 style='color: #F86015; margin: 0 0 15px 0;'>Suggested Recipients</h3>";
                $html .= "<div style='display: grid; gap: 10px;'>";
                
                foreach ($recipients as $user) {
                    $html .= "<div onclick=\"document.getElementById('recipient-select').value = " . $user['id'] . "\" 
                             style='background: #f9f9f9; padding: 12px; border-radius: 6px; cursor: pointer;
                                     display: flex; align-items: center; gap: 12px; border: 1px solid #eee;
                                     transition: all 0.2s;'";
                    $html .= " onmouseover=\"this.style.background='#f0f0f0'; this.style.borderColor='#F86015';\"";
                    $html .= " onmouseout=\"this.style.background='#f9f9f9'; this.style.borderColor='#eee';\">";
                    
                    $html .= "<img src='" . htmlspecialchars($user['avatar_url'] ?: 'https://via.placeholder.com/40') . "' 
                             alt='avatar' style='width: 40px; height: 40px; border-radius: 50%; object-fit: cover;'>";
                    
                    $html .= "<div style='flex: 1;'>";
                    $html .= "<p style='margin: 0; font-weight: bold; color: #333;'>" . 
                             htmlspecialchars($user['display_name'] ?? $user['username']) . "</p>";
                    $html .= "<small style='color: #999;'>@" . htmlspecialchars($user['username']) . "</small>";
                    $html .= "</div>";
                    
                    $html .= "<i class='fas fa-arrow-right' style='color: #F86015;'></i>";
                    $html .= "</div>";
                }
                
                $html .= "</div>";
                $html .= "</div>";
            }

            $html .= "</div>";
            $html .= "</div>";

            return $html;
        } catch (Exception $e) {
            return "<div style='padding: 20px; color: #d9534f; background: #f2dede; border-radius: 4px;'>Error loading compose page</div>";
        }
    }
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('compose-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const recipientId = document.getElementById('recipient-select').value;
            const content = document.getElementById('message-content').value.trim();
            
            if (!recipientId) {
                alert('Please select a recipient');
                return;
            }
            
            if (!content) {
                alert('Please type a message');
                return;
            }

            fetch('api.php?action=send_message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'receiver_id=' + recipientId + '&content=' + encodeURIComponent(content)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Message sent! ✓');
                    window.location = '?conversation&user=' + recipientId;
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to send message');
            });
        });
    }
});
</script>
