<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "functions/init.php";
$conn = dbConnect();
$error = '';
$success = '';

require_once "handlers/auth-handlers.php";

// Julkiset sivut, joita voi tarkastella myös ilman sisäänkirjautumista
$publicPages = [
    'privacy'         => 'pages/privacy-policy.php',
    'forgot-password' => 'pages/forgot-password.php',
    'reset-password'  => 'pages/reset-password.php',
];

$page = trim($_GET['page'] ?? '');

// Jos käyttäjä ei ole kirjautunut sisään
if (!isset($_SESSION['user_id'])) {
    if (isset($publicPages[$page])) {
        include $publicPages[$page];
        exit;
    }
    if (isAjaxRequest()) {
        sendJsonResponse([
            'success' => false,
            'logged_in' => false,
            'error' => 'Istunto on vanhentunut. Kirjaudu sisään uudelleen.',
            'redirect' => 'index.php'
        ], 401);
    }
    include 'pages/form-page.php';
    exit;
}

// Päivitetään käyttäjän tiedot tietokannasta sessioon (varmistetaan aina tuore display_name, username, avatar ja role)
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

require_once "handlers/post-handlers.php";
require_once "handlers/interaction-handlers.php";
require_once "handlers/setting-handlers.php";
require_once "handlers/profile-handlers.php";
require_once "handlers/admin-handlers.php";

// Huoltotilan tarkistus: Jos huoltotila on päällä, vain ylläpitäjät pääsevät sisään
if (function_exists('isMaintenanceMode') && isMaintenanceMode($conn) && !isAdmin() && $page !== 'logout') {
    include 'pages/maintenance.php';
    exit;
}

// Oletussivu kirjautuneelle käyttäjälle on 'home'
if (empty($page)) {
    $page = 'home';
}

// Suojataan ylläpitosivu (Vain ylläpitäjät voivat nähdä tai käyttää)
if ($page === 'admin') {
    if (!isAdmin()) {
        setFlashToast("Pääsy evätty. Vain ylläpitäjille.", "error");
        header("Location: index.php?page=home");
        exit;
    }
}

// Määrittele reitit ja niiden vastaavat tiedostot
$routes = [
    'home'          => 'pages/home.php',
    'admin'         => 'pages/admin.php',
    'profile'       => 'pages/profile.php',
    'notifications' => 'pages/notifications.php',
    'selaa'         => 'pages/selaa.php',
    'setting'       => 'pages/setting.php',
    'palaute'       => 'pages/palaute.php',
    'privacy'       => 'pages/privacy-policy.php',
    'logout'        => 'handlers/logout.php',
];

// Jos sivu-parametri on määritelty reiteissä, sisällytä vastaava tiedosto
if (isset($routes[$page])) { 
    include $routes[$page];
} else {
    include $routes['home'];
}