<?php
/**
 * VueLogin.class.php
 * Page de connexion - Google OAuth uniquement
 */

class VueLogin
{
    private $cnxDB;
 
    public function __construct()
    {
        $dsn  = "mysql:host=mysql-digbeu.alwaysdata.net;dbname=digbeu_iobc;charset=utf8mb4";
        $user = "digbeu_jeremy";
        $pass = "toto123&*";
 
        try {
            $this->cnxDB = new PDO($dsn, $user, $pass);
            $this->cnxDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
 
    public function __toString()
    {
        return $this->afficheFormulaire();
    }
 
    private function afficheFormulaire()
    {
        return '
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Connexion - KAM</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                .login-container {
                    background: white;
                    border-radius: 24px;
                    padding: 50px 40px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.1);
                    max-width: 450px;
                    width: 100%;
                    text-align: center;
                }
                .logo {
                    font-size: 48px;
                    margin-bottom: 10px;
                }
                .brand {
                    font-size: 32px;
                    font-weight: 800;
                    color: #634444;
                    margin-bottom: 15px;
                }
                .tagline {
                    color: #888;
                    margin-bottom: 40px;
                    font-size: 15px;
                }
                .btn-google {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    gap: 12px;
                    width: 100%;
                    padding: 16px 24px;
                    background: white;
                    border: 2px solid #ddd;
                    border-radius: 12px;
                    font-size: 16px;
                    font-weight: 600;
                    color: #333;
                    cursor: pointer;
                    transition: all 0.3s;
                    text-decoration: none;
                    margin-bottom: 20px;
                }
                .btn-google:hover {
                    border-color: #4285F4;
                    box-shadow: 0 4px 12px rgba(66,133,244,0.2);
                    transform: translateY(-2px);
                }
                .btn-google img {
                    width: 24px;
                    height: 24px;
                }
                .divider {
                    display: flex;
                    align-items: center;
                    margin: 30px 0;
                    color: #999;
                    font-size: 14px;
                }
                .divider::before, .divider::after {
                    content: "";
                    flex: 1;
                    height: 1px;
                    background: #ddd;
                    margin: 0 15px;
                }
                .features {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 20px;
                    margin-top: 40px;
                    padding-top: 40px;
                    border-top: 1px solid #eee;
                }
                .feature {
                    text-align: center;
                }
                .feature i {
                    font-size: 28px;
                    color: #FF7318;
                    margin-bottom: 10px;
                }
                .feature p {
                    font-size: 13px;
                    color: #666;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 14px;
                    color: #888;
                }
                .footer a {
                    color: #FF7318;
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
                <div class="logo">🍳</div>
                <div class="brand">KAM</div>
                <p class="tagline">Partagez vos recettes, découvrez de nouvelles saveurs</p>
                
                <a href="google-login.php" class="btn-google">
                    <svg width="24" height="24" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Continuer avec Google
                </a>
                
                <div class="divider">Sécurisé et rapide</div>
                
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-shield-alt"></i>
                        <p>Connexion sécurisée</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-bolt"></i>
                        <p>Accès instantané</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-users"></i>
                        <p>Rejoignez la communauté</p>
                    </div>
                </div>
                
                <div class="footer">
                    <p>Pas encore de compte ? <a href="index.php?inscription">Créer un compte</a></p>
                </div>
            </div>
        </body>
        </html>
        ';
    }
}
?>