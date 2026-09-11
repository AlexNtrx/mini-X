<?php
/*
  Lataa kaikki funktiomoduulit
 */

require_once __DIR__ . "/db.php";
require_once __DIR__ . "/posts.php";
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/likes.php";
require_once __DIR__ . "/comments.php";
require_once __DIR__ . "/notifications.php";
require_once __DIR__ . "/password-reset.php";

// Palauttaa turvallisen uudelleenohjausosoitteen (estää Open Redirect -haavoittuvuuden)
function getSafeRedirectUrl($default = 'index.php')
{
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if (empty($referer)) {
        return $default;
    }

    $refererHost = parse_url($referer, PHP_URL_HOST);
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    $cleanRefererHost = preg_replace('/:\d+$/', '', $refererHost ?? '');
    $cleanCurrentHost = preg_replace('/:\d+$/', '', $currentHost);

    if (!empty($refererHost) && strcasecmp($cleanRefererHost, $cleanCurrentHost) === 0) {
        return $referer;
    }

    return $default;
}

