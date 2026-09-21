<?php
/**
 * Mini-X - Toast Notification Functions
 * Hallinnoi sovelluksen laajuisia flash-ilmoituksia istunnossa (Session)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Lisää uuden Toast-ilmoituksen istuntoon (Flash message)
 *
 * @param string $message Ilmoitusteksti
 * @param string $type Ilmoitustyyppi ('success', 'error', 'info', 'warning')
 * @param string|null $title Valinnainen otsikko
 * @return void
 */
function setFlashToast(string $message, string $type = 'success', ?string $title = null): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['flash_toasts']) || !is_array($_SESSION['flash_toasts'])) {
        $_SESSION['flash_toasts'] = [];
    }

    $_SESSION['flash_toasts'][] = [
        'type'    => in_array($type, ['success', 'error', 'info', 'warning'], true) ? $type : 'info',
        'message' => $message,
        'title'   => $title
    ];
}

/**
 * Hakee kaikki odottavat Toast-ilmoitukset ja tyhjentää ne istunnosta
 *
 * @return array
 */
function getFlashToasts(): array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $toasts = $_SESSION['flash_toasts'] ?? [];
    unset($_SESSION['flash_toasts']);

    return is_array($toasts) ? $toasts : [];
}

/**
 * Tarkistaa onko odottavia Toast-ilmoituksia
 *
 * @return bool
 */
function hasFlashToasts(): bool
{
    return !empty($_SESSION['flash_toasts']);
}
