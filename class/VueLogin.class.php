<?php
/**
 * VueLogin.class.php
 * Gestion de la connexion utilisateur
 */
require_once(__DIR__ . '/../config.php');

class VueLogin
{
    private $cnxDB;
    private $errors = [];
    private $user = null;

    public function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->cnxDB = new PDO($dsn, DB_USER, DB_PASS);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $this->errors[] = "Erreur de connexion à la base de données";
        }
    }

    public function __toString()
    {
        // Traitement du login en POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
            $this->traiterLogin();
        }
        
        return $this->afficherFormulaire();
    }

    private function traiterLogin()
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->errors[] = "Veuillez remplir tous les champs";
            return;
        }

        try {
            $stmt = $this->cnxDB->prepare("SELECT id, username, display_name, email, password_hash FROM users WHERE username = :username OR email = :email LIMIT 1");
            $stmt->execute([':username' => $username, ':email' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['display_name'] = $user['display_name'];
                $_SESSION['user_email'] = $user['email'];
                
                // Redirection vers accueil connecté
                header("Location: index.php?accueil");
                exit;
            } else {
                $this->errors[] = "Identifiants invalides";
            }
        } catch (PDOException $e) {
            $this->errors[] = "Erreur lors de la connexion";
        }
    }

    private function afficherFormulaire()
    {
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Security::generateCSRFToken()); ?>">
    <title>Connexion - claque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        :root {
            --cream: #FFE8CC;
            --orange: #FF7318;
            --pink: #D81B60;
            --blue: #4A90E2;
            --brown: #634444;
            --white: #FFFFFF;
            --gray-light: #F5F5F5;
            --shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--cream);
            color: var(--brown);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: var(--white);
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow);
            max-width: 420px;
            width: 100%;
        }
        .logo {
            font-size: 40px;
            text-align: center;
            margin-bottom: 20px;
        }
        .brand {
            text-align: center;
            font-size: 28px;
            font-weight: 800;
            color: var(--brown);
            margin-bottom: 8px;
        }
        .tagline {
            text-align: center;
            color: var(--gray-light);
            margin-bottom: 30px;
            font-size: 14px;
        }
        .error-message {
            background: #FFE8E8;
            border: 1px solid #FF6B6B;
            color: #C92A2A;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--brown);
        }
        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus {
            outline: none;
            border-color: var(--orange);
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--orange), var(--pink));
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 115, 24, 0.3);
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .footer a {
            color: var(--orange);
            text-decoration: none;
            font-weight: 600;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo" style="display: flex; align-items: center; justify-content: center;">
            <img src="assets/logo.png" alt="logo" style="width: 60px; height: 60px; object-fit: contain;">
            <img src="assets/claque.png" alt="claque" style="width: 60px; height: 60px; object-fit: contain; margin-left: 10px;">
        </div>
        <p class="tagline">Connectez-vous à la communauté</p>

        <?php if (!empty($this->errors)): ?>
            <?php foreach ($this->errors as $error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Pseudo ou Email</label>
                <input type="text" id="username" name="username" required placeholder="Votre pseudo">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required placeholder="Votre mot de passe">
            </div>

            <button type="submit" name="login_submit">Se connecter</button>
        </form>

        <div class="footer">
            <p>Pas encore de compte ? <a href="index.php?inscription">Créer un compte</a></p>
            <p style="margin-top: 10px;"><a href="index.php?forgot">Mot de passe oublié ?</a></p>
        </div>
    </div>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
