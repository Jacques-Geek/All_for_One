<?php
header('Content-Type: application/json; charset=utf-8');

// Endpoint simple de traduction serveur-side.
// POST JSON { "text": "...", "target": "en|fr|es|fon|goun" }

$body = file_get_contents('php://input');
$data = json_decode($body, true);
// Log request for debugging
@file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] REQ: " . $body . "\n", FILE_APPEND);
if (!$data || empty($data['text']) || empty($data['target'])) {
    echo json_encode(['ok' => false, 'error' => 'invalid_request']);
    exit;
}

$text = trim($data['text']);
$target = strtolower(trim($data['target']));

// Load config safely
$configPath = __DIR__ . '/includes/config.php';
$googleKey = '';
if (file_exists($configPath)) {
    include $configPath; // defines $CONFIG
    if (!empty($CONFIG['google_translate_key'])) $googleKey = $CONFIG['google_translate_key'];
}

// Support for Fon/Goun via local dictionary
if ($target === 'fon' || $target === 'goun') {
    $dictPath = __DIR__ . '/includes/dictionary.json';
    $dict = [];
    if (file_exists($dictPath)) {
        $raw = file_get_contents($dictPath);
        $dict = json_decode($raw, true) ?: [];
    }
    $langDict = isset($dict[$target]) && is_array($dict[$target]) ? $dict[$target] : [];
    $key = mb_strtolower($text);
    if (isset($langDict[$key]) && $langDict[$key] !== '') {
        $resp = ['ok' => true, 'translated' => $langDict[$key], 'engine' => 'dictionary'];
        @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP: " . json_encode($resp, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        echo json_encode($resp);
        exit;
    }
    // not found
    $resp = ['ok' => false, 'error' => 'no_dictionary', 'message' => 'Traduction non trouvée dans le dictionnaire local. Vous pouvez l\'ajouter via save_translation.php'];
    @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP: " . json_encode($resp, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
    echo json_encode($resp);
    exit;
}

// For other languages, try Google Translate if key is present. Use cURL if available for reliability.
function http_post($url, $postFields, $headers = []) {
    // prefer curl
    if (function_exists('curl_version')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        // follow redirects to avoid 301 issues (may be needed for some endpoints)
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);
        if ($res === false) return ['ok' => false, 'error' => 'curl_error', 'message' => $err];
        return ['ok' => true, 'body' => $res];
    }
    // fallback to file_get_contents
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => (empty($headers) ? "Content-Type: application/x-www-form-urlencoded\r\n" : implode("\r\n", $headers) . "\r\n"),
            'content' => $postFields,
            'timeout' => 20
        ]
    ];
    $context = stream_context_create($opts);
    $res = @file_get_contents($url, false, $context);
    if ($res === false) return ['ok' => false, 'error' => 'http_error'];
    return ['ok' => true, 'body' => $res];
}

if ($googleKey) {
    $url = 'https://translation.googleapis.com/language/translate/v2?key=' . urlencode($googleKey);
    $post = http_build_query([
        'q' => $text,
        'target' => $target,
        'format' => 'text'
    ]);
    $r = http_post($url, $post, ["Content-Type: application/x-www-form-urlencoded"]);
    if (!$r['ok']) {
        $resp = ['ok' => false, 'error' => 'service_unavailable', 'detail' => $r];
        @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP: " . json_encode($resp) . "\n", FILE_APPEND);
        echo json_encode($resp);
        exit;
    }
    $j = json_decode($r['body'], true);
    if (isset($j['data']['translations'][0]['translatedText'])) {
        $translated = $j['data']['translations'][0]['translatedText'];
        $resp = ['ok' => true, 'translated' => $translated, 'engine' => 'google'];
        @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP: " . json_encode($resp, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        echo json_encode($resp);
        exit;
    }
    $resp = ['ok' => false, 'error' => 'translate_failed', 'raw' => $j];
    @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP: " . json_encode($resp) . "\n", FILE_APPEND);
    echo json_encode($resp);
    exit;
}

