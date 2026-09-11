<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['logged_in' => false, 'unread_count' => 0]);
    exit;
}

require_once __DIR__ . '/../functions/init.php';
$conn = dbConnect();

$userId = (int)$_SESSION['user_id'];
$unreadCount = 0;

if ($conn) {
    $unreadCount = getUnreadNotificationCount($conn, $userId);
}

echo json_encode([
    'logged_in' => true,
    'unread_count' => $unreadCount
]);
