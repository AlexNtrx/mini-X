<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['logged_in' => false, 'notifications' => []]);
    exit;
}

require_once __DIR__ . '/../functions/init.php';
$conn = dbConnect();

$userId = (int)$_SESSION['user_id'];
$sinceId = isset($_GET['since_id']) ? (int)$_GET['since_id'] : 0;
$notifications = [];

if ($conn && $userId > 0) {
    $rawNotifications = getNewNotificationsSince($conn, $userId, $sinceId);
    foreach ($rawNotifications as $row) {
        $notifications[] = [
            'id' => (int)$row['id'],
            'actor_name' => $row['actor_name'],
            'post_id' => (int)$row['post_id'],
            'type' => $row['type'],
            'content_preview' => $row['content_preview'] ?? '',
            'created_at' => $row['created_at'],
            'is_read' => (int)$row['is_read']
        ];
    }

    // Koska käyttäjä on ilmoitussivulla ja hakee uudet ilmoitukset näkyviin, merkitään ne luetuiksi
    if (!empty($notifications)) {
        markNotificationsAsRead($conn, $userId);
    }
}

echo json_encode([
    'logged_in' => true,
    'notifications' => $notifications
]);
