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
    'privacy'    => 'pages/privacy-policy.php',
    'tietosuoja' => 'pages/privacy-policy.php'
];

$page = trim($_GET['page'] ?? '');

// Jos käyttäjä ei ole kirjautunut sisään
if (!isset($_SESSION['user_id'])) {
    if (isset($publicPages[$page])) {
        include $publicPages[$page];
        exit;
    }
    include 'pages/form-page.php';
    exit;
}

require_once "handlers/post-handlers.php";
require_once "handlers/interaction-handlers.php";
require_once "handlers/setting-handlers.php";

// Oletussivu kirjautuneelle käyttäjälle on 'home'
if (empty($page)) {
    $page = 'home';
}

// Määrittele reitit ja niiden vastaavat tiedostot
$routes = [
    'home'          => 'pages/home.php',
    'profile'       => 'pages/profile.php',
    'notifications' => 'pages/notifications.php',
    'selaa'         => 'pages/selaa.php',
    'setting'       => 'pages/setting.php',
    'privacy'       => 'pages/privacy-policy.php',
    'tietosuoja'    => 'pages/privacy-policy.php'
];

// Jos sivu-parametri on määritelty reiteissä, sisällytä vastaava tiedosto
if (isset($routes[$page])) { 
    include $routes[$page];
} else {
    include $routes['home'];
}