// If no google key, fall back to public google translate endpoint (best-effort).
// Use cURL GET for reliability when possible.
$publicUrl = 'https://translate.googleapis.com/translate_a/single?client=gtx&sl=auto&tl=' . urlencode($target) . '&dt=t&q=' . urlencode($text);
if (function_exists('curl_version')) {
    $ch = curl_init($publicUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    // follow redirects (defensive)
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($res === false) {
        // public endpoint failed - try LibreTranslate as a fallback before returning error
        $libreUrl = 'https://de.libretranslate.com/translate';
        $post = http_build_query(['q' => $text, 'source' => 'auto', 'target' => $target, 'format' => 'text']);
        $lr = http_post($libreUrl, $post, ["Content-Type: application/x-www-form-urlencoded"]);
        if ($lr['ok']) {
            $lj = json_decode($lr['body'], true);
            $libTranslated = '';
            if (is_array($lj) && isset($lj['translatedText'])) $libTranslated = $lj['translatedText'];
            elseif (is_string($lj)) $libTranslated = $lj;
            if ($libTranslated !== '') {
                $resp = ['ok' => true, 'translated' => $libTranslated, 'engine' => 'libre'];
                @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP_LIBRE_FALLBACK: " . json_encode($resp, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
                echo json_encode($resp);
                exit;
            }
        }
        $resp = ['ok' => false, 'error' => 'public_unavailable', 'message' => $err];
        @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP: " . json_encode($resp) . "\n", FILE_APPEND);
        echo json_encode($resp);
        exit;
    }
    $j = json_decode($res, true);
    if (is_array($j) && isset($j[0])) {
        $translated = '';
        foreach ($j[0] as $seg) $translated .= $seg[0];
        // If public endpoint returned same text (no translation), try LibreTranslate as fallback
        $normSrc = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)));
        $normTranslated = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $translated)));
        if ($normSrc === $normTranslated) {
            // attempt LibreTranslate fallback (use regional host to avoid 301)
            $libreUrl = 'https://de.libretranslate.com/translate';
            $post = http_build_query(['q' => $text, 'source' => 'auto', 'target' => $target, 'format' => 'text']);
            $lr = http_post($libreUrl, $post, ["Content-Type: application/x-www-form-urlencoded"]);
            if ($lr['ok']) {
                $lj = json_decode($lr['body'], true);
                $libTranslated = '';
                if (is_array($lj) && isset($lj['translatedText'])) $libTranslated = $lj['translatedText'];
                elseif (is_string($lj)) $libTranslated = $lj;
                if ($libTranslated !== '') {
                    $resp = ['ok' => true, 'translated' => $libTranslated, 'engine' => 'libre'];
                    @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP_LIBRE: " . json_encode($resp, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
                    echo json_encode($resp);
                    exit;
                }
            }
        }
        $resp = ['ok' => true, 'translated' => $translated, 'engine' => 'public'];
        @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP: " . json_encode($resp, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
        echo json_encode($resp);
        exit;
    }
} else {
    $res = @file_get_contents($publicUrl);
    if ($res !== false) {
        $j = json_decode($res, true);
        if (is_array($j) && isset($j[0])) {
            $translated = '';
            foreach ($j[0] as $seg) $translated .= $seg[0];
            // If public endpoint returned same text (no translation), try LibreTranslate fallback here as well
            $normSrc = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $text)));
            $normTranslated = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $translated)));
            if ($normSrc === $normTranslated) {
                // use regional host to avoid 301 redirects
                $libreUrl = 'https://de.libretranslate.com/translate';
                $post = http_build_query(['q' => $text, 'source' => 'auto', 'target' => $target, 'format' => 'text']);
                $lr = http_post($libreUrl, $post, ["Content-Type: application/x-www-form-urlencoded"]);
                if ($lr['ok']) {
                    $lj = json_decode($lr['body'], true);
                    $libTranslated = '';
                    if (is_array($lj) && isset($lj['translatedText'])) $libTranslated = $lj['translatedText'];
                    elseif (is_string($lj)) $libTranslated = $lj;
                    if ($libTranslated !== '') {
                        $resp = ['ok' => true, 'translated' => $libTranslated, 'engine' => 'libre'];
                        @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP_LIBRE: " . json_encode($resp, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
                        echo json_encode($resp);
                        exit;
                    }
                }
            }
            $resp = ['ok' => true, 'translated' => $translated, 'engine' => 'public'];
            @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP: " . json_encode($resp, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);
            echo json_encode($resp);
            exit;
        }
    }
}

 $resp = ['ok' => false, 'error' => 'no_service', 'message' => 'Aucun service de traduction disponible. Configurez une clé Google Translate dans includes/config.php ou ajoutez des traductions locales pour Fon/Goun.'];
 @file_put_contents(__DIR__ . '/translate.log', "[" . date('c') . "] RESP: " . json_encode($resp) . "\n", FILE_APPEND);
 echo json_encode($resp);
