<?php
// Start session to check admin login
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Include database connection
include 'db_connection.php';

// Query to get the latest order
$sql = "SELECT order_id FROM orders ORDER BY date_create DESC LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$latest_order = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if there is a new order (You can store the last checked order in session or DB)
if (isset($_SESSION['last_order_id'])) {
    if ($latest_order && $latest_order['order_id'] > $_SESSION['last_order_id']) {
        // New order detected
        $_SESSION['last_order_id'] = $latest_order['order_id'];
        echo json_encode(['new_order' => true, 'order_id' => $latest_order['order_id']]);
    } else {
        // No new order
        echo json_encode(['new_order' => false]);
    }
} else {
    // First time, set the session value
    $_SESSION['last_order_id'] = $latest_order['order_id'];
    echo json_encode(['new_order' => false]);
}
