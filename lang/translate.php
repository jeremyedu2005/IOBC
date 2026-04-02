<?php
// Fonction de traduction automatique avec Google Translate
function translateText($text, $targetLang, $sourceLang = 'auto') {
    // Éviter de traduire les très courts textes
    if (strlen(trim($text)) < 3) {
        return $text;
    }
    
    // Pas de traduction si source et cible sont identiques
    if ($sourceLang === $targetLang) {
        return $text;
    }
    
    // Utiliser Google Translate API (via URL)
    $url = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=' . urlencode($sourceLang) . '&tl=' . urlencode($targetLang) . '&dt=t&q=' . urlencode($text);
    
    try {
        // Utiliser file_get_contents avec contexte stream (sans cURL)
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'header' => "User-Agent: Mozilla/5.0\r\n"
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            // Si l'API échoue, retourner le texte original
            return $text;
        }
        
        // Parser la réponse JSON
        $result = json_decode($response, true);
        
        if (is_array($result) && isset($result[0])) {
            $translated = '';
            foreach ($result[0] as $part) {
                if (is_array($part)) {
                    $translated .= $part[0] ?? '';
                }
            }
            return !empty($translated) ? $translated : $text;
        }
    } catch (Exception $e) {
        return $text;
    }
    
    return $text;
}

// Fonction pour détecter la langue d'un texte
function detectLanguage($text) {
    // Utiliser Google Detect pour détecter la langue
    $url = 'https://translate.googleapis.com/translate_a/element.js?cb=detectLanguageCallback&q=' . urlencode($text);
    
    // Patterns simples pour détecter les langues (optionnel)
    if (preg_match('/[àâäæçéèêëïîôùûüœ]/i', $text)) {
        return 'fr';
    }
    if (preg_match('/[ăăêôơư]/i', $text)) {
        return 'vi';
    }
    if (preg_match('/[çšž]/i', $text)) {
        return 'sq';
    }
    return 'en'; // Par défaut
}

// Utilisation :
// echo translateText('Bonjour', 'en'); // Traduit "Bonjour" en anglais
// echo detectLanguage('Bonjour'); // Détecte que c'est du français
