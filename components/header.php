<?php
/**
 * Reusable Header / Navbar Component
 *
 * @var string|null $headerTitle Main title (e.g. 'Etusivu', 'Ilmoitukset')
 */
$title = $headerTitle ?? 'Mini X';
?>
<header class="app-header" id="appHeader">
    <div class="header-left">
        <button
            type="button"
            class="hamburger-button"
            id="hamburgerButton"
            aria-label="Avaa valikko"
            aria-expanded="false">
            &#9776;
        </button>
    </div>

    <div class="header-center">
        <h1 class="header-title"><?= htmlspecialchars($title) ?></h1>
    </div>

    <div class="header-right">
        <div class="header-dummy" aria-hidden="true"></div>
    </div>
</header>
<?php
unset($headerTitle, $title);
?>
