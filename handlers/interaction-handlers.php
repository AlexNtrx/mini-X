<?php
/** @var mysqli $conn */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $redirectUrl = getSafeRedirectUrl("index.php");
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
    // Lisää kommentti (tai vastaus alikommenttina)
    elseif (isset($_POST["add_comment"])) {
        $postId = (int)($_POST["post_id"] ?? 0);
        $parentId = !empty($_POST["parent_id"]) ? (int)$_POST["parent_id"] : null;
        $content = trim($_POST["comment_content"] ?? "");
        if ($postId > 0 && $userId > 0) {
            if ($content === "") {
                $error = "Kommentti ei voi olla tyhjä.";
            } elseif (mb_strlen($content) > 140) {
                $error = "Kommentti saa olla enintään 140 merkkiä.";
            } else {
                if (addComment($conn, $postId, $userId, $content, $parentId)) {
                    $preview = mb_substr($content, 0, 60);
                    $postOwnerId = getPostOwnerId($conn, $postId);

                    if ($parentId && $parentId > 0) {
                        $parentOwnerId = getCommentOwnerId($conn, $parentId);
                        if ($parentOwnerId > 0 && $parentOwnerId !== $userId) {
                            addNotification($conn, $parentOwnerId, $userId, $_SESSION['username'] ?? '', $postId, 'comment_reply', $preview);
                        }
                        if ($postOwnerId > 0 && $postOwnerId !== $userId && $postOwnerId !== $parentOwnerId) {
                            addNotification($conn, $postOwnerId, $userId, $_SESSION['username'] ?? '', $postId, 'comment', $preview);
                        }
                    } else {
                        if ($postOwnerId > 0 && $postOwnerId !== $userId) {
                            addNotification($conn, $postOwnerId, $userId, $_SESSION['username'] ?? '', $postId, 'comment', $preview);
                        }
                    }
                }
                header("Location: " . $cleanUrl . "#comments-" . $postId);
                exit;
            }
        }
    }
    // Aseta tai poista paras vastaus (Best Answer)
    elseif (isset($_POST["toggle_best_answer"])) {
        $postId = (int)($_POST["post_id"] ?? 0);
        $commentId = (int)($_POST["comment_id"] ?? 0);
        if ($postId > 0 && $commentId > 0 && $userId > 0) {
            $res = toggleBestAnswer($conn, $postId, $commentId, $userId);
            if ($res && is_array($res)) {
                if ($res['action'] === 'marked') {
                    $commentOwnerId = (int)($res['comment_owner_id'] ?? 0);
                    if ($commentOwnerId > 0 && $commentOwnerId !== $userId) {
                        addNotification($conn, $commentOwnerId, $userId, $_SESSION['username'] ?? '', $postId, 'best_answer', 'Valitsi kommenttisi parhaaksi vastaukseksi! ⭐');
                    }
                    setFlashToast("Valittu parhaaksi vastaukseksi! ⭐", "success");
                } elseif ($res['action'] === 'unmarked') {
                    setFlashToast("Parhaan vastauksen merkintä poistettu.", "info");
                }
            }
            header("Location: " . $cleanUrl . "#comments-" . $postId);
            exit;
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
