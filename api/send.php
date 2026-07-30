<?php
header('Content-Type: application/json');

// Accept raw POST payload data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['sender'], $data['target'], $data['text'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required payload parameters.']);
    exit;
}

$sender = preg_replace('/[^a-z0-9_]/', '', strtolower($data['sender']));
$target = preg_replace('/[^a-z0-9_]/', '', strtolower($data['target']));
$text = trim($data['text']);
$timestamp = isset($data['timestamp']) ? intval($data['timestamp']) : time() * 1000;

if (empty($sender) || empty($target) || empty($text)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters.']);
    exit;
}

$dir = __DIR__ . '/../data/';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// Create a unique conversation file name combining both sorted user IDs
$participants = [$sender, $target];
sort($participants);
$filename = $dir . md5($participants[0] . '_' . $participants[1]) . '.json';

$messages = [];
if (file_exists($filename)) {
    $json = file_get_contents($filename);
    $messages = json_decode($json, true) ?: [];
}

// Append new message
$messages[] = [
    'sender' => $sender,
    'target' => $target,
    'text' => $text,
    'timestamp' => $timestamp
];

// Save back to JSON store
file_put_contents($filename, json_encode($messages));

echo json_encode(['success' => true]);
?>