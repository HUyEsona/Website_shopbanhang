<?php
// get_messages.php
header('Content-Type: application/json');

$host = 'localhost';
$db   = 'cuoiky';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

try {
    $stmt = $pdo->query('SELECT username, message, created_at FROM messages ORDER BY created_at ASC');
    $messages = $stmt->fetchAll();
    echo json_encode(['success' => true, 'messages' => $messages]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to fetch messages']);
}
