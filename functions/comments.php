<?php

// Lisää kommentin julkaisuun
function addComment($conn, $postId, $userId, $content)
{
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $postId, $userId, $content);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

// Hakee julkaisun kaikki kommentit (kirjoittaja haetaan users-taulusta)
function getCommentsByPost($conn, $postId)
{
    $sql = "SELECT comments.*, users.username AS author 
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            WHERE comments.post_id = ? 
            ORDER BY comments.id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }
    $stmt->close();
    return $comments;
}

// Poistaa kommentin (vain omistaja voi poistaa)
function deleteComment($conn, $commentId, $userId)
{
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $commentId, $userId);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}
