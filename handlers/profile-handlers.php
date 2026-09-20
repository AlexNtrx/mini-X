<?php
/**
 * Mini-X - Profile Handlers
 * Käsittelee suorat profiilimuokkaukset suoraan profiilisivun Modalista.
 */

/** @var mysqli $conn */
/** @var string $error */
/** @var string $success */

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);

    // Käsittelee Edit Profile Modal -lomakkeen tallennuksen
    if (isset($_POST["update_profile_modal"]) && $userId > 0) {
        $hasError = false;

        // 1. Päivitä näyttönimi (Display Name)
        if (isset($_POST["display_name"])) {
            $newDisplayName = trim(preg_replace('/\s+/', ' ', $_POST["display_name"]));
            $len = mb_strlen($newDisplayName, "UTF-8");
            if ($len < 1) {
                $error = "Nimi ei voi olla tyhjä.";
                $hasError = true;
            } elseif ($len > 25) {
                $error = "Nimen tulee olla enintään 25 merkkiä.";
                $hasError = true;
            } else {
                if (updateDisplayName($conn, $userId, $newDisplayName)) {
                    $_SESSION['display_name'] = $newDisplayName;
                }
            }
        }

        // 2. Profiilikuvan päivitys tai poisto
        if (!$hasError) {
            if (!empty($_POST["delete_avatar_flag"])) {
                deleteUserAvatar($conn, $userId);
            } elseif (!empty($_POST["avatar_cropped_data"])) {
                $avatarRes = updateUserAvatarFromDataUrl($conn, $userId, $_POST["avatar_cropped_data"]);
                if ($avatarRes !== true) {
                    $error = is_string($avatarRes) ? $avatarRes : "Profiilikuvan tallentaminen epäonnistui.";
                    $hasError = true;
                }
            } elseif (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] !== UPLOAD_ERR_NO_FILE) {
                $avatarRes = updateUserAvatar($conn, $userId, $_FILES["avatar"]);
                if ($avatarRes !== true) {
                    $error = is_string($avatarRes) ? $avatarRes : "Profiilikuvan lataaminen epäonnistui.";
                    $hasError = true;
                }
            }
        }

        // 3. Bannerikuvan päivitys tai poisto
        if (!$hasError) {
            if (!empty($_POST["delete_banner_flag"])) {
                deleteUserBanner($conn, $userId);
            } elseif (isset($_FILES["banner"]) && $_FILES["banner"]["error"] !== UPLOAD_ERR_NO_FILE) {
                $bannerRes = updateUserBanner($conn, $userId, $_FILES["banner"]);
                if ($bannerRes !== true && $bannerRes !== null) {
                    $error = is_string($bannerRes) ? $bannerRes : "Bannerin lataaminen epäonnistui.";
                    $hasError = true;
                }
            }
        }

        // 4. Bio, Musiikki ja Teema (Customization)
        if (!$hasError) {
            $bio         = trim($_POST["bio"] ?? "");
            $themeAccent = sanitizeHexColor($_POST["theme_accent"] ?? "#1d9bf0");
            $bannerPosY  = isset($_POST["banner_pos_y"]) ? max(0, min(100, (int)$_POST["banner_pos_y"])) : 50;

            $customData = [
                'bio'          => $bio,
                'theme_accent' => $themeAccent,
                'banner_pos_y' => $bannerPosY
            ];

            // Taustan päivitys tai poisto (Preset tai oma kuva)
            if (!empty($_POST["delete_bg_flag"])) {
                deleteUserBackground($conn, $userId);
                $customData['theme_bg_type'] = 'default';
                $customData['theme_bg_val']  = '';
            } elseif (isset($_FILES["background_image"]) && $_FILES["background_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
                $bgRes = updateUserBackground($conn, $userId, $_FILES["background_image"]);
                if ($bgRes !== true && $bgRes !== null) {
                    $error = is_string($bgRes) ? $bgRes : "Taustakuvan lataaminen epäonnistui.";
                    $hasError = true;
                }
            } elseif (isset($_POST["theme_bg_type"])) {
                $customData['theme_bg_type'] = trim($_POST["theme_bg_type"]);
                $customData['theme_bg_val']  = trim($_POST["theme_bg_val"] ?? "");
            }

            // Musiikin poisto tai päivitys
            if (!empty($_POST["delete_song_flag"])) {
                $customData['song_title']   = '';
                $customData['song_artist']  = '';
                $customData['song_artwork'] = '';
                $customData['song_url']     = '';
            } else {
                $songTitle   = trim($_POST["song_title"] ?? "");
                $songArtist  = trim($_POST["song_artist"] ?? "");
                $songArtwork = trim($_POST["song_artwork"] ?? "");
                $songUrl     = trim($_POST["song_url"] ?? "");

                if (!empty($songUrl) && !preg_match('/^https?:\/\//i', $songUrl)) {
                    $error = "Musiikin osoitteen tulee alkaa http:// tai https://";
                    $hasError = true;
                } else {
                    $customData['song_title']   = $songTitle;
                    $customData['song_artist']  = $songArtist;
                    $customData['song_artwork'] = $songArtwork;
                    $customData['song_url']     = $songUrl;
                }
            }

            if (!$hasError) {
                if (saveUserCustomization($conn, $userId, $customData)) {
                    $success = "Profiili päivitetty onnistuneesti! ✨";
                } else {
                    $error = "Tietojen tallennus epäonnistui.";
                }
            }
        }
    }
}
