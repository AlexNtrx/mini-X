<?php
/** @var mysqli $conn */
/** @var string $error */
/** @var string $success */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);

    // Päivitä näyttönimi (Display Name)
    if (isset($_POST["update_display_name"])) {
        $newDisplayName = trim(preg_replace('/\s+/', ' ', $_POST["display_name"] ?? ""));
        if ($userId > 0) {
            $len = mb_strlen($newDisplayName, "UTF-8");
            if ($len < 1) {
                $error = "Nimi ei voi olla tyhjä.";
            } elseif ($len > 25) {
                $error = "Nimen tulee olla enintään 25 merkkiä.";
            } else {
                if (updateDisplayName($conn, $userId, $newDisplayName)) {
                    $_SESSION['display_name'] = $newDisplayName;
                    $success = "Nimi päivitetty onnistuneesti!";
                    if (isAjaxRequest()) {
                        sendJsonResponse([
                            'success' => true,
                            'message' => $success,
                            'display_name' => $newDisplayName,
                            'initials' => getUserInitials($newDisplayName)
                        ]);
                    }
                } else {
                    $error = "Päivitys epäonnistui.";
                }
            }
        }
        if (!empty($error) && isAjaxRequest()) {
            sendJsonResponse(['success' => false, 'error' => $error], 400);
        }
    }
    // Päivitä profiilin käyttäjänimi
    elseif (isset($_POST["update_profile"])) {
        $newUsername = trim($_POST["username"] ?? "");
        if ($userId > 0) {
            if (empty($newUsername)) {
                $error = "Käyttäjänimi ei voi olla tyhjä.";
            } elseif (mb_strlen($newUsername) < 3) {
                $error = "Käyttäjänimen tulee olla vähintään 3 merkkiä.";
            } elseif (mb_strlen($newUsername) > 20) {
                $error = "Käyttäjänimen tulee olla enintään 20 merkkiä.";
            } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $newUsername)) {
                $error = "Käyttäjänimi voi sisältää vain kirjaimia (a-z), numeroita ja alaviivoja (_).";
            } elseif (isUsernameExists($conn, $newUsername, $userId)) {
                $error = "Käyttäjänimi on jo varattu toiselle käyttäjälle.";
            } else {
                if (updateUsername($conn, $userId, $newUsername)) {
                    $_SESSION['username'] = $newUsername;
                    $success = "Käyttäjänimi päivitetty onnistuneesti!";
                    if (isAjaxRequest()) {
                        sendJsonResponse([
                            'success' => true,
                            'message' => $success,
                            'username' => $newUsername
                        ]);
                    }
                } else {
                    $error = "Päivitys epäonnistui.";
                }
            }
        }
        if (!empty($error) && isAjaxRequest()) {
            sendJsonResponse(['success' => false, 'error' => $error], 400);
        }
    }
    // Päivitä profiilikuva (Avatar)
    elseif (isset($_POST["update_avatar"])) {
        if ($userId > 0 && isset($_FILES["avatar"])) {
            $result = updateUserAvatar($conn, $userId, $_FILES["avatar"]);
            if ($result === true) {
                $success = "Profiilikuva päivitetty onnistuneesti!";
                if (isAjaxRequest()) {
                    $newAvatarUrl = getUserAvatarUrl($_SESSION['avatar'] ?? null);
                    sendJsonResponse([
                        'success' => true,
                        'message' => $success,
                        'avatar_url' => $newAvatarUrl
                    ]);
                }
            } else {
                $error = is_string($result) ? $result : "Kuvan lataaminen epäonnistui.";
            }
        }
        if (!empty($error) && isAjaxRequest()) {
            sendJsonResponse(['success' => false, 'error' => $error], 400);
        }
    }
    // Poista profiilikuva
    elseif (isset($_POST["delete_avatar"])) {
        if ($userId > 0) {
            if (deleteUserAvatar($conn, $userId)) {
                $success = "Profiilikuva poistettu onnistuneesti!";
                if (isAjaxRequest()) {
                    sendJsonResponse([
                        'success' => true,
                        'message' => $success,
                        'initials' => getUserInitials($_SESSION['display_name'] ?? ($_SESSION['username'] ?? ''))
                    ]);
                }
            } else {
                $error = "Profiilikuvan poistaminen epäonnistui.";
            }
        }
        if (!empty($error) && isAjaxRequest()) {
            sendJsonResponse(['success' => false, 'error' => $error], 400);
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
                    if (isAjaxRequest()) {
                        sendJsonResponse([
                            'success' => true,
                            'message' => $success
                        ]);
                    }
                } else {
                    $error = "Päivitys epäonnistui.";
                }
            }
        }
        if (!empty($error) && isAjaxRequest()) {
            sendJsonResponse(['success' => false, 'error' => $error], 400);
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
                    if (isAjaxRequest()) {
                        sendJsonResponse([
                            'success' => true,
                            'message' => $success,
                            'email' => $newEmail
                        ]);
                    }
                } else {
                    $error = "Sähköpostin päivitys epäonnistui.";
                }
            }
        }
        if (!empty($error) && isAjaxRequest()) {
            sendJsonResponse(['success' => false, 'error' => $error], 400);
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
                    if (isAjaxRequest()) {
                        sendJsonResponse([
                            'success' => true,
                            'message' => 'Tilisi on poistettu onnistuneesti.',
                            'redirect' => 'index.php?status=account_deleted'
                        ]);
                    }
                    header("Location: index.php?status=account_deleted");
                    exit;
                } else {
                    $error = is_string($result) ? $result : "Tilin poistaminen epäonnistui.";
                }
            }
        }
        if (!empty($error) && isAjaxRequest()) {
            sendJsonResponse(['success' => false, 'error' => $error], 400);
        }
    }
}

