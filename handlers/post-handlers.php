<?php
/** @var mysqli $conn */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.php";

    // Luo uusi julkaisu
    if (isset($_POST["create_post"])) {
        $content = trim($_POST["content"] ?? "");
        if ($userId > 0) {
            if ($content === "") {
                $error = "Julkaisun teksti ei voi olla tyhjä.";
            } elseif (mb_strlen($content) > 140) {
                $error = "Julkaisun teksti saa olla enintään 140 merkkiä.";
            } else {
                if (addPost($conn, $userId, $content)) {
                    header("Location: " . $redirectUrl);
                    exit;
                } else {
                    $error = "Julkaisun luominen epäonnistui.";
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
            deletePost($conn, $id, $userId);
            header("Location: " . $redirectUrl);
            exit;
        }
    }
}