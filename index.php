<?php
// Pidä PHP-varoitukset poissa rekisteröintivastauksesta.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['register_post'])) {
    ini_set('display_errors', '0');
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "functions/init.php";
$conn = dbConnect();
$error = '';
$success = '';

refreshAuthenticatedSession($conn);
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

// Suojataan ja ohjataan ylläpitosivu erilliseen admin.php-portaaliin
if ($page === 'admin') {
    if (!isAdmin()) {
        setFlashToast("Pääsy evätty. Vain ylläpitäjille.", "error");
        header("Location: index.php?page=home");
        exit;
    }
    $queryParams = $_GET;
    unset($queryParams['page']);
    $qs = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
    header("Location: admin.php" . $qs);
    exit;
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
