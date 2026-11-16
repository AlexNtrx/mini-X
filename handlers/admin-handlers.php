<?php
/**
 * Mini-X - Admin Actions Handler
 * Käsittelee kaikki ylläpitäjän toimenpiteet: käyttäjien moderointi, huoneet,
 * ilmiantojen ratkaisu, palautteet, julkaisujen poisto ja huoltotila.
 */

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["admin_action"])) {
    // Varmistetaan että käyttäjä on ylläpitäjä
    if (!function_exists('isAdmin') || !isAdmin()) {
        if (function_exists('isAjaxRequest') && isAjaxRequest()) {
            sendJsonResponse(['success' => false, 'error' => 'Pääsy evätty.'], 403);
        }
        setFlashToast("Pääsy evätty.", "error");
        header("Location: index.php?page=home");
        exit;
    }

    $adminId = (int)($_SESSION['user_id'] ?? 0);
    $action = trim($_POST["admin_action"]);
    $redirectUrl = getSafeRedirectUrl("admin.php");

    // 1. Käyttäjätietojen päivitys (Username, Display Name, Role)
    if ($action === "update_user") {
        $targetUserId = (int)($_POST["target_user_id"] ?? 0);
        $newUsername = trim($_POST["username"] ?? "");
        $newDisplayName = trim($_POST["display_name"] ?? "");
        $newRole = trim($_POST["role"] ?? "user");

        $res = adminUpdateUser($conn, $adminId, $targetUserId, $newUsername, $newDisplayName, $newRole);
        if ($res['success']) {
            setFlashToast($res['message'], "success");
        } else {
            setFlashToast($res['message'], "error");
        }
        header("Location: " . $redirectUrl);
        exit;
    }

    // 2. Käyttäjän avattaren nollaus
    elseif ($action === "reset_avatar") {
        $targetUserId = (int)($_POST["target_user_id"] ?? 0);
        if (adminResetUserAvatar($conn, $adminId, $targetUserId)) {
            setFlashToast("Käyttäjän profiilikuva nollattu onnistuneesti!", "success");
        } else {
            setFlashToast("Profiilikuvan nollaus epäonnistui.", "error");
        }
        header("Location: " . $redirectUrl);
        exit;
    }

    // 3. Käyttäjätilin tilan vaihtaminen (Jäädytys / Aktivointi)
    elseif ($action === "toggle_user_status") {
        $targetUserId = (int)($_POST["target_user_id"] ?? 0);
        $res = adminToggleUserStatus($conn, $adminId, $targetUserId);
        if ($res['success']) {
            setFlashToast($res['message'], "success");
        } else {
            setFlashToast($res['message'], "error");
        }
        header("Location: " . $redirectUrl);
        exit;
    }

    // 4. Käyttäjätilin pysyvä poisto (Hard Delete)
    elseif ($action === "delete_user_permanent") {
        $targetUserId = (int)($_POST["target_user_id"] ?? 0);
        if (adminDeleteUserPermanently($conn, $adminId, $targetUserId)) {
            setFlashToast("Käyttäjätili ja kaikki sen tiedot poistettu pysyvästi!", "success");
        } else {
            setFlashToast("Käyttäjän poistaminen epäonnistui.", "error");
        }
        header("Location: " . $redirectUrl);
        exit;
    }

    // 5. Uuden huoneen lisääminen
    elseif ($action === "add_room") {
        $slug = trim($_POST["slug"] ?? "");
        $name = trim($_POST["name"] ?? "");
        $icon = trim($_POST["icon"] ?? "");
        $desc = trim($_POST["description"] ?? "");

        $res = adminAddRoom($conn, $adminId, $slug, $name, $icon, $desc);
        if ($res['success']) {
            setFlashToast($res['message'], "success");
        } else {
            setFlashToast($res['message'], "error");
        }
        header("Location: admin.php?tab=rooms");
        exit;
    }

    // 6. Huoneen tilan muuttaminen (Aktiivinen / Piilotettu)
    elseif ($action === "toggle_room") {
        $roomId = (int)($_POST["room_id"] ?? 0);
        $res = adminToggleRoomStatus($conn, $adminId, $roomId);
        if ($res['success']) {
            setFlashToast($res['message'], "success");
        } else {
            setFlashToast($res['message'], "error");
        }
        header("Location: admin.php?tab=rooms");
        exit;
    }

    // 7. Huoneen poistaminen
    elseif ($action === "delete_room") {
        $roomId = (int)($_POST["room_id"] ?? 0);
        $res = adminDeleteRoom($conn, $adminId, $roomId);
        if ($res['success']) {
            setFlashToast($res['message'], "success");
        } else {
            setFlashToast($res['message'], "error");
        }
        header("Location: admin.php?tab=rooms");
        exit;
    }

    // 8. Julkaisun poisto ylläpitäjän toimesta
    elseif ($action === "admin_delete_post") {
        $postId = (int)($_POST["post_id"] ?? 0);
        if ($postId > 0 && adminDeletePost($conn, $adminId, $postId)) {
            setFlashToast("Julkaisu poistettu ylläpidon toimesta!", "success");
        } else {
            setFlashToast("Julkaisun poistaminen epäonnistui.", "error");
        }
        header("Location: " . $redirectUrl);
        exit;
    }

    // 9. Julkaisun kiinnitys (Toggle Pin)
    elseif ($action === "admin_toggle_pin") {
        $postId = (int)($_POST["post_id"] ?? 0);
        $res = adminTogglePostPin($conn, $adminId, $postId);
        if ($res['success']) {
            $msg = $res['is_pinned'] ? "Julkaisu kiinnitetty ilmoitukseksi!" : "Julkaisun kiinnitys poistettu.";
            setFlashToast($msg, "success");
        } else {
            setFlashToast("Toiminto epäonnistui.", "error");
        }
        header("Location: " . $redirectUrl);
        exit;
    }

    // 10. Ilmiannon ratkaisu (Dismiss tai Delete Content)
    elseif ($action === "resolve_report") {
        $reportId = (int)($_POST["report_id"] ?? 0);
        $resolveAction = trim($_POST["resolve_action"] ?? "dismiss");

        if (adminResolveReport($conn, $adminId, $reportId, $resolveAction)) {
            $msg = ($resolveAction === 'delete_content') ? "Sisältö poistettu ja ilmianto kuitattu." : "Ilmianto hylätty.";
            setFlashToast($msg, "success");
        } else {
            setFlashToast("Ilmiannon käsittely epäonnistui.", "error");
        }
        header("Location: admin.php?tab=reports");
        exit;
    }

    // 11. Palautteen tilan muuttaminen
    elseif ($action === "update_feedback") {
        $feedbackId = (int)($_POST["feedback_id"] ?? 0);
        $status = trim($_POST["status"] ?? "read");
        $note = trim($_POST["admin_note"] ?? "");

        if (adminUpdateFeedbackStatus($conn, $adminId, $feedbackId, $status, $note)) {
            setFlashToast("Palautteen tila päivitetty!", "success");
        } else {
            setFlashToast("Päivitys epäonnistui.", "error");
        }
        header("Location: admin.php?tab=feedbacks");
        exit;
    }

    // 12. Palautteen poisto
    elseif ($action === "delete_feedback") {
        $feedbackId = (int)($_POST["feedback_id"] ?? 0);
        if (adminDeleteFeedback($conn, $adminId, $feedbackId)) {
            setFlashToast("Palaute poistettu!", "success");
        } else {
            setFlashToast("Palautteen poisto epäonnistui.", "error");
        }
        header("Location: admin.php?tab=feedbacks");
        exit;
    }

    // 13. Huoltotilan kytkeminen (Toggle Maintenance Mode)
    elseif ($action === "toggle_maintenance") {
        $currentMode = getSystemSetting($conn, 'maintenance_mode', '0');
        $newMode = ($currentMode === '1') ? '0' : '1';
        setSystemSetting($conn, 'maintenance_mode', $newMode);
        logAdminAction($conn, $adminId, 'toggle_maintenance', 'system', null, "Huoltotila asetettu arvoon: {$newMode}");

        $msg = ($newMode === '1') ? "Huoltotila on nyt KÄYTÖSSÄ! (Vain ylläpitäjät pääsevät sivustolle)" : "Huoltotila on nyt POIS PÄÄLTÄ. (Sivusto auki kaikille)";
        setFlashToast($msg, "success");
        header("Location: admin.php?tab=overview");
        exit;
    }
}
