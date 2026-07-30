<?php
header('Content-Type: application/json');
$dir = __DIR__ . '/data/';
if (!is_dir($dir)) mkdir($dir, 0777, true);

$dbPath = $dir . 'groups.sql';
$db = new SQLite3($dbPath);

$db->exec("CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room TEXT NOT NULL,
    sender TEXT NOT NULL,
    text TEXT NOT NULL,
    timestamp INTEGER NOT NULL
)");

$db->exec("CREATE TABLE IF NOT EXISTS group_members (
    group_name TEXT NOT NULL,
    member TEXT NOT NULL,
    PRIMARY KEY(group_name, member)
)");

$db->exec("CREATE TABLE IF NOT EXISTS typing (
    room TEXT PRIMARY KEY,
    sender TEXT,
    updated_at INTEGER
)");

$action = $_GET['action'] ?? '';

if ($action === 'creategroup') {
    $data = json_decode(file_get_contents('php://input'), true);
    $group = preg_replace('/[^a-z0-9_]/', '', strtolower($data['group'] ?? ''));
    $creator = preg_replace('/[^a-z0-9_]/', '', strtolower($data['creator'] ?? ''));
    
    if (!empty($group)) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO group_members (group_name, member) VALUES (:g, :m)");
        $stmt->bindValue(':g', $group, SQLITE3_TEXT);
        $stmt->bindValue(':m', $creator, SQLITE3_TEXT);
        $stmt->execute();
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'addmember') {
    $data = json_decode(file_get_contents('php://input'), true);
    $group = preg_replace('/[^a-z0-9_]/', '', strtolower($data['group'] ?? ''));
    $member = preg_replace('/[^a-z0-9_]/', '', strtolower($data['member'] ?? ''));
    
    if (!empty($group) && !empty($member)) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO group_members (group_name, member) VALUES (:g, :m)");
        $stmt->bindValue(':g', $group, SQLITE3_TEXT);
        $stmt->bindValue(':m', $member, SQLITE3_TEXT);
        $stmt->execute();
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'getmembers') {
    $group = preg_replace('/[^a-z0-9_]/', '', strtolower($_GET['group'] ?? ''));
    $stmt = $db->prepare("SELECT member FROM group_members WHERE group_name = :g");
    $stmt->bindValue(':g', $group, SQLITE3_TEXT);
    $res = $stmt->execute();
    
    $members = [];
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $members[] = $row['member'];
    }
    echo json_encode($members);
    exit;
}

if ($action === 'typing') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sender = $data['sender'] ?? '';
    $target = $data['target'] ?? '';
    $isGroup = $data['isGroup'] ?? false;
    
    $room = $isGroup ? $target : (function($u1, $u2) {
        $p = [$u1, $u2]; sort($p); return 'dm_' . md5($p[0] . '_' . $p[1]);
    })($sender, $target);

    $stmt = $db->prepare("INSERT OR REPLACE INTO typing (room, sender, updated_at) VALUES (:room, :sender, :time)");
    $stmt->bindValue(':room', $room, SQLITE3_TEXT);
    $stmt->bindValue(':sender', $sender, SQLITE3_TEXT);
    $stmt->bindValue(':time', time(), SQLITE3_INTEGER);
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'send') {
    $data = json_decode(file_get_contents('php://input'), true);
    $sender = preg_replace('/[^a-z0-9_]/', '', strtolower($data['sender'] ?? ''));
    $target = preg_replace('/[^a-z0-9_]/', '', strtolower($data['target'] ?? ''));
    $text = trim($data['text'] ?? '');
    $isGroup = $data['isGroup'] ?? false;
    $timestamp = intval($data['timestamp'] ?? time() * 1000);

    if (empty($sender) || empty($target) || empty($text)) exit(json_encode(['error' => 'Bad request']));

    $room = $isGroup ? $target : (function($u1, $u2) {
        $p = [$u1, $u2]; sort($p); return 'dm_' . md5($p[0] . '_' . $p[1]);
    })($sender, $target);

    $stmt = $db->prepare("INSERT INTO messages (room, sender, text, timestamp) VALUES (:room, :sender, :text, :timestamp)");
    $stmt->bindValue(':room', $room, SQLITE3_TEXT);
    $stmt->bindValue(':sender', $sender, SQLITE3_TEXT);
    $stmt->bindValue(':text', $text, SQLITE3_TEXT);
    $stmt->bindValue(':timestamp', $timestamp, SQLITE3_INTEGER);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'fetchdirect' || $action === 'fetchgroup') {
    $targetKey = $_GET['group'] ?? '';
    if ($action === 'fetchdirect') {
        $user = $_GET['user'] ?? '';
        $target = $_GET['target'] ?? '';
        $p = [$user, $target]; sort($p);
        $targetKey = 'dm_' . md5($p[0] . '_' . $p[1]);
    }

    $stmt = $db->prepare("SELECT sender, text, timestamp FROM messages WHERE room = :room ORDER BY id ASC");
    $stmt->bindValue(':room', $targetKey, SQLITE3_TEXT);
    $res = $stmt->execute();

    $messages = [];
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $messages[] = $row;
    }

    $typingUser = null;
    $tStmt = $db->prepare("SELECT sender, updated_at FROM typing WHERE room = :room");
    $tStmt->bindValue(':room', $targetKey, SQLITE3_TEXT);
    $tRes = $tStmt->execute()->fetchArray(SQLITE3_ASSOC);
    if ($tRes && (time() - $tRes['updated_at'] < 4)) {
        $typingUser = $tRes['sender'];
    }

    echo json_encode(['messages' => $messages, 'typing' => $typingUser]);
    exit;
}
?>
