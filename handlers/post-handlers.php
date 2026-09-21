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

                if (empty($error)) {
                    if (addPost($conn, $userId, $content, $imageFileName)) {
                        setFlashToast("Julkaisu luotu onnistuneesti!", "success");
                        header("Location: " . $redirectUrl);
                        exit;
                    } else {
                        $error = "Julkaisun luominen epäonnistui.";
                    }
                }
            }
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
                    setFlashToast("Julkaisu päivitetty onnistuneesti!", "success");
                    header("Location: " . $redirectUrl);
                    exit;
                } else {
                    $error = "Julkaisun muokkaaminen epäonnistui.";
                }
            }
        }
    }
    // Poista julkaisu
    elseif (isset($_POST["delete_post"])) {
        $id = (int)($_POST["id"] ?? 0);
        if ($id > 0 && $userId > 0) {
            if (deletePost($conn, $id, $userId)) {
                setFlashToast("Julkaisu poistettu onnistuneesti!", "success");
            } else {
                setFlashToast("Julkaisun poistaminen epäonnistui.", "error");
            }
            header("Location: " . $redirectUrl);
            exit;
        }
    }
}