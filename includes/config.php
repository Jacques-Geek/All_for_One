<?php
// includes/config.php — fichier de configuration pour chemins d'outils externes
// Modifiez la valeur 'ffmpeg_cmd' si ffmpeg n'est pas dans le PATH système.
// Exemple Windows : 'C:\\ffmpeg\\bin\\ffmpeg.exe'
$CONFIG = [
    'ffmpeg_cmd' => 'C:\\ffmpeg\\bin\\ffmpeg.exe',
    // Clé API Google Cloud Translate (optionnelle). Laisser vide pour ne pas utiliser l'API payante.
    'google_translate_key' => ''
];

