<?php
/** @var mysqli $conn */
/** @var string $error */
/** @var string $success */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Rekisteröidy
    if (isset($_POST["register_post"])) {
        $username = trim($_POST["username"] ?? "");
        $password = trim($_POST["password"] ?? "");
        $email = trim($_POST['email'] ?? "");

        if (empty($username) || empty($password) || empty($email)) {
            $error = "Täytä kaikki kentät.";
        } elseif (strlen($username) < 3) {
            $error = "Käyttäjänimen tulee olla vähintään 3 merkkiä.";
        } elseif (strlen($password) < 6) {
            $error = "Salasanan tulee olla vähintään 6 merkkiä.";
        } elseif (isUsernameExists($conn, $username)) {
            $error = "Käyttäjänimi on jo varattu.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Virheellinen sähköpostiosoite.";
        } elseif (isEmailExists($conn, $email)) {
            $error = "Sähköpostiosoite on jo käytössä.";
        } else {
            if (registerUser($conn, $username, $password, $email)) {
                $success = "Rekisteröinti onnistui! Voit nyt kirjautua sisään.";
            } else {
                $error = "Rekisteröinti epäonnistui.";
            }
        }
    }
    // Kirjaudu sisään
    elseif (isset($_POST["login_post"])) {
        $username = trim($_POST["username"] ?? "");
        $password = trim($_POST["password"] ?? "");

        if (empty($username) || empty($password)) {
            $error = "Täytä käyttäjänimi ja salasana.";
        } else {
            $user = loginUser($conn, $username, $password);
            if ($user) {
                // Jos tili on poistettu (Soft Delete), pyydetään vahvistus tilin uudelleenaktivointiin
                if (!empty($user['deleted_at'])) {
                    $_SESSION['pending_reactivation_user_id'] = (int)$user['id'];
                    $_SESSION['pending_reactivation_username'] = $user['username'];
                    $showReactivationModal = true;
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['avatar'] = $user['avatar'];
                    header("Location: index.php?page=home");
                    exit;
                }
            } else {
                $error = "Virheellinen käyttäjänimi tai salasana.";
            }
        }
    }
    // Vahvista tilin uudelleenaktivointi
    elseif (isset($_POST["confirm_reactivation"])) {
        $pendingUserId = (int)($_SESSION['pending_reactivation_user_id'] ?? 0);
        $pendingUsername = $_SESSION['pending_reactivation_username'] ?? '';

        if ($pendingUserId > 0 && !empty($pendingUsername)) {
            reactivateUser($conn, $pendingUserId);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $pendingUserId;
            $_SESSION['username'] = $pendingUsername;
            unset($_SESSION['pending_reactivation_user_id'], $_SESSION['pending_reactivation_username']);
            header("Location: index.php?page=home");
            exit;
        } else {
            $error = "Aktivointi epäonnistui. Kirjaudu sisään uudelleen.";
        }
    }
    // Peruuta tilin uudelleenaktivointi
    elseif (isset($_POST["cancel_reactivation"])) {
        unset($_SESSION['pending_reactivation_user_id'], $_SESSION['pending_reactivation_username']);
        $showReactivationModal = false;
    }
}
