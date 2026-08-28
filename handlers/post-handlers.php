<?php 
require_once "functions/init.php";
/** @var mysqli $conn */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    $redirectUrl = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "index.php";

    //  Luo uusi julkaisu
    if (isset($_POST["create_post"])) {
        $content = trim($_POST["content"] ?? "");
        if ($content !== "" && $userId > 0) {
            if (addPost($conn, $userId, $content)) {
                header("Location: " . $redirectUrl);
                exit;
            }
        }
    }
    //  Muokkaa julkaisua
    elseif (isset($_POST["update_post"])) {
        $id = (int)($_POST["id"] ?? 0);
        $content = trim($_POST["content"] ?? "");
        if ($id > 0 && $content !== "" && $userId > 0) {
            updatePost($conn, $id, $content, $userId);
            header("Location: " . $redirectUrl);
            exit;
        }
    }
    //  Poista julkaisu
    elseif (isset($_POST["delete_post"])) {
        $id = (int)($_POST["id"] ?? 0);
        if ($id > 0 && $userId > 0) {
            deletePost($conn, $id, $userId);
            header("Location: " . $redirectUrl);
            exit;
        }
    }
    // Rekisteröidy
    elseif (isset($_POST["register_post"])) {
        $username = trim($_POST["username"] ?? "");
        $password = trim($_POST["password"] ?? "");
        $email = trim($_POST['email'] ?? "");

        if (empty($username) || empty($password)|| empty($email)) {
            $error = "Täytä kaikki kentät.";
        } elseif (strlen($username) < 3) {
            $error = "Käyttäjänimen tulee olla vähintään 3 merkkiä.";
        } elseif (strlen($password) < 6) {
            $error = "Salasanan tulee olla vähintään 6 merkkiä.";
        } elseif (isUsernameExists($conn, $username)) {
            $error = "Käyttäjänimi on jo varattu.";
        } else {
            if (registerUser($conn, $username, $password,$email)) {
                $success = "Rekisteröinti onnistui! Voit nyt kirjautua sisään.";
            } else {
                $error = "Rekisteröinti epäonnistui.";
            }
        }
    }
    //  Kirjaudu sisään
    elseif (isset($_POST["login_post"])) {
        $username = trim($_POST["username"] ?? "");
        $password = trim($_POST["password"] ?? "");

        if (empty($username) || empty($password)) {
            $error = "Täytä käyttäjänimi ja salasana.";
        } else {
            $user = loginUser($conn, $username, $password);
            if ($user) {
                session_regenerate_id(true); 
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: index.php?page=home");
                exit;
            } else {
                $error = "Virheellinen käyttäjänimi tai salasana.";
            }
        }
    }
    //  Tykkää / Peruuta tykkäys
    elseif (isset($_POST["like_post"])) {
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
    //  Lisää kommentti
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
    //  Poista kommentti
    elseif (isset($_POST["delete_comment"])) {
        $commentId = (int)($_POST["comment_id"] ?? 0);
        if ($commentId > 0 && $userId > 0) {
            deleteComment($conn, $commentId, $userId);
            header("Location: " . $redirectUrl);
            exit;
        }
    }
    //  Päivitä profiilin käyttäjänimi
    elseif (isset($_POST["update_profile"])) {
        $newUsername = trim($_POST["username"] ?? "");
        if ($userId > 0) {
            if (empty($newUsername)) {
                $error = "Käyttäjänimi ei voi olla tyhjä.";
            } elseif (strlen($newUsername) < 3) {
                $error = "Käyttäjänimen tulee olla vähintään 3 merkkiä.";
            } elseif ($newUsername !== ($_SESSION['username'] ?? '') && isUsernameExists($conn, $newUsername)) {
                $error = "Käyttäjänimi on jo varattu toiselle käyttäjälle.";
            } else {
                if (updateUsername($conn, $userId, $newUsername)) {
                    $_SESSION['username'] = $newUsername;
                    $success = "Käyttäjänimi päivitetty onnistuneesti!";
                } else {
                    $error = "Päivitys epäonnistui.";
                }
            }
        }
    }
    // Päivitä salasana
    elseif (isset($_POST["update_password"])) {
        $newSalasana = trim($_POST["password"] ?? "");
        $confirmPassword =  trim($_POST["confirm_password"] ?? "");
        if ($userId > 0) {
            if (empty($newSalasana)) {
                $error = "Salasana ei voi olla tyhjä.";
            } elseif (strlen($newSalasana) < 6) {
                $error = "Salasana tulee olla vähintään 6 merkkiä.";
            } elseif ($newSalasana !== $confirmPassword) {
                $error = "Salasanat eivät täsmää";
            } else {
                if (updateSalasana($conn, $userId, $newSalasana)) {
                    $success = "Käyttäjänimi päivitetty onnistuneesti!";
                } else {
                    $error = "Päivitys epäonnistui.";
                }
            }
        }
    }
}
?>