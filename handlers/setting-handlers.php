<?php
/** @var mysqli $conn */
/** @var string $error */
/** @var string $success */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);

    // Päivitä profiilin käyttäjänimi
    if (isset($_POST["update_profile"])) {
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
    // Päivitä profiilikuva (Avatar)
    elseif (isset($_POST["update_avatar"])) {
        if ($userId > 0 && isset($_FILES["avatar"])) {
            $result = updateUserAvatar($conn, $userId, $_FILES["avatar"]);
            if ($result === true) {
                $success = "Profiilikuva päivitetty onnistuneesti!";
            } else {
                $error = is_string($result) ? $result : "Kuvan lataaminen epäonnistui.";
            }
        }
    }
    // Poista profiilikuva
    elseif (isset($_POST["delete_avatar"])) {
        if ($userId > 0) {
            if (deleteUserAvatar($conn, $userId)) {
                $success = "Profiilikuva poistettu onnistuneesti!";
            } else {
                $error = "Profiilikuvan poistaminen epäonnistui.";
            }
        }
    }
    // Päivitä salasana
    elseif (isset($_POST["update_password"])) {
        $newSalasana = trim($_POST["password"] ?? "");
        $confirmPassword = trim($_POST["confirm_password"] ?? "");
        if ($userId > 0) {
            if (empty($newSalasana)) {
                $error = "Salasana ei voi olla tyhjä.";
            } elseif (strlen($newSalasana) < 6) {
                $error = "Salasana tulee olla vähintään 6 merkkiä.";
            } elseif ($newSalasana !== $confirmPassword) {
                $error = "Salasanat eivät täsmää.";
            } else {
                if (updateSalasana($conn, $userId, $newSalasana)) {
                    $success = "Salasana päivitetty onnistuneesti!";
                } else {
                    $error = "Päivitys epäonnistui.";
                }
            }
        }
    }
    // Päivitä sähköposti
    elseif (isset($_POST["update_email"])) {
        $newEmail = trim($_POST["email"] ?? "");
        if ($userId > 0) {
            if (empty($newEmail)) {
                $error = "Sähköposti ei voi olla tyhjä.";
            } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
                $error = "Virheellinen sähköpostiosoite.";
            } elseif (isEmailExists($conn, $newEmail, $userId)) {
                $error = "Tämä sähköpostiosoite on jo toisen käyttäjän käytössä.";
            } else {
                if (updateEmail($conn, $userId, $newEmail)) {
                    $success = "Sähköposti päivitetty onnistuneesti!";
                } else {
                    $error = "Sähköpostin päivitys epäonnistui.";
                }
            }
        }
    }
    // Poista tili (Soft Delete)
    elseif (isset($_POST["delete_account"])) {
        $confirmPassword = trim($_POST["confirm_password"] ?? "");
        if ($userId > 0) {
            if (empty($confirmPassword)) {
                $error = "Anna salasanasi vahvistaaksesi tilin poiston.";
            } else {
                $result = softDeleteUser($conn, $userId, $confirmPassword);
                if ($result === true) {
                    session_unset();
                    session_destroy();
                    header("Location: index.php?status=account_deleted");
                    exit;
                } else {
                    $error = is_string($result) ? $result : "Tilin poistaminen epäonnistui.";
                }
            }
        }
    }
}
