<?php
// Classe Helper pour la traduction de contenu utilisateur

class ContentTranslator {
    private static $translations = []; // Cache simple
    
    /**
     * Traduit le contenu utilisateur selon la langue actuelle
     * @param string $content Contenu à traduire
     * @param string $sourceLang Langue source (auto pour détection)
     * @param string $targetLang Langue cible (par défaut : langue actuelle)
     * @return string Contenu traduit
     */
    public static function translate($content, $sourceLang = 'auto', $targetLang = null) {
        global $lang;
        $targetLang = $targetLang ?? $lang;
        
        // Clé pour le cache
        $cacheKey = md5($content . $sourceLang . $targetLang);
        
        // Vérifier le cache
        if (isset(self::$translations[$cacheKey])) {
            return self::$translations[$cacheKey];
        }
        
        // Appeler la traduction si la fonction existe
        if (function_exists('translateText')) {
            $translated = translateText($content, $targetLang, $sourceLang);
            self::$translations[$cacheKey] = $translated;
            return $translated;
        }
        
        return $content;
    }
    
    /**
     * Affiche un contenu traduit avec fallback
     */
    public static function display($content, $sourceLang = 'auto') {
        echo htmlspecialchars(self::translate($content, $sourceLang));
    }
}
