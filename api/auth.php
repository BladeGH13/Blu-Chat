<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$bluId = preg_replace('/[^a-z0-9_]/', '', strtolower($data['bluId'] ?? ''));
$password = $data['password'] ?? '';

if (empty($bluId) || empty($password)) {
    http_response_code(400);
    exit(json_encode(['error' => 'Blu ID and password are required.']));
}

$dir = __DIR__ . '/data/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$dbPath = $dir . 'users.sql';
$db = new SQLite3($dbPath);

$db->exec("CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    blu_id TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    created_at INTEGER NOT NULL
)");

$stmt = $db->prepare("SELECT * FROM users WHERE blu_id = :blu_id");
$stmt->bindValue(':blu_id', $bluId, SQLITE3_TEXT);
$result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if ($result) {
    if ($result['password'] === $password) {
        echo json_encode(['success' => true, 'message' => 'Login successful', 'id' => $result['id']]);
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Incorrect password.']);
    }
} else {
    $insert = $db->prepare("INSERT INTO users (blu_id, password, created_at) VALUES (:blu_id, :password, :time)");
    $insert->bindValue(':blu_id', $bluId, SQLITE3_TEXT);
    $insert->bindValue(':password', $password, SQLITE3_TEXT);
    $insert->bindValue(':time', time(), SQLITE3_INTEGER);
    
    if ($insert->execute()) {
        $newId = $db->lastInsertRowID();
        echo json_encode(['success' => true, 'message' => 'Account created successfully', 'id' => $newId]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed.']);
    }
}
?>
