<?php


// Hakee julkaisun tekijän user_id:n
function getPostOwnerId($conn, $postId)
{
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['user_id'] ?? 0);
}

// Luo uuden ilmoituksen (ei luoda, jos käyttäjä tykkää/kommentoi omaa julkaisuaan)
function addNotification($conn, $userId, $actorId, $actorName, $postId, $type, $contentPreview = '')
{
    if (!$userId || !$actorId || $userId === $actorId) {
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO notifications (user_id, actor_id, actor_name, post_id, type, content_preview) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisiss", $userId, $actorId, $actorName, $postId, $type, $contentPreview);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

// Hakee käyttäjän kaikki ilmoitukset
function getUserNotifications($conn, $userId)
{
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
    return $notifications;
}

// Hakee lukemattomien ilmoitusten määrän
function getUnreadNotificationCount($conn, $userId)
{
    if (!$userId) return 0;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['total'] ?? 0);
}

// Merkitsee kaikki käyttäjän ilmoitukset luetuiksi
function markNotificationsAsRead($conn, $userId)
{
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $userId);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}
