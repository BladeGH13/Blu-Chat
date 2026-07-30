<?php
header('Content-Type: application/json');

$user = isset($_GET['user']) ? preg_replace('/[^a-z0-9_]/', '', strtolower($_GET['user'])) : '';
$target = isset($_GET['target']) ? preg_replace('/[^a-z0-9_]/', '', strtolower($_GET['target'])) : '';

if (empty($user) || empty($target)) {
    echo json_encode([]);
    exit;
}

$dir = __DIR__ . '/../data/';
$participants = [$user, $target];
sort($participants);
$filename = $dir . md5($participants[0] . '_' . $participants[1]) . '.json';

if (file_exists($filename)) {
    echo file_get_contents($filename);
} else {
    echo json_encode([]);
}
?>