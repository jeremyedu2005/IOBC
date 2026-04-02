<?php
/**
 * VueFormulaire.class.php
 * Page d'inscription + traitement sécurisé
 */
require_once(__DIR__ . '/../config.php');

class VueFormulaire
{
    private $cnxDB;
    private $errors = [];
 
    public function __construct()
    {
        $dsn  = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
        $user = DB_USER;
        $pass = DB_PASS;
 
        try {
            $this->cnxDB = new PDO($dsn, $user, $pass);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
 
    public function __toString()
    {
        // Si le formulaire est soumis, on traite
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ok'])) {
            $result = $this->traiterInscription();
            if ($result === true) {
                // Inscription réussie → redirection vers login
                header("Location: index.php?login&registered=1");
                exit;
            } else {
                // Erreurs → on les stocke pour affichage
                $this->errors = $result;
            }
        }
        
        return $this->afficherFormulaire();
    }
    
    /**
     * Valide et traite l'inscription
     * @return bool|array true si succès, array d'erreurs sinon
     */
    private function traiterInscription()
    {
        $errors = [];
        
        // 1. Récupération et nettoyage des données
        $username = trim($_POST['username'] ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $birth_date = $_POST['birth_date'] ?? '';
        $cgu = isset($_POST['cgu']);
        
        // 2. Validations
        if (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = "Le pseudo doit contenir entre 3 et 50 caractères.";
        }
        
        if (empty($display_name)) {
            $errors['display_name'] = "Le nom affiché est obligatoire.";
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Adresse email invalide.";
        }
        
        if (strlen($password) < 8) {
            $errors['password'] = "Le mot de passe doit contenir au moins 8 caractères.";
        }
        
        if (empty($birth_date)) {
            $errors['birth_date'] = "Date de naissance requise.";
        } else {
            // Vérifier âge minimum (13 ans)
            $age = date_diff(date_create($birth_date), date_create('today'))->y;
            if ($age < 13) {
                $errors['birth_date'] = "Vous devez avoir au moins 13 ans.";
            }
        }
        
        if (!$cgu) {
            $errors['cgu'] = "Vous devez accepter les conditions d'utilisation.";
        }
        
        // 3. Vérifications BDD (unicité)
        if (empty($errors)) {
            // Vérifier pseudo unique
            $stmt = $this->cnxDB->prepare("SELECT id FROM users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            if ($stmt->fetch()) {
                $errors['username'] = "Ce pseudo est déjà utilisé.";
            }
            
            // Vérifier email unique
            $stmt = $this->cnxDB->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $errors['email'] = "Cet email est déjà inscrit.";
            }
        }
        
        // 4. Si erreurs → retour tableau
        if (!empty($errors)) {
            return $errors;
        }
        
        // 5. Insertion en BDD
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->cnxDB->prepare("
                INSERT INTO users (
                    username, display_name, email, password_hash, 
                    birth_date, cgu_accepted_at, karma_score
                ) VALUES (
                    :username, :display_name, :email, :password_hash, 
                    :birth_date, NOW(), 0
                )
            ");
            
            $stmt->execute([
                ':username' => $username,
                ':display_name' => $display_name,
                ':email' => $email,
                ':password_hash' => $password_hash,
                ':birth_date' => $birth_date
            ]);
            
            return true; // Succès
            
        } catch (PDOException $e) {
            error_log("Erreur inscription : " . $e->getMessage());
            return ['general' => "Une erreur est survenue. Veuillez réessayer."];
        }
    }
    
    /**
     * Affiche le formulaire HTML
     */
    private function afficherFormulaire()
    {
        // Valeurs précédentes pour re-remplir le formulaire en cas d'erreur
        $old = [
            'username' => htmlspecialchars($_POST['username'] ?? ''),
            'display_name' => htmlspecialchars($_POST['display_name'] ?? ''),
            'email' => htmlspecialchars($_POST['email'] ?? ''),
            'birth_date' => $_POST['birth_date'] ?? ''
        ];
        
        // Fonction helper pour afficher les erreurs
        $error = function($field) {
    return isset($this->errors[$field]) 
        ? '<span class="error">' . htmlspecialchars($this->errors[$field]) . '</span>' 
        : '';
};
        
        ob_start();
        ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(Security::generateCSRFToken()); ?>">
    <title>Inscription - claque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #FFE8CC 0%, #FFD4A3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .form-container {
            background: white;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        .logo { text-align: center; font-size: 48px; margin-bottom: 10px; }
        .brand { text-align: center; font-size: 28px; font-weight: 800; color: #634444; margin-bottom: 5px; }
        .tagline { text-align: center; color: #888; margin-bottom: 30px; font-size: 14px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; margin-bottom: 6px; font-weight: 600; color: #634444; font-size: 14px;
        }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="date"] {
            width: 100%; padding: 12px 15px; border: 2px solid #FFE8CC;
            border-radius: 10px; font-size: 14px; outline: none; transition: border-color 0.3s;
        }
        .form-group input:focus { border-color: #FF7318; }
        .form-group input.error { border-color: #D81B60; }
        
        .error { color: #D81B60; font-size: 12px; margin-top: 5px; display: block; }
        .error-general {
            background: #FCE4EC; color: #D81B60; padding: 12px;
            border-radius: 8px; margin-bottom: 20px; font-size: 14px;
        }
        
        .checkbox-group {
            display: flex; align-items: flex-start; gap: 10px; margin: 25px 0;
        }
        .checkbox-group input { margin-top: 3px; }
        .checkbox-group label { font-size: 13px; color: #666; line-height: 1.4; }
        .checkbox-group a { color: #FF7318; text-decoration: none; }
        
        .btn-submit {
            width: 100%; padding: 14px; background: #FF7318; color: white;
            border: none; border-radius: 12px; font-size: 16px; font-weight: 600;
            cursor: pointer; transition: all 0.3s; margin-top: 10px;
        }
        .btn-submit:hover { background: #E65A0C; transform: translateY(-2px); }
        
        .divider {
            display: flex; align-items: center; margin: 25px 0; color: #999; font-size: 13px;
        }
        .divider::before, .divider::after {
            content: ""; flex: 1; height: 1px; background: #ddd; margin: 0 15px;
        }
        
        .btn-google {
            display: flex; align-items: center; justify-content: center; gap: 12px;
            width: 100%; padding: 12px; background: white; border: 2px solid #ddd;
            border-radius: 12px; font-size: 14px; font-weight: 600; color: #333;
            cursor: pointer; transition: all 0.3s; text-decoration: none;
        }
        .btn-google:hover { border-color: #4285F4; box-shadow: 0 4px 12px rgba(66,133,244,0.2); }
        
        .footer { text-align: center; margin-top: 25px; font-size: 14px; color: #888; }
        .footer a { color: #FF7318; text-decoration: none; font-weight: 600; }
        
        .password-strength {
            height: 4px; background: #eee; border-radius: 2px; margin-top: 8px; overflow: hidden;
        }
        .password-strength-bar {
            height: 100%; width: 0%; transition: all 0.3s; border-radius: 2px;
        }
        .strength-weak { background: #D81B60; width: 33%; }
        .strength-medium { background: #FFA726; width: 66%; }
        .strength-strong { background: #2ECC71; width: 100%; }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="logo" style="display: flex; align-items: center; justify-content: center;">
            <img src="assets/logo.png" alt="logo" style="width: 60px; height: 60px; object-fit: contain;">
            <img src="assets/claque.png" alt="claque" style="width: 60px; height: 60px; object-fit: contain; margin-left: 10px;">
        </div>
        <p class="tagline">Rejoignez la communauté culinaire</p>
        
        <?php if (isset($this->errors['general'])): ?>
            <div class="error-general">
                <i class="fas fa-exclamation-circle"></i> <?= $this->errors['general'] ?>
            </div>
        <?php endif; ?>
        
        <form action="" method="post" class="inscription-form" id="registerForm">
            <div class="form-group">
                <label for="usernameField">Pseudo *</label>
                <input type="text" id="usernameField" name="username" 
                       value="<?= $old['username'] ?>" 
                       placeholder="Choisissez un pseudo unique" 
                       required <?= isset($this->errors['username']) ? 'class="error"' : '' ?>>
                <?= $error('username') ?>
            </div>
 
            <div class="form-group">
                <label for="displayNameField">Nom affiché *</label>
                <input type="text" id="displayNameField" name="display_name"
                       value="<?= $old['display_name'] ?>"
                       placeholder="Votre prénom ou nom complet"
                       required <?= isset($this->errors['display_name']) ? 'class="error"' : '' ?>>
                <?= $error('display_name') ?>
            </div>
 
            <div class="form-group">
                <label for="emailField">Email *</label>
                <input type="email" id="emailField" name="email"
                       value="<?= $old['email'] ?>"
                       placeholder="Votre adresse email"
                       required <?= isset($this->errors['email']) ? 'class="error"' : '' ?>>
                <?= $error('email') ?>
            </div>
 
            <div class="form-group">
                <label for="passwordField">Mot de passe *</label>
                <input type="password" id="passwordField" name="password"
                       placeholder="Au moins 8 caractères"
                       required <?= isset($this->errors['password']) ? 'class="error"' : '' ?>>
                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
                <?= $error('password') ?>
            </div>
 
            <div class="form-group">
                <label for="birthDateField">Date de naissance *</label>
                <input type="date" id="birthDateField" name="birth_date"
                       value="<?= $old['birth_date'] ?>"
                       required <?= isset($this->errors['birth_date']) ? 'class="error"' : '' ?>>
                <?= $error('birth_date') ?>
            </div>
 
            <div class="checkbox-group">
                <input type="checkbox" id="cguField" name="cgu" required>
                <label for="cguField">
                    J'accepte les <a href="index.php?mentions-legales" target="_blank">conditions d'utilisation</a> *
                </label>
            </div>
            <?= $error('cgu') ?>
 
            <button type="submit" name="ok" class="btn-submit">Créer mon compte</button>
        </form>
        
        <div class="divider">ou</div>
        
        <a href="google-login.php" class="btn-google">
            <svg width="20" height="20" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Continuer avec Google
        </a>
        
        <div class="footer">
            <p>Déjà un compte ? <a href="index.php?login">Se connecter</a></p>
        </div>
    </div>
    
    <script>
        // Validation mot de passe en temps réel
        const passwordInput = document.getElementById('passwordField');
        const strengthBar = document.getElementById('strengthBar');
        
        passwordInput.addEventListener('input', function() {
            const pwd = this.value;
            let strength = 0;
            
            if (pwd.length >= 8) strength++;
            if (pwd.match(/[a-z]/) && pwd.match(/[A-Z]/)) strength++;
            if (pwd.match(/[0-9]/) || pwd.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength-bar';
            if (strength === 1) strengthBar.classList.add('strength-weak');
            else if (strength === 2) strengthBar.classList.add('strength-medium');
            else if (strength === 3) strengthBar.classList.add('strength-strong');
        });
        
        // Empêcher la soumission si erreurs côté client
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const pwd = passwordInput.value;
            if (pwd.length < 8) {
                e.preventDefault();
                alert('Le mot de passe doit contenir au moins 8 caractères.');
                passwordInput.focus();
            }
        });
    </script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}
?>