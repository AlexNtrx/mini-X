<?php
/**
 * Logout Handler
 * Poistaa istuntotiedot ja ohjaa käyttäjän kirjautumissivulle.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_unset();  // Poistaa kaikki istuntomuuttujat
session_destroy(); // Tuhoaa istunnon

if (function_exists('isAjaxRequest') && isAjaxRequest()) {
    sendJsonResponse([
        'success' => true,
        'message' => 'Olet kirjautunut ulos.',
        'redirect' => 'index.php'
    ]);
}

$isDirect = (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'logout.php' && strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/handlers/') !== false);
header("Location: " . ($isDirect ? "../index.php" : "index.php"));
exit();
