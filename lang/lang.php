<?php
// Fonction pour charger la langue
function loadLang($lang = 'fr') {
    $file = __DIR__ . "/$lang.php";
    if (file_exists($file)) {
        return include $file;
    }
    // Fallback français si la langue n'existe pas
    return include __DIR__ . '/fr.php';
}

// Détecte la langue (GET, SESSION, COOKIE, etc.)
function getCurrentLang() {
    if (isset($_GET['lang'])) {
        $_SESSION['lang'] = $_GET['lang'];
        setcookie('lang', $_GET['lang'], time() + 3600 * 24 * 30, '/');
        // Redirection pour prise en compte immédiate
        $url = strtok($_SERVER['REQUEST_URI'], '?');
        header('Location: ' . $url);
        exit;
    }
    if (isset($_SESSION['lang'])) {
        $lang = $_SESSION['lang'];
    } elseif (isset($_COOKIE['lang'])) {
        $lang = $_COOKIE['lang'];
    } else {
        $lang = 'en';
    }
    // Toujours synchroniser session/cookie
    $_SESSION['lang'] = $lang;
    if (!isset($_COOKIE['lang']) || $_COOKIE['lang'] !== $lang) {
        setcookie('lang', $lang, time() + 3600 * 24 * 30, '/');
    }
    return $lang;
}
