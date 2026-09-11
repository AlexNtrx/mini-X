<?php
/** @var mysqli $conn */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.php";
    $cleanUrl = preg_replace('/#.*$/', '', $redirectUrl);

    // Tykkää / Peruuta tykkäys
    if (isset($_POST["like_post"])) {
        $postId = (int)($_POST["post_id"] ?? 0);
        if ($postId > 0 && $userId > 0) {
            toggleLike($conn, $postId, $userId);
            $postOwnerId = getPostOwnerId($conn, $postId);
            if (isPostLikedByUser($conn, $postId, $userId)) {
                addNotification($conn, $postOwnerId, $userId, $_SESSION['username'] ?? '', $postId, 'like');
            } else {
                removeNotification($conn, $postOwnerId, $userId, $postId, 'like');
            }
            header("Location: " . $cleanUrl . "#post-" . $postId);
            exit;
        }
    }
    // Lisää kommentti
    elseif (isset($_POST["add_comment"])) {
        $postId = (int)($_POST["post_id"] ?? 0);
        $content = trim($_POST["comment_content"] ?? "");
        if ($postId > 0 && $userId > 0) {
            if ($content === "") {
                $error = "Kommentti ei voi olla tyhjä.";
            } elseif (mb_strlen($content) > 140) {
                $error = "Kommentti saa olla enintään 140 merkkiä.";
            } else {
                if (addComment($conn, $postId, $userId, $content)) {
                    $postOwnerId = getPostOwnerId($conn, $postId);
                    $preview = mb_substr($content, 0, 60);
                    addNotification($conn, $postOwnerId, $userId, $_SESSION['username'] ?? '', $postId, 'comment', $preview);
                }
                header("Location: " . $cleanUrl . "#comments-" . $postId);
                exit;
            }
        }
    }
    // Poista kommentti
    elseif (isset($_POST["delete_comment"])) {
        $commentId = (int)($_POST["comment_id"] ?? 0);
        $postId = (int)($_POST["post_id"] ?? 0);
        if ($commentId > 0 && $userId > 0) {
            deleteComment($conn, $commentId, $userId);
            header("Location: " . $cleanUrl . ($postId > 0 ? "#comments-" . $postId : ""));
            exit;
        }
    }
}
