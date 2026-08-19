<?php

// Lisää tai poistaa tykkäyksen (Toggle)
function toggleLike($conn, $postId, $userId)
{   
    // get funktio,joka hakee tykkäyksen olemassaolon
    $stmt = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $isLiked = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    // Jos käyttäjä on jo tykännyt, poista tykkäys, muuten lisää uusi tykkäys
    if ($isLiked) { 
        $del = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $del->bind_param("ii", $postId, $userId);
        $res = $del->execute();
        $del->close();
        return $res;
    } else {
        $ins = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $ins->bind_param("ii", $postId, $userId);
        $res = $ins->execute();
        $ins->close();
        return $res;
    }
}

// Hakee julkaisun tykkäysten määrän
function getLikeCount($conn, $postId)
{
    
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM likes WHERE post_id = ?");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row['total'] ?? 0;
}

// Tarkistaa onko käyttäjä jo tykännyt julkaisusta
function isPostLikedByUser($conn, $postId, $userId)
{
    if (!$userId) return false;
    $stmt = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $postId, $userId);
    $stmt->execute();
    $liked = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $liked;
}
