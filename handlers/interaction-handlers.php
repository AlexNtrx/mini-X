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
            $isLiked = isPostLikedByUser($conn, $postId, $userId);
            if ($isLiked) {
                addNotification($conn, $postOwnerId, $userId, $_SESSION['username'] ?? '', $postId, 'like');
            } else {
                removeNotification($conn, $postOwnerId, $userId, $postId, 'like');
            }
            if (isAjaxRequest()) {
                $likeCount = getLikeCount($conn, $postId);
                sendJsonResponse([
                    'success' => true,
                    'post_id' => $postId,
                    'is_liked' => $isLiked,
                    'like_count' => $likeCount
                ]);
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

                    if (isAjaxRequest()) {
                        $post = getPostById($conn, $postId);
                        $commentsHtml = renderCommentsListHtml($conn, $postId, null, (int)($post['user_id'] ?? 0));
                        $commentCount = getCommentCount($conn, $postId);
                        sendJsonResponse([
                            'success' => true,
                            'message' => 'Kommentti lisätty onnistuneesti!',
                            'post_id' => $postId,
                            'comments_html' => $commentsHtml,
                            'comment_count' => $commentCount
                        ]);
                    }
                } else {
                    $error = "Kommentin lisääminen epäonnistui.";
                }

                if (empty($error)) {
                    header("Location: " . $cleanUrl . "#comments-" . $postId);
                    exit;
                }
            }

            if (!empty($error) && isAjaxRequest()) {
                sendJsonResponse([
                    'success' => false,
                    'error' => $error
                ], 400);
            }
        }
    }
    // Poista kommentti
    elseif (isset($_POST["delete_comment"])) {
        $idOptions = ['options' => ['min_range' => 1]];
        $commentId = filter_var($_POST["comment_id"] ?? null, FILTER_VALIDATE_INT, $idOptions);
        $postId = filter_var($_POST["post_id"] ?? null, FILTER_VALIDATE_INT, $idOptions);
        if (!$commentId || !$postId || $userId <= 0 || !deleteComment($conn, $commentId, $postId, $userId)) {
            $error = 'Kommentin poisto epäonnistui.';
            if (isAjaxRequest()) {
                sendJsonResponse(['success' => false, 'error' => $error], 400);
            }
            setFlashToast($error, 'error');
            header("Location: " . $cleanUrl);
            exit;
        }

        if (isAjaxRequest()) {
            $post = getPostById($conn, $postId);
            $commentsHtml = renderCommentsListHtml($conn, $postId, null, (int)($post['user_id'] ?? 0));
            $commentCount = getCommentCount($conn, $postId);
            sendJsonResponse([
                'success' => true,
                'message' => 'Kommentti poistettu onnistuneesti!',
                'post_id' => $postId,
                'comments_html' => $commentsHtml,
                'comment_count' => $commentCount
            ]);
        }

        header("Location: " . $cleanUrl . "#comments-" . $postId);
        exit;
    }
    // Lähetä ilmianto (Report Post/Comment)
    elseif (isset($_POST["submit_report"])) {
        $postId = !empty($_POST["post_id"]) ? (int)$_POST["post_id"] : null;
        $commentId = !empty($_POST["comment_id"]) ? (int)$_POST["comment_id"] : null;
        $reason = trim($_POST["reason"] ?? "other");
        $details = trim($_POST["details"] ?? "");

        if ($userId > 0 && ($postId || $commentId)) {
            if (function_exists('createReport') && createReport($conn, $userId, $postId, $commentId, $reason, $details)) {
                if (isAjaxRequest()) {
                    sendJsonResponse(['success' => true, 'message' => 'Kiitos ilmoituksesta! Ylläpito tarkistaa sisällön pikimmiten.']);
                }
                setFlashToast("Kiitos ilmoituksesta! Ylläpito tarkistaa sisällön pikimmiten.", "success");
            } else {
                if (isAjaxRequest()) {
                    sendJsonResponse(['success' => false, 'message' => 'Ilmoituksen lähetys epäonnistui.'], 500);
                }
                setFlashToast("Ilmoituksen lähetys epäonnistui.", "error");
            }
        } else {
            if (isAjaxRequest()) {
                sendJsonResponse(['success' => false, 'message' => 'Virheellinen pyyntö.'], 400);
            }
        }
        header("Location: " . $cleanUrl);
        exit;
    }
    // Lähetä palaute (Submit Feedback)
    elseif (isset($_POST["submit_feedback"])) {
        $type = trim($_POST["feedback_type"] ?? "general");
        $subject = trim($_POST["subject"] ?? "");
        $message = trim($_POST["message"] ?? "");

        if ($userId > 0) {
            if (empty($subject) || empty($message)) {
                if (isAjaxRequest()) {
                    sendJsonResponse(['success' => false, 'message' => 'Täytä otsikko ja viesti.'], 400);
                }
                setFlashToast("Täytä otsikko ja viesti.", "error");
            } elseif (function_exists('submitFeedback') && submitFeedback($conn, $userId, $type, $subject, $message)) {
                if (isAjaxRequest()) {
                    sendJsonResponse(['success' => true, 'message' => 'Kiitos palautteestasi! Viestisi on toimitettu ylläpidolle.']);
                }
                setFlashToast("Kiitos palautteestasi! Viestisi on toimitettu ylläpidolle.", "success");
            } else {
                if (isAjaxRequest()) {
                    sendJsonResponse(['success' => false, 'message' => 'Palautteen lähetys epäonnistui.'], 500);
                }
                setFlashToast("Palautteen lähetys epäonnistui.", "error");
            }
        }
        header("Location: " . $cleanUrl);
        exit;
    }
}
