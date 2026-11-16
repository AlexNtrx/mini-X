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
if (!refreshAuthenticatedSession($conn)) {
    setFlashToast("Kirjaudu sisään päästäksesi ylläpitoon.", "error");
    header("Location: index.php");
    exit;
}

// 2. Tarkista ylläpito-oikeudet (RBAC)
if (!function_exists('isAdmin') || !isAdmin()) {
    http_response_code(403);
    setFlashToast("Pääsy evätty. Vain ylläpitäjille.", "error");
    header("Location: index.php?page=home");
    exit;
}

// 3. Käsittele ylläpidon toimenpiteet
require_once __DIR__ . "/handlers/admin-handlers.php";
require_once __DIR__ . "/handlers/interaction-handlers.php";
require_once __DIR__ . "/handlers/post-handlers.php";

// 4. Lataa erillinen ylläpidon näkymä
include __DIR__ . "/pages/admin.php";
