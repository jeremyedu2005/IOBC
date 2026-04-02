<?php

/**
 * VueCommentsWidget - Système de commentaires professionnel
 * Affiche, crée, édite, supprime les commentaires sur posts
 */

class VueCommentsWidget
{
    private $cnxDB;
    private $user_id;

    public function __construct($cnxDB, $user_id = null)
    {
        $this->cnxDB = $cnxDB;
        $this->user_id = $user_id;
    }

    /**
     * Obtenir commentaires d'un post
     */
    public function getPostComments($post_id, $limit = 10, $offset = 0)
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT c.id, c.content, c.created_at, c.parent_comment_id,
                       u.id as author_id, u.display_name, u.avatar_url, u.username
                FROM comments c
                JOIN users u ON c.author_id = u.id
                WHERE c.post_id = :post_id AND c.parent_comment_id IS NULL
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset
            ");
            $stmt->execute([
                ':post_id' => $post_id,
                ':limit' => (int)$limit,
                ':offset' => (int)$offset
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtenir replies d'un commentaire
     */
    public function getCommentReplies($comment_id)
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT c.id, c.content, c.created_at,
                       u.id as author_id, u.display_name, u.avatar_url, u.username
                FROM comments c
                JOIN users u ON c.author_id = u.id
                WHERE c.parent_comment_id = :parent_id
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([':parent_id' => $comment_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Total commentaires pour un post
     */
    public function getCommentCount($post_id)
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT COUNT(*) as total 
                FROM comments 
                WHERE post_id = :post_id
            ");
            $stmt->execute([':post_id' => $post_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Afficher section commentaires HTML
     */
    public function afficherCommentaires($post_id)
    {
        $comments = $this->getPostComments($post_id, 5);
        $total = $this->getCommentCount($post_id);
        $user_logged_in = $this->user_id !== null;

        $html = "<div class='comments-section' style='margin-top: 20px; border-top: 2px solid #eee; padding-top: 20px;'>";
        
        // Titre
        $html .= "<h3 style='color: #F86015; font-family: Baloo, cursive; margin-bottom: 15px;'>
                    💬 Commentaires (" . $total . ")
                  </h3>";

        // Commentaires
        if (count($comments) > 0) {
            foreach ($comments as $comment) {
                $html .= $this->afficherCommentaire($comment, $post_id);
            }
        } else {
            $html .= "<p style='color: #999; text-align: center;'>Aucun commentaire. Soyez le premier! 👇</p>";
        }

        // Voir plus
        if ($total > 5) {
            $html .= "<button class='btn-voir-plus' data-post='" . $post_id . "' 
                        style='background: #f5f5f5; border: none; padding: 10px 20px; 
                               border-radius: 8px; color: #F86015; cursor: pointer; 
                               width: 100%; margin: 15px 0;'>
                        Voir les " . ($total - 5) . " autres commentaires ↓
                      </button>";
        }

        // Formulaire commentaire
        if ($user_logged_in) {
            $html .= $this->afficherFormulaireCommentaire($post_id);
        } else {
            $html .= "<div style='background: #FEF5F1; padding: 15px; border-radius: 8px; 
                           text-align: center; margin-top: 20px;'>
                        <p style='margin: 0; color: #333;'>
                            <a href='?login' style='color: #F86015; font-weight: bold; 
                                                    text-decoration: none;'>Connectez-vous</a>
                            pour commenter
                        </p>
                      </div>";
        }

        $html .= "</div>";
        return $html;
    }

    /**
     * Afficher UN commentaire
     */
    private function afficherCommentaire($comment, $post_id)
    {
        $replies = $this->getCommentReplies($comment['id']);
        $can_edit_delete = $this->user_id == $comment['author_id'];

        $html = "<div class='comment-card' style='background: #f9f9f9; padding: 15px; 
                     border-radius: 8px; margin-bottom: 15px; border-left: 3px solid #F86015;'>";
        
        // Header
        $html .= "<div style='display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;'>";
        $html .= "<div style='display: flex; align-items: center; gap: 10px;'>";
        $html .= "<img src='" . htmlspecialchars($comment['avatar_url'] ?: 'https://via.placeholder.com/40') . "' 
                 alt='avatar' style='width: 40px; height: 40px; border-radius: 50%; object-fit: cover;'>";
        $html .= "<div>";
        $html .= "<strong style='color: #333;'>" . htmlspecialchars($comment['display_name']) . "</strong>";
        $html .= "<br><small style='color: #999;'>" . date('d M Y à H:i', strtotime($comment['created_at'])) . "</small>";
        $html .= "</div>";
        $html .= "</div>";

        // Actions
        if ($can_edit_delete) {
            $html .= "<div style='display: flex; gap: 10px;'>";
            $html .= "<button class='btn-edit-comment' data-id='" . $comment['id'] . "' 
                        style='background: none; border: none; color: #F86015; cursor: pointer;'>
                        ✏️ Edit
                      </button>";
            $html .= "<button class='btn-delete-comment' data-id='" . $comment['id'] . "' 
                        style='background: none; border: none; color: #c00; cursor: pointer;'>
                        ❌ Delete
                      </button>";
            $html .= "</div>";
        }
        $html .= "</div>";

        // Contenu
        $html .= "<p style='color: #333; margin: 10px 0; font-family: Cabin, sans-serif;'>" . 
                 htmlspecialchars($comment['content']) . "</p>";

        // Reply button
        $html .= "<div style='margin-top: 10px;'>";
        $html .= "<button class='btn-reply-comment' data-id='" . $comment['id'] . "' data-post='" . $post_id . "' 
                    style='background: none; border: none; color: #F86015; cursor: pointer; 
                           font-weight: bold; font-size: 14px;'>
                    ↪️ Reply
                  </button>";
        $html .= "</div>";

        // Replies
        if (count($replies) > 0) {
            $html .= "<div style='margin-left: 30px; margin-top: 15px; border-left: 2px solid #ddd; padding-left: 15px;'>";
            foreach ($replies as $reply) {
                $html .= $this->afficherReply($reply, $post_id);
            }
            $html .= "</div>";
        }

        $html .= "</div>";
        return $html;
    }

    /**
     * Afficher reply (commentaire imbriqué)
     */
    private function afficherReply($reply, $post_id)
    {
        $can_edit_delete = $this->user_id == $reply['author_id'];

        $html = "<div class='reply-card' style='background: #fff; padding: 10px; 
                     border-radius: 6px; margin-bottom: 10px;'>";
        
        $html .= "<div style='display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;'>";
        $html .= "<div style='display: flex; align-items: center; gap: 8px;'>";
        $html .= "<img src='" . htmlspecialchars($reply['avatar_url'] ?: 'https://via.placeholder.com/32') . "' 
                 alt='avatar' style='width: 32px; height: 32px; border-radius: 50%; object-fit: cover;'>";
        $html .= "<div>";
        $html .= "<strong style='color: #333; font-size: 13px;'>" . htmlspecialchars($reply['display_name']) . "</strong>";
        $html .= "<br><small style='color: #999; font-size: 11px;'>" . 
                 date('d M Y H:i', strtotime($reply['created_at'])) . "</small>";
        $html .= "</div>";
        $html .= "</div>";

        if ($can_edit_delete) {
            $html .= "<div style='display: flex; gap: 8px;'>";
            $html .= "<button class='btn-delete-reply' data-id='" . $reply['id'] . "' 
                        style='background: none; border: none; color: #c00; cursor: pointer; font-size: 12px;'>
                        ❌
                      </button>";
            $html .= "</div>";
        }
        $html .= "</div>";

        $html .= "<p style='color: #333; margin: 0; font-size: 13px; word-wrap: break-word;'>" . 
                 htmlspecialchars($reply['content']) . "</p>";
        
        $html .= "</div>";
        return $html;
    }

    /**
     * Formulaire ajouter commentaire
     */
    private function afficherFormulaireCommentaire($post_id)
    {
        $html = "<div class='comment-form' style='margin-top: 20px; padding: 15px; 
                     background: #FEF5F1; border-radius: 8px;'>";
        
        $html .= "<form class='form-comment' data-post='" . $post_id . "' style='display: flex; flex-direction: column; gap: 10px;'>";
        
        $html .= "<textarea name='content' placeholder='Partagez votre avis...' 
                  style='padding: 10px; border: 2px solid #eee; border-radius: 6px; 
                         font-family: Cabin, sans-serif; font-size: 14px; resize: vertical; 
                         min-height: 60px;' required='required'></textarea>";
        
        $html .= "<button type='submit' class='btn-post-comment' 
                  style='background: linear-gradient(135deg, #F86015, #e55a0f); 
                         color: white; border: none; padding: 10px 20px; border-radius: 6px; 
                         cursor: pointer; font-weight: bold; align-self: flex-end;'>
                  Publier 💬
                  </button>";
        
        $html .= "</form>";
        $html .= "</div>";
        
        return $html;
    }
}
?>
