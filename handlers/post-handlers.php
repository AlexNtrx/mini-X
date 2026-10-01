<?php
/** @var mysqli $conn */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $redirectUrl = getSafeRedirectUrl("index.php");

    // Luo uusi julkaisu
    if (isset($_POST["create_post"])) {
        $content = trim($_POST["content"] ?? "");
        $hasImage = isset($_FILES["image"]) && is_array($_FILES["image"]) && ($_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE);

        if ($userId > 0) {
            if ($content === "" && !$hasImage) {
                $error = "Julkaisussa on oltava tekstiä tai kuva.";
            } elseif (mb_strlen($content) > 140) {
                $error = "Julkaisun teksti saa olla enintään 140 merkkiä.";
            } else {
                $imageFileName = null;
                if ($hasImage) {
                    $uploadResult = uploadPostImage($_FILES["image"], $userId);
                    if (!empty($uploadResult['error'])) {
                        $error = $uploadResult['error'];
                    } else {
                        $imageFileName = $uploadResult['filename'] ?? null;
                    }
                }

                $room = trim($_POST["room"] ?? "general");
                $postType = trim($_POST["post_type"] ?? "general");

                if (empty($error)) {
                    $newPostId = addPost($conn, $userId, $content, $imageFileName, $room, $postType);
                    if ($newPostId) {
                        if (isAjaxRequest()) {
                            $newPost = getPostById($conn, (int)$newPostId);
                            $postHtml = $newPost ? renderPostCardHtml($conn, $newPost) : '';
                            sendJsonResponse([
                                'success' => true,
                                'message' => 'Julkaisu luotu onnistuneesti!',
                                'post_id' => (int)$newPostId,
                                'html' => $postHtml
                            ]);
                        }
                        setFlashToast("Julkaisu luotu onnistuneesti!", "success");
                        header("Location: " . $redirectUrl);
                        exit;
                    } else {
                        $error = "Julkaisun luominen epäonnistui.";
                    }
                }
            }
        }

        if (!empty($error) && isAjaxRequest()) {
            sendJsonResponse([
                'success' => false,
                'error' => $error
            ], 400);
        }
    }
    // Muokkaa julkaisua
    elseif (isset($_POST["update_post"])) {
        $id = (int)($_POST["id"] ?? 0);
        $content = trim($_POST["content"] ?? "");
        if ($id > 0 && $userId > 0) {
            if ($content === "") {
                $error = "Julkaisun teksti ei voi olla tyhjä.";
            } elseif (mb_strlen($content) > 140) {
                $error = "Julkaisun teksti saa olla enintään 140 merkkiä.";
            } else {
                if (updatePost($conn, $id, $content, $userId)) {
                    if (isAjaxRequest()) {
                        sendJsonResponse([
                            'success' => true,
                            'message' => 'Julkaisu päivitetty onnistuneesti!',
                            'post_id' => $id,
                            'content' => $content
                        ]);
                    }
                    setFlashToast("Julkaisu päivitetty onnistuneesti!", "success");
                    header("Location: " . $redirectUrl);
                    exit;
                } else {
                    $error = "Julkaisun muokkaaminen epäonnistui.";
                }
            }
        }

        if (!empty($error) && isAjaxRequest()) {
            sendJsonResponse([
                'success' => false,
                'error' => $error
            ], 400);
        }
    }
    // Poista julkaisu
    elseif (isset($_POST["delete_post"])) {
        $id = (int)($_POST["id"] ?? 0);
        if ($id > 0 && $userId > 0) {
            if (deletePost($conn, $id, $userId)) {
                if (isAjaxRequest()) {
                    sendJsonResponse([
                        'success' => true,
                        'message' => 'Julkaisu poistettu onnistuneesti!',
                        'post_id' => $id
                    ]);
                }
                setFlashToast("Julkaisu poistettu onnistuneesti!", "success");
            } else {
                if (isAjaxRequest()) {
                    sendJsonResponse([
                        'success' => false,
                        'error' => 'Julkaisun poistaminen epäonnistui.'
                    ], 400);
                }
                setFlashToast("Julkaisun poistaminen epäonnistui.", "error");
            }
            header("Location: " . $redirectUrl);
            exit;
        }
    }
}