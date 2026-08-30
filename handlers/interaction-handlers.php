<?php
/** @var mysqli $conn */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.php";

    // Tykkää / Peruuta tykkäys
    if (isset($_POST["like_post"])) {
        $postId = (int)($_POST["post_id"] ?? 0);
        if ($postId > 0 && $userId > 0) {
            toggleLike($conn, $postId, $userId);
            if (isPostLikedByUser($conn, $postId, $userId)) {
                $postOwnerId = getPostOwnerId($conn, $postId);
                addNotification($conn, $postOwnerId, $userId, $_SESSION['username'] ?? '', $postId, 'like');
            }
            header("Location: " . $redirectUrl);
            exit;
        }
    }
    // Lisää kommentti
    elseif (isset($_POST["add_comment"])) {
        $postId = (int)($_POST["post_id"] ?? 0);
        $content = trim($_POST["comment_content"] ?? "");
        if ($postId > 0 && $userId > 0 && $content !== "") {
            if (addComment($conn, $postId, $userId, $content)) {
                $postOwnerId = getPostOwnerId($conn, $postId);
                $preview = mb_substr($content, 0, 60);
                addNotification($conn, $postOwnerId, $userId, $_SESSION['username'] ?? '', $postId, 'comment', $preview);
            }
            header("Location: " . $redirectUrl);
            exit;
        }
    }
    // Poista kommentti
    elseif (isset($_POST["delete_comment"])) {
        $commentId = (int)($_POST["comment_id"] ?? 0);
        if ($commentId > 0 && $userId > 0) {
            deleteComment($conn, $commentId, $userId);
            header("Location: " . $redirectUrl);
            exit;
        }
    }
}
