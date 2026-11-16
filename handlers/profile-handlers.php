<?php
/**
 * Mini-X - Profile Handlers
 * Käsittelee suorat profiilimuokkaukset suoraan profiilisivun Modalista.
 */

/** @var mysqli $conn */
/** @var string $error */
/** @var string $success */

function applyProfileModalChanges($conn, int $userId, ProfileImageChanges $images): ?string
{
    if (isset($_POST['display_name'])) {
        $name = trim(preg_replace('/\s+/', ' ', $_POST['display_name']));
        $length = mb_strlen($name, 'UTF-8');
        if ($length < 1) {
            return 'Nimi ei voi olla tyhjä.';
        }
        if ($length > 25) {
            return 'Nimen tulee olla enintään 25 merkkiä.';
        }
        if (!updateDisplayName($conn, $userId, $name)) {
            return 'Nimen tallentaminen epäonnistui.';
        }
        $_SESSION['display_name'] = $name;
    }

    $avatarResult = true;
    if (!empty($_POST['delete_avatar_flag'])) {
        $avatarResult = deleteUserAvatar($conn, $userId, $images);
    } elseif (!empty($_POST['avatar_cropped_data'])) {
        $avatarResult = updateUserAvatarFromDataUrl($conn, $userId, $_POST['avatar_cropped_data'], $images);
    } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $avatarResult = updateUserAvatar($conn, $userId, $_FILES['avatar'], $images);
    }
    if ($avatarResult !== true) {
        return is_string($avatarResult) ? $avatarResult : 'Profiilikuvan tallentaminen epäonnistui.';
    }

    $bannerResult = true;
    if (!empty($_POST['delete_banner_flag'])) {
        $bannerResult = deleteUserBanner($conn, $userId, $images);
    } elseif (isset($_FILES['banner']) && $_FILES['banner']['error'] !== UPLOAD_ERR_NO_FILE) {
        $bannerResult = updateUserBanner($conn, $userId, $_FILES['banner'], $images);
    }
    if ($bannerResult !== true && $bannerResult !== null) {
        return is_string($bannerResult) ? $bannerResult : 'Bannerin tallentaminen epäonnistui.';
    }

    $customData = [
        'bio' => trim($_POST['bio'] ?? ''),
        'theme_accent' => sanitizeHexColor($_POST['theme_accent'] ?? '#1d9bf0'),
        'banner_pos_y' => isset($_POST['banner_pos_y']) ? max(0, min(100, (int)$_POST['banner_pos_y'])) : 50,
    ];

    $backgroundResult = true;
    if (!empty($_POST['delete_bg_flag'])) {
        $backgroundResult = deleteUserBackground($conn, $userId, $images);
    } elseif (isset($_FILES['background_image']) && $_FILES['background_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $backgroundResult = updateUserBackground($conn, $userId, $_FILES['background_image'], $images);
    } elseif (isset($_POST['theme_bg_type'])) {
        $customData['theme_bg_type'] = trim($_POST['theme_bg_type']);
        $customData['theme_bg_val'] = trim($_POST['theme_bg_val'] ?? '');
    }
    if ($backgroundResult !== true && $backgroundResult !== null) {
        return is_string($backgroundResult) ? $backgroundResult : 'Taustakuvan tallentaminen epäonnistui.';
    }

    $deleteSong = !empty($_POST['delete_song_flag']);
    foreach (['song_title', 'song_artist', 'song_artwork', 'song_url'] as $field) {
        $customData[$field] = $deleteSong ? '' : trim($_POST[$field] ?? '');
    }
    if ($customData['song_url'] !== '' && !preg_match('/^https?:\/\//i', $customData['song_url'])) {
        return 'Musiikin osoitteen tulee alkaa http:// tai https://';
    }

    return saveUserCustomization($conn, $userId, $customData) ? null : 'Tietojen tallennus epäonnistui.';
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if (isset($_POST["update_profile_modal"]) && $userId > 0) {
        $images = new ProfileImageChanges();
        $previousSession = $_SESSION;
        $transactionStarted = false;
        $saved = false;
        $error = 'Tietojen tallennus epäonnistui.';
        try {
            // Taulun varmistaminen tekee DDL-kyselyitä ennen transaktiota.
            if (ensureCustomizationsTable($conn) && $conn->begin_transaction()) {
                $transactionStarted = true;
                $user = $conn->prepare("SELECT id FROM users WHERE id = ? FOR UPDATE");
                $user->bind_param("i", $userId);
                $exists = $user->execute() && $user->get_result()->num_rows === 1;
                $user->close();
                if ($exists) {
                    $error = applyProfileModalChanges($conn, $userId, $images);
                    $saved = $error === null && $conn->commit();
                }
            }
        } catch (mysqli_sql_exception $exception) {
            error_log($exception->getMessage());
        } finally {
            if (!$saved) {
                if ($transactionStarted) {
                    $conn->rollback();
                }
                $_SESSION = $previousSession;
            }
            $images->finish($saved);
        }

        $hasError = !$saved;
        $error = $hasError ? ($error ?? 'Tietojen tallennus epäonnistui.') : '';
        if ($saved) {
            $success = "Profiili päivitetty onnistuneesti! ✨";
        }

        if ($hasError && isAjaxRequest()) {
            sendJsonResponse([
                'success' => false,
                'error' => $error ?: 'Profiilin tallennus epäonnistui.'
            ], 400);
        }

        if (!$hasError && isAjaxRequest()) {
            $updatedUser = null;
            $uStmt = $conn->prepare("SELECT username, display_name, avatar FROM users WHERE id = ? LIMIT 1");
            if ($uStmt) {
                $uStmt->bind_param("i", $userId);
                $uStmt->execute();
                $updatedUser = $uStmt->get_result()->fetch_assoc();
                $uStmt->close();
            }
            $updatedCustom = getUserCustomization($conn, $userId);
            $accentHex = sanitizeHexColor($updatedCustom['theme_accent'] ?? '#1d9bf0');
            $r = hexdec(substr($accentHex, 1, 2));
            $g = hexdec(substr($accentHex, 3, 2));
            $b = hexdec(substr($accentHex, 5, 2));
            $accentRgb = "$r, $g, $b";

            $bgStyle = getProfileBackgroundStyle($updatedCustom['theme_bg_type'] ?? 'default', $updatedCustom['theme_bg_val'] ?? '');

            ob_start();
            $customization = $updatedCustom;
            $isOwnProfile = true;
            include __DIR__ . '/../components/music-player.php';
            $musicHtml = ob_get_clean();

            sendJsonResponse([
                'success' => true,
                'message' => $success ?: 'Profiili päivitetty onnistuneesti! ✨',
                'data' => [
                    'display_name' => $updatedUser['display_name'] ?? ($updatedUser['username'] ?? ''),
                    'avatar_url' => getUserAvatarUrl($updatedUser['avatar'] ?? null),
                    'initials' => getUserInitials($updatedUser['display_name'] ?? ($updatedUser['username'] ?? '')),
                    'banner_url' => getUserBannerUrl($updatedCustom['theme_banner'] ?? null),
                    'banner_pos_y' => (int)($updatedCustom['banner_pos_y'] ?? 50),
                    'bio' => $updatedCustom['bio'] ?? '',
                    'theme_accent' => $accentHex,
                    'theme_accent_rgb' => $accentRgb,
                    'theme_bg_style' => $bgStyle,
                    'music_html' => $musicHtml,
                    'song' => [
                        'title' => $updatedCustom['song_title'] ?? '',
                        'artist' => $updatedCustom['song_artist'] ?? '',
                        'artwork' => $updatedCustom['song_artwork'] ?? '',
                        'url' => $updatedCustom['song_url'] ?? '',
                    ]
                ]
            ]);
        }
    }
}
