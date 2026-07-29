<?php
header('Content-Type: application/json; charset=utf-8');
// POST JSON { "text":"...", "target":"fon|goun", "translation":"..." }
$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!$data || empty($data['text']) || empty($data['target']) || empty($data['translation'])) {
    echo json_encode(['ok'=>false,'error'=>'invalid_request']); exit;
}
$text = trim($data['text']);
$target = strtolower(trim($data['target']));
$translation = trim($data['translation']);
if (!in_array($target, ['fon','goun'])) {
    echo json_encode(['ok'=>false,'error'=>'invalid_target']); exit;
}
$dictPath = __DIR__ . '/includes/dictionary.json';
$dict = [];
if (file_exists($dictPath)) {
    $raw = file_get_contents($dictPath);
    $dict = json_decode($raw, true) ?: [];
}
if (!isset($dict[$target]) || !is_array($dict[$target])) $dict[$target] = [];
$key = mb_strtolower($text);
$dict[$target][$key] = $translation;
if (file_put_contents($dictPath, json_encode($dict, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
    echo json_encode(['ok'=>false,'error'=>'write_failed']); exit;
}
echo json_encode(['ok'=>true]);
