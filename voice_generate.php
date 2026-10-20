<?php
header('Content-Type: application/json; charset=utf-8');

$input = $_POST['text'] ?? '';
$voiceName = $_POST['voice_name'] ?? 'Zéphyr';
$audioPath = null;

if (empty(trim($input))) {
    echo json_encode([
        'ok' => false,
        'error' => 'Le texte est vide.'
    ]);
    exit;
}

$apiUrl = getenv('VOICE_API_URL') ?: ($_ENV['VOICE_API_URL'] ?? null);
$apiKey = getenv('VOICE_API_KEY') ?: ($_ENV['VOICE_API_KEY'] ?? null);

if (!empty($_FILES['audio']['tmp_name'])) {
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExt = pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION) ?: 'webm';
    $audioPath = $uploadDir . '/voice_' . time() . '_' . uniqid() . '.' . $fileExt;
    move_uploaded_file($_FILES['audio']['tmp_name'], $audioPath);
}

if (!$apiUrl) {
    echo json_encode([
        'ok' => true,
        'demo' => true,
        'message' => 'Aucune API de voix personnalisée configurée. Lecture locale activée en mode démo.'
    ]);
    exit;
}

$ch = curl_init();

$headers = [
    'Accept: application/json',
];

if ($apiKey) {
    $headers[] = 'Authorization: Bearer ' . $apiKey;
}

$postFields = [
    'text' => $input,
    'voice_name' => $voiceName,
];

if ($audioPath) {
    $postFields['audio'] = new CURLFile($audioPath, mime_content_type($audioPath), basename($audioPath));
}

curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => 120,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo json_encode([
        'ok' => false,
        'error' => 'Erreur de connexion à l’API vocale : ' . curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    $decoded = json_decode($response, true);

    if (is_array($decoded) && isset($decoded['audio_url'])) {
        echo json_encode([
            'ok' => true,
            'audio_url' => $decoded['audio_url'],
            'message' => $decoded['message'] ?? 'Voix générée avec succès.'
        ]);
        exit;
    }

    if (is_array($decoded) && isset($decoded['audio'])) {
        $base64 = $decoded['audio'];
        $filename = 'generated_' . time() . '.mp3';
        $directory = __DIR__ . '/generated';
        if (!is_dir($directory)) mkdir($directory, 0777, true);
        $output = $directory . '/' . $filename;
        file_put_contents($output, base64_decode($base64));

        echo json_encode([
            'ok' => true,
            'audio_url' => 'generated/' . $filename,
            'message' => 'Voix générée avec succès.'
        ]);
        exit;
    }

    echo json_encode([
        'ok' => false,
        'error' => 'Réponse API invalide.',
        'raw' => $response
    ]);
    exit;
}

echo json_encode([
    'ok' => false,
    'error' => 'L’API vocale a retourné une erreur HTTP ' . $httpCode,
    'raw' => $response
]);
