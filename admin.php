<?php
/**
 * Mini-X - Admin Dashboard Portal
 * Erillinen ja suojattu ylläpitopaneeli (Dedicated Standalone Admin Portal).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/functions/init.php";
$conn = dbConnect();
$error = '';
$success = '';

// 1. Tarkista kirjautuminen
if (!isset($_SESSION['user_id'])) {
    setFlashToast("Kirjaudu sisään päästäksesi ylläpitoon.", "error");
    header("Location: index.php");
    exit;
}

// 2. Päivitä käyttäjän roolitiedot tuoreena tietokannasta
if (isset($conn) && $conn) {
    if (function_exists('ensureAdminSchema')) {
        ensureAdminSchema($conn);
    }
    $sessionStmt = $conn->prepare("SELECT username, display_name, avatar, role FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    if ($sessionStmt) {
        $sessionStmt->bind_param("i", $_SESSION['user_id']);
        $sessionStmt->execute();
        $sessionUserData = $sessionStmt->get_result()->fetch_assoc();
        $sessionStmt->close();
        if ($sessionUserData) {
            $_SESSION['username'] = $sessionUserData['username'];
            $_SESSION['display_name'] = !empty($sessionUserData['display_name']) ? $sessionUserData['display_name'] : $sessionUserData['username'];
            $_SESSION['avatar'] = $sessionUserData['avatar'];
            $_SESSION['role'] = $sessionUserData['role'] ?? 'user';
        }
    }
}

// 3. Tarkista ylläpito-oikeudet (RBAC)
if (!function_exists('isAdmin') || !isAdmin()) {
    http_response_code(403);
    setFlashToast("Pääsy evätty. Vain ylläpitäjille.", "error");
    header("Location: index.php?page=home");
    exit;
}

// 4. Käsittele ylläpidon toimenpiteet
require_once __DIR__ . "/handlers/admin-handlers.php";
require_once __DIR__ . "/handlers/interaction-handlers.php";
require_once __DIR__ . "/handlers/post-handlers.php";

// 5. Lataa erillinen ylläpidon näkymä
include __DIR__ . "/pages/admin.php";
