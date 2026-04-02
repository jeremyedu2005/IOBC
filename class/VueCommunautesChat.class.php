<?php
require_once(__DIR__ . '/../config.php');

class VueCommunautesChat
{
    private $cnxDB;
    private $communityId;
    private $userId;

    public function __construct($communityId)
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?login");
            exit;
        }
        $this->userId = $_SESSION['user_id'];
        $this->communityId = (int)$communityId;

        // Connexion BDD
        $dsn  = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
        $user = DB_USER;
        $pass = DB_PASS;

        try {
            $this->cnxDB = new PDO($dsn, $user, $pass);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion");
        }
    }

    public function __toString()
    {
        // Charger les traductions
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        require_once(__DIR__ . '/../lang/lang.php');
        $tr = loadLang($lang) ?? [];

        // On gère l'envoi classique seulement si JS est désactivé
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content']) && !isset($_POST['ajax'])) {
            $this->traiterEnvoiMessage();
            header("Location: index.php?chat&id=" . $this->communityId);
            exit;
        } 
        // Si c'est un envoi AJAX (via JavaScript)
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
            $this->traiterEnvoiMessage();
            return ""; // On ne renvoie rien pour ne pas casser le JavaScript
        }

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
            <title><?= isset($tr['chat']) ? $tr['chat'] : 'Chat' ?> - claque</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link rel="stylesheet" href="css/style.css">
            <link rel="stylesheet" href="css/responsive.css">
            <link rel="stylesheet" href="css/communautes-chat.css">
            <style>
                body {
                    background-color: #FEF5F1;
                    font-family: 'Cabin', sans-serif;
                }
                h1, h2, h3, h4, h5, h6 {
                    font-family: 'Baloo', cursive;
                }
            </style>
        </head>
        <body>
            <!-- Header -->
            <?php require_once(__DIR__ . '/../inc/header.inc.php'); ?>

            <div style="display: flex; gap: 20px; padding: 20px; background: #FEF5F1; min-height: calc(100vh - 200px);">
                <!-- Members Sidebar -->
                <aside class="members-sidebar" style="width: 200px; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                    <h3 style="color: #F86015; margin-top: 0;"><i class="fas fa-users"></i> <?= isset($tr['members']) ? $tr['members'] : 'Membres' ?></h3>
                    <?= $this->afficherMembres() ?>
                </aside>

                <!-- Chat Main Container -->
                <main class="chat-main" style="flex: 1; background: white; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; flex-direction: column;">
                    <header class="chat-header" style="background: linear-gradient(to right, #F86015, #078CDF); color: white; padding: 20px; border-radius: 15px 15px 0 0; display: flex; align-items: center; justify-content: space-between;">
                        <a href="index.php?communautes" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 10px; font-weight: bold;">
                            <i class="fas fa-chevron-left"></i> <?= isset($tr['back']) ? $tr['back'] : 'Retour' ?>
                        </a>
                        <h2 style="margin: 0; font-family: 'Baloo', cursive; font-size: 18px;"><?= isset($tr['community_chat']) ? $tr['community_chat'] : 'Discussion Communautaire' ?></h2>
                        <div></div>
                    </header>

                    <div id="messages-box" class="messages-display" style="flex: 1; overflow-y: auto; padding: 20px; background: #FEF5F1;">
                        <?= $this->afficherMessagesUniquement() ?>
                    </div>

                    <div class="chat-footer" style="padding: 15px; background: white; border-top: 1px solid #eee; border-radius: 0 0 15px 15px;">
                        <form id="chat-form" method="post" enctype="multipart/form-data" class="chat-form" style="display: flex; gap: 10px; align-items: center;">
                            <label class="file-label" title="Joindre une image" style="cursor: pointer; color: #F86015; font-size: 20px;">
                                <i class="fas fa-camera"></i>
                                <input type="file" id="chat-file" name="media" accept="image/*" style="display:none;">
                            </label>
                            <input type="text" id="msg-input" name="content" placeholder="<?= isset($tr['msg_placeholder']) ? $tr['msg_placeholder'] : 'Écrivez à la communauté...' ?>" required autocomplete="off" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-family: 'Cabin', sans-serif;">
                            <button type="submit" style="background: #F86015; color: white; border: none; padding: 10px 15px; border-radius: 8px; cursor: pointer;"><i class="fas fa-paper-plane"></i></button>
                        </form>
                    </div>
                </main>
            </div>

            <!-- Footer -->
            <?php require_once(__DIR__ . '/../inc/footer.inc.php'); ?>

            <script>
                const box = document.getElementById('messages-box');
                const form = document.getElementById('chat-form');

                function scrollToBottom() {
                    box.scrollTop = box.scrollHeight;
                }

                // Charger les messages en temps réel
                function loadMessages() {
                    fetch('index.php?chat_messages&id=<?= $this->communityId ?>')
                        .then(response => response.text())
                        .then(html => {
                            const isAtBottom = box.scrollHeight - box.clientHeight <= box.scrollTop + 50;
                            box.innerHTML = html;
                            if (isAtBottom) scrollToBottom();
                        });
                }

                // Envoi AJAX pour éviter de rafraîchir la page
                form.onsubmit = function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('ajax', '1');

                    fetch('index.php?chat&id=<?= $this->communityId ?>', {
                        method: 'POST',
                        body: formData
                    }).then(() => {
                        this.reset();
                        loadMessages();
                    });
                };

                // Rafraîchir toutes les 10 secondes (optimisé performance)
                setInterval(loadMessages, 10000);
                window.onload = scrollToBottom;
                loadMessages(); // Charger immédiatement au démarrage
            </script>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    public function afficherMessagesUniquement()
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT m.*, u.username, u.display_name, mm.media_url 
                FROM community_messages m
                JOIN users u ON m.sender_id = u.id
                LEFT JOIN community_message_media mm ON m.id = mm.message_id
                WHERE m.community_id = :cid
                ORDER BY m.created_at ASC
            ");
            $stmt->execute([':cid' => $this->communityId]);
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Table n'existe pas encore - retourner un message informatif
            $errorMsg = "<div style='text-align:center; color:#c00; padding:20px; background:#ffe0e0; border-radius:8px; margin:20px;'>";
            $errorMsg .= "<i class='fas fa-database-slash'></i> Table de messages non trouvée.<br>";
            $errorMsg .= "<small>(Contactez l'administrateur)</small></div>";
            return $errorMsg;
        }

        $html = "";
        foreach ($messages as $m) {
            $isMe = ($m['sender_id'] == $this->userId) ? "msg-me" : "msg-other";
            // On affiche le pseudo ou le nom d'affichage au lieu de l'email
            $name = htmlspecialchars($m['display_name'] ?: $m['username']); 
            
            $html .= "<div class='message-item $isMe'>";
            $html .= "<span class='author'>@$name</span>";
            if ($m['content']) $html .= "<p class='content'>" . htmlspecialchars($m['content']) . "</p>";
            if ($m['media_url']) $html .= "<img src='{$m['media_url']}' class='chat-img'>";
            $html .= "<span class='time'>" . date('H:i', strtotime($m['created_at'])) . "</span>";
            $html .= "</div>";
        }
        return $html;
    }

    private function traiterEnvoiMessage()
    {
        $content = htmlspecialchars(trim($_POST['content']));
        try {
            $this->cnxDB->beginTransaction();
            $stmt = $this->cnxDB->prepare("INSERT INTO community_messages (community_id, sender_id, content) VALUES (:cid, :sid, :cnt)");
            $stmt->execute([':cid' => $this->communityId, ':sid' => $this->userId, ':cnt' => $content]);
            $msg_id = $this->cnxDB->lastInsertId();

            if (isset($_FILES['media']) && $_FILES['media']['error'] === 0) {
                $ext = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                $filename = "chat_" . $msg_id . "_" . time() . "." . $ext;
                $dest = 'uploads/' . $filename;
                if (move_uploaded_file($_FILES['media']['tmp_name'], $dest)) {
                    $stmtMedia = $this->cnxDB->prepare("INSERT INTO community_message_media (message_id, media_url, media_type) VALUES (:mid, :url, 'image')");
                    $stmtMedia->execute([':mid' => $msg_id, ':url' => $dest]);
                }
            }
            $this->cnxDB->commit();
        } catch (Exception $e) {
            $this->cnxDB->rollBack();
        }
    }

    private function afficherMembres()
    {
        try {
            $stmt = $this->cnxDB->prepare("
                SELECT u.username, u.display_name, cm.role 
                FROM community_members cm
                JOIN users u ON cm.user_id = u.id
                WHERE cm.community_id = :cid
                ORDER BY cm.role DESC, u.username ASC
            ");
            $stmt->execute([':cid' => $this->communityId]);
            $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $html = "<ul>";
            foreach ($members as $m) {
                $pseudo = htmlspecialchars($m['username']);
                $roleBadge = ($m['role'] === 'admin') ? ' <i class="fas fa-crown" style="color:gold;"></i>' : '';
                $html .= "<li><i class='fas fa-user-circle'></i> $pseudo$roleBadge</li>";
            }
            return $html . "</ul>";
        } catch (PDOException $e) {
            // Table n'existe pas encore
            return "<ul><li style='color:#999;'>Members coming soon...</li></ul>";
        }
    }
}