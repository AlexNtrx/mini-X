<?php
/**
 * Mini-X - Centralized Toast Component
 * Renderöi Toast-ilmoitussäiliön ja välittää mahdolliset PHP-puolen ilmoitukset turvallisesti JavaScriptille.
 */

$toastList = [];

// Tarkistetaan mahdolliset sivukohtaiset PHP-muuttujat
if (!empty($error)) {
    $toastList[] = [
        'type'    => 'error',
        'message' => (string)$error,
        'title'   => 'Virhe'
    ];
}

if (!empty($success)) {
    $toastList[] = [
        'type'    => 'success',
        'message' => (string)$success,
        'title'   => 'Onnistui'
    ];
}

// Haetaan ja tyhjennetään istunnon Flash Toastit
if (function_exists('getFlashToasts')) {
    $flashToasts = getFlashToasts();
    foreach ($flashToasts as $ft) {
        $toastList[] = [
            'type'    => $ft['type'] ?? 'info',
            'message' => (string)($ft['message'] ?? ''),
            'title'   => $ft['title'] ?? null
        ];
    }
}

$initialToastsJson = !empty($toastList) ? htmlspecialchars(json_encode($toastList, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') : '';
?>
<!-- Centralized Toast Notification Container -->
<div id="toast-container" class="toast-container" aria-live="polite"<?= !empty($initialToastsJson) ? ' data-initial-toasts="' . $initialToastsJson . '"' : '' ?>></div>
