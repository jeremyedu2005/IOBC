<?php
require_once(__DIR__ . '/../config.php');

class VueAgenda
{
    private $cnxDB;
    private $userId;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?login");
            exit;
        }
        $this->userId = $_SESSION['user_id'];

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
        $message = "";
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_event'])) {
            $message = $this->traiterCreationEvenement();
        }

        // Charger les traductions
        $lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr';
        require_once(__DIR__ . '/../lang/lang.php');
        $tr = loadLang($lang) ?? [];

        // Données des communautés tendance
        $communautes = [
            ['nom' => 'AlbanianKitchen', 'membres' => '57K'],
            ['nom' => 'VietnameseStreetFood', 'membres' => '142K'],
            ['nom' => 'FrenchPastry', 'membres' => '98K'],
            ['nom' => 'MediterraneanCuisine', 'membres' => '76K'],
            ['nom' => 'VeganRecipes', 'membres' => '130K'],
        ];

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
            <meta name="csrf-token" content="<?php echo htmlspecialchars(Security::generateCSRFToken()); ?>">
            <title>Agenda - claque</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
            <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
            <link rel="stylesheet" href="css/style.css">
            <link rel="stylesheet" href="css/responsive.css">
            <link rel="stylesheet" href="css/agenda.css">
            <style>
                body {
                    background-color: #FEF5F1;
                    font-family: 'Cabin', sans-serif;
                }
                h1, h2, h3, h4, h5, h6 {
                    font-family: 'Baloo', cursive;
                }
                .section-title {
                    color: #F86015;
                }
                .nav-link.active {
                    color: #F86015;
                    border-left: 3px solid #F86015;
                }
                .btn-primary {
                    background-color: #F86015;
                    color: white;
                }
                .btn-primary:hover {
                    background-color: #e54a0e;
                }
                
                /* Calendar Improvements */
                .fc {
                    font-family: 'Cabin', sans-serif;
                }
                
                #calendar {
                    background: white;
                    border-radius: 12px;
                    padding: 15px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
                }
                
                .fc-button-primary {
                    background-color: #F86015 !important;
                    border-color: #F86015 !important;
                }
                
                .fc-button-primary:hover {
                    background-color: #E65A0C !important;
                }
                
                .fc-button-primary.fc-button-active {
                    background-color: #D83810 !important;
                }
                
                .fc-col-header-cell {
                    padding: 10px 2px !important;
                    background-color: #FEF5F1 !important;
                    font-weight: 600 !important;
                    color: #F86015 !important;
                }
                
                .fc-daygrid-day {
                    height: 100px !important;
                }
                
                .fc-daygrid-day-number {
                    padding: 6px 4px !important;
                    font-weight: 600 !important;
                }
                
                .fc-event {
                    padding: 2px 4px !important;
                    font-size: 12px !important;
                }
                
                .fc-daygrid-day-frame {
                    min-height: 100px !important;
                }
            </style>
        </head>
        <body>
            <!-- Header -->
            <?php require_once(__DIR__ . '/../inc/header.inc.php'); ?>

            <div class="main-container">
                <!-- Left Sidebar -->
                <?php require_once(__DIR__ . '/../inc/sidebar-left.inc.php'); ?>

                <!-- Main Content -->
                <main class="content">
                    <div class="section-header">
                        <h2 class="section-title"><i class="fas fa-calendar"></i> <?= isset($tr['agenda']) ? $tr['agenda'] : 'Agenda' ?></h2>
                    </div>

                    <div class="agenda-wrapper">
                        <div class="agenda-sidebar">
                            <h3 style="margin-top:0; color: #F86015;">📅 Planifier</h3>
                            <?= $message ?>
                            <?= $this->afficherFormulaire() ?>
                        </div>

                        <div class="agenda-main">
                            <div id='calendar'></div>
                        </div>
                    </div>
                </main>

                <!-- Right Sidebar -->
                <?php require_once(__DIR__ . '/../inc/sidebar-right.inc.php'); ?>
            </div>

            <!-- Bottom Navigation (Mobile) -->
            <nav class="bottom-nav">
                <div class="bottom-nav-items">
                    <a href="index.php?accueil" class="bottom-nav-item">
                        <i class="fas fa-home"></i>
                        <span><?= isset($tr['home']) ? $tr['home'] : 'Home' ?></span>
                    </a>
                    <a href="index.php?agenda" class="bottom-nav-item active">
                        <i class="fas fa-calendar"></i>
                        <span><?= isset($tr['agenda']) ? $tr['agenda'] : 'Agenda' ?></span>
                    </a>
                    <a href="index.php?communautes" class="bottom-nav-item">
                        <i class="fas fa-users"></i>
                        <span><?= isset($tr['communities']) ? $tr['communities'] : 'Communities' ?></span>
                    </a>
                    <a href="index.php?messages" class="bottom-nav-item">
                        <i class="fas fa-comment"></i>
                        <span><?= isset($tr['messages']) ? $tr['messages'] : 'Messages' ?></span>
                    </a>
                    <a href="index.php?profile" class="bottom-nav-item">
                        <i class="fas fa-user"></i>
                        <span><?= isset($tr['profile']) ? $tr['profile'] : 'Profile' ?></span>
                    </a>
                </div>
            </nav>

            <!-- Footer -->
            <?php require_once(__DIR__ . '/../inc/footer.inc.php'); ?>

            <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var calendarEl = document.getElementById('calendar');
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        locale: 'fr',
                        contentHeight: 'auto',
                        height: 'auto',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,listWeek'
                        },
                        events: 'index.php?agenda_events',
                        eventColor: '#F86015',
                        eventTextColor: '#fff',
                        eventBorderColor: '#E65A0C',
                        navLinks: true,
                        selectable: true,
                        displayEventTime: true,
                        dayHeaderClassNames: 'fc-day-header-custom',
                        eventClassNames: 'fc-event-custom',
                        eventDisplay: 'block'
                    });
                    calendar.render();
                });
            </script>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    private function afficherFormulaire()
    {
        return '
        <form action="" method="post" style="font-size: 0.9em;">
            <div class="form-group" style="margin-bottom:10px;">
                <label>Titre :</label>
                <input type="text" name="title" required style="width:100%; padding:8px; border-radius:5px; border:1px solid #ddd;">
            </div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>Début :</label>
                <input type="datetime-local" name="start_time" required style="width:100%; padding:8px; border-radius:5px; border:1px solid #ddd;">
            </div>
            <div class="form-group" style="margin-bottom:10px;">
                <label>Fin :</label>
                <input type="datetime-local" name="end_time" required style="width:100%; padding:8px; border-radius:5px; border:1px solid #ddd;">
            </div>
            <div class="form-group" style="margin-bottom:15px;">
                <label>Type :</label>
                <select name="event_type" style="width:100%; padding:8px; border-radius:5px; border:1px solid #ddd;">
                    <option value="private">Privé</option>
                    <option value="public">Public</option>
                </select>
            </div>
            <button type="submit" name="create_event" class="btn-submit">Ajouter</button>
        </form>';
    }

    private function traiterCreationEvenement() {
        // ... (Gardez votre logique SQL identique) ...
        $title = htmlspecialchars(trim($_POST['title']));
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];
        $type = $_POST['event_type'];
        try {
            $stmt = $this->cnxDB->prepare("INSERT INTO events (organizer_id, title, start_time, end_time, event_type) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$this->userId, $title, $start, $end, $type]);
            return "<p style='color:green; font-size:0.8em;'>Ajouté !</p>";
        } catch(Exception $e) { return "<p style='color:red;'>Erreur</p>"; }
    }

    public function getEventsJson() {
        $stmt = $this->cnxDB->prepare("SELECT id, title, start_time as start, end_time as end FROM events WHERE organizer_id = ?");
        $stmt->execute([$this->userId]);
        return json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}