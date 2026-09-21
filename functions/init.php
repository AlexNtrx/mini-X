<?php
// Asetetaan aikavyöhyke (Timezone: Suomi / Europe/Helsinki)
date_default_timezone_set('Europe/Helsinki');

// Asetetaan sovelluksen versio
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.1.2');
}

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
require_once __DIR__ . "/theme.php";
require_once __DIR__ . "/toast.php";

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

/**
 * Muotoilee aikaleiman lyhyeen ja selkeään Twitter/X-tyyliin (esim. 'nyt', '15 s', '5 min', '2 t', '3 pv', '17. syysk.')
 *
 * @param string|int|null $datetime Aikaleima (string tai UNIX-timestamp)
 * @return string Lyhyt aikaleima
 */
function formatTimeAgo($datetime)
{
    if (empty($datetime)) {
        return '';
    }

    $timestamp = is_numeric($datetime) ? (int)$datetime : strtotime($datetime);
    if (!$timestamp) {
        return htmlspecialchars((string)$datetime);
    }

    $now = time();
    $diff = $now - $timestamp;

    // Jos aikaleima on tulevaisuudessa tai alle 10 sekuntia sitten
    if ($diff < 10) {
        return 'nyt';
    }

    // Alle 1 minuutti (esim. 45 s)
    if ($diff < 60) {
        return $diff . ' s';
    }

    // Alle 1 tunti (esim. 15 min)
    if ($diff < 3600) {
        $mins = max(1, (int)floor($diff / 60));
        return $mins . ' min';
    }

    // Alle 24 tuntia (esim. 2 t)
    if ($diff < 86400) {
        $hours = (int)floor($diff / 3600);
        return $hours . ' t';
    }

    // Alle 7 päivää (esim. 3 pv)
    if ($diff < 604800) {
        $days = (int)floor($diff / 86400);
        return $days . ' pv';
    }

    // Kuluva vuosi (esim. 17. syysk.)
    $postYear = (int)date('Y', $timestamp);
    $currentYear = (int)date('Y', $now);

    $months = [
        1 => 'tammik.', 2 => 'helmik.', 3 => 'maalisk.', 4 => 'huhtik.',
        5 => 'toukok.', 6 => 'kesäk.', 7 => 'heinäk.', 8 => 'elok.',
        9 => 'syysk.', 10 => 'lokak.', 11 => 'marrask.', 12 => 'jouluk.'
    ];

    $day = date('j', $timestamp);
    $monthNum = (int)date('n', $timestamp);
    $monthName = $months[$monthNum] ?? date('n', $timestamp) . '.';

    if ($postYear === $currentYear) {
        return $day . '. ' . $monthName;
    }

    // Eri vuosi (esim. 17. syysk. 2025)
    return $day . '. ' . $monthName . ' ' . $postYear;
}

