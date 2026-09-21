<?php

// Lisää kommentin julkaisuun (tukee myös vastauksia eli parent_id)
function addComment($conn, $postId, $userId, $content, $parentId = null)
{
    $content = trim($content);
    if ($content === '') {
        return false;
    }

    $validParentId = null;
    if ($parentId !== null && (int)$parentId > 0) {
        $pId = (int)$parentId;
        $checkStmt = $conn->prepare("SELECT id, parent_id, post_id FROM comments WHERE id = ? LIMIT 1");
        if ($checkStmt) {
            $checkStmt->bind_param("i", $pId);
            $checkStmt->execute();
            $parentRow = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();
            if ($parentRow && (int)$parentRow['post_id'] === (int)$postId) {
                // Rajoitetaan 1-tasoiseen ketjutukseen: jos kohde on jo alikommentti, liitetään sen pääkommenttiin
                $validParentId = !empty($parentRow['parent_id']) ? (int)$parentRow['parent_id'] : (int)$parentRow['id'];
            }
        }
    }

    if ($validParentId !== null) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, parent_id, user_id, content) VALUES (?, ?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("iiis", $postId, $validParentId, $userId, $content);
    } else {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("iis", $postId, $userId, $content);
    }

    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

// Hakee kommentin kirjoittajan user_id:n
function getCommentOwnerId($conn, $commentId)
{
    if (!$conn || $commentId <= 0) return 0;
    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ? LIMIT 1");
    if (!$stmt) return 0;
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['user_id'] ?? 0);
}

// Asettaa tai poistaa parhaan vastauksen (Best Answer)
function toggleBestAnswer($conn, $postId, $commentId, $userId)
{
    if (!$conn || $postId <= 0 || $userId <= 0) return false;

    // Tarkistetaan julkaisun omistaja ja nykyinen valinta
    $stmt = $conn->prepare("SELECT user_id, best_comment_id FROM posts WHERE id = ? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$post || (int)$post['user_id'] !== (int)$userId) {
        return false;
    }

    // Jos sama kommentti on jo valittu, poistetaan valinta (toggle off)
    if ((int)($post['best_comment_id'] ?? 0) === (int)$commentId) {
        $upd = $conn->prepare("UPDATE posts SET best_comment_id = NULL WHERE id = ?");
        if ($upd) {
            $upd->bind_param("i", $postId);
            $upd->execute();
            $upd->close();
        }
        return ['action' => 'unmarked'];
    }

    // Varmistetaan että valittava kommentti kuuluu tähän julkaisuun
    $cStmt = $conn->prepare("SELECT id, user_id FROM comments WHERE id = ? AND post_id = ? LIMIT 1");
    if (!$cStmt) return false;
    $cStmt->bind_param("ii", $commentId, $postId);
    $cStmt->execute();
    $comment = $cStmt->get_result()->fetch_assoc();
    $cStmt->close();

    if (!$comment) {
        return false;
    }

    $upd = $conn->prepare("UPDATE posts SET best_comment_id = ? WHERE id = ?");
    if (!$upd) return false;
    $upd->bind_param("ii", $commentId, $postId);
    $res = $upd->execute();
    $upd->close();

    return [
        'action' => 'marked',
        'comment_owner_id' => (int)$comment['user_id']
    ];
}

// Hakee julkaisun kaikki kommentit puurakenteena (pääkommentit + vastaukset, Best Answer ensin)
function getCommentsByPost($conn, $postId, $bestCommentId = null)
{
    $sql = "SELECT comments.*, 
                   users.username AS author, 
                   COALESCE(NULLIF(users.display_name, ''), users.username) AS author_display_name,
                   users.avatar AS author_avatar 
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            WHERE comments.post_id = ? 
            ORDER BY comments.id ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $allComments = [];
    while ($row = $result->fetch_assoc()) {
        $allComments[] = $row;
    }
    $stmt->close();

    $parents = [];
    $repliesByParent = [];

    foreach ($allComments as $c) {
        $c['replies'] = [];
        if (empty($c['parent_id'])) {
            $parents[$c['id']] = $c;
        } else {
            $repliesByParent[$c['parent_id']][] = $c;
        }
    }

    // Kiinnitetään alikommentit (replies) pääkommentteihin
    foreach ($repliesByParent as $pId => $subList) {
        if (isset($parents[$pId])) {
            $parents[$pId]['replies'] = $subList;
        } else {
            // Jos vanhempaa ei löydy, näytetään omana kommenttinaan
            foreach ($subList as $orphan) {
                $parents[$orphan['id']] = $orphan;
            }
        }
    }

    // Jos bestCommentId on asetettu, siirretään kyseinen pääkommentti kärkeen
    if ($bestCommentId !== null && (int)$bestCommentId > 0 && isset($parents[(int)$bestCommentId])) {
        $best = $parents[(int)$bestCommentId];
        unset($parents[(int)$bestCommentId]);
        $parents = [(int)$bestCommentId => $best] + $parents;
    }

    return array_values($parents);
}

// Poistaa kommentin (ja sen alikommentit) sekä päivittää tarvittaessa julkaisun best_comment_id
function deleteComment($conn, $commentId, $userId)
{
    // Nollataan mahdollinen best_comment_id viittaus
    $upd = $conn->prepare("UPDATE posts SET best_comment_id = NULL WHERE best_comment_id = ?");
    if ($upd) {
        $upd->bind_param("i", $commentId);
        $upd->execute();
        $upd->close();
    }

    // Poistetaan myös alikommentit
    $delSub = $conn->prepare("DELETE FROM comments WHERE parent_id = ?");
    if ($delSub) {
        $delSub->bind_param("i", $commentId);
        $delSub->execute();
        $delSub->close();
    }

    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ii", $commentId, $userId);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}
