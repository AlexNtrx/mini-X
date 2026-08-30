<?php
/** @var mysqli $conn */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.php";

    // Luo uusi julkaisu
    if (isset($_POST["create_post"])) {
        $content = trim($_POST["content"] ?? "");
        if ($content !== "" && $userId > 0) {
            if (addPost($conn, $userId, $content)) {
                header("Location: " . $redirectUrl);
                exit;
            }
        }
    }
    // Muokkaa julkaisua
    elseif (isset($_POST["update_post"])) {
        $id = (int)($_POST["id"] ?? 0);
        $content = trim($_POST["content"] ?? "");
        if ($id > 0 && $content !== "" && $userId > 0) {
            updatePost($conn, $id, $content, $userId);
            header("Location: " . $redirectUrl);
            exit;
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