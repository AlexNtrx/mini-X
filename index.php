<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "functions/init.php";
$conn = dbConnect();
require_once "handlers/post-handlers.php";

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
];

// Jos sivu-parametri on määritelty reiteissä, sisällytä vastaava tiedosto
if (isset($routes[$page])) { 
    include $routes[$page];
} else {
    include $routes['home'];
}