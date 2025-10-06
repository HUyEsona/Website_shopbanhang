<?php
// send_message.php
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

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['username']) || !isset($data['message'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

$username = trim($data['username']);
$message = trim($data['message']);

if ($username === '' || $message === '') {
    echo json_encode(['success' => false, 'error' => 'Username and message cannot be empty']);
    exit;
}

try {
    $stmt = $pdo->prepare('INSERT INTO messages (username, message, created_at) VALUES (?, ?, NOW())');
    $stmt->execute([$username, $message]);
    echo json_encode(['success' => true]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to save message']);
}
