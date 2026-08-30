<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "functions/init.php";
$conn = dbConnect();
$error = '';
$success = '';

require_once "handlers/auth-handlers.php";
require_once "handlers/post-handlers.php";
require_once "handlers/interaction-handlers.php";
require_once "handlers/setting-handlers.php";

// jos käyttäjä ei ole kirjautunut sisään, ohjataan hänet kirjautumissivulle
if (!isset($_SESSION['user_id'])) {
    include 'pages/form-page.php';
    exit;
}
//  Hae sivu-parametri GET-pyynnöstä, oletuksena 'home'
$page = trim($_GET['page'] ?? 'home');   

// Määrittele reitit ja niiden vastaavat tiedostot
$routes = [
    'home'          => 'pages/home.php',
    'profile'       => 'pages/profile.php',
    'notifications' => 'pages/notifications.php',
    'selaa'         => 'pages/selaa.php',
    'setting'       => 'pages/setting.php'
];

// Jos sivu-parametri on määritelty reiteissä, sisällytä vastaava tiedosto
if (isset($routes[$page])) { 
    include $routes[$page];
} else {
    include $routes['home'];
}