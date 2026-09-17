<?php
require_once __DIR__ . "/../functions/init.php";
/** @var mysqli $conn */
$currentPage = $_GET['page'] ?? 'home';
$unreadNotifs = (isset($conn) && isset($_SESSION['user_id'])) ? getUnreadNotificationCount($conn, (int)$_SESSION['user_id']) : 0;
$sidebarAvatarUrl = getUserAvatarUrl($_SESSION['avatar'] ?? null);
$sidebarDisplayName = $_SESSION['display_name'] ?? ($_SESSION['username'] ?? '');
$sidebarUsername = $_SESSION['username'] ?? '';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">Mini X</div>
        <button
            type="button"
            class="sidebar-close-btn"
            id="sidebarCloseButton"
            aria-label="Sulje valikko">
            &times;
        </button>
    </div>

    <nav>
        <a href="index.php?page=profile" class="sidebar-user-badge" aria-label="Siirry profiiliin" title="Näytä profiili">
            <div class="sidebar-avatar">
                <?php if ($sidebarAvatarUrl): ?>
                    <img src="<?= $sidebarAvatarUrl ?>" alt="Avatar" class="sidebar-avatar-img">
                <?php else: ?>
                    <?= getUserInitials($sidebarDisplayName) ?>
                <?php endif; ?>
            </div>
            <div class="sidebar-user-info">
                <span class="sidebar-username"><?= htmlspecialchars($sidebarDisplayName) ?></span>
                <span class="sidebar-handle">@<?= htmlspecialchars($sidebarUsername) ?></span>
            </div>
        </a>
        <a href="index.php?page=home" class="<?= $currentPage === 'home' ? 'active' : '' ?>">Etusivu</a>
        <a href="index.php?page=selaa" class="<?= $currentPage === 'selaa' ? 'active' : '' ?>">Selaa</a>
        <a href="index.php?page=notifications" class="nav-item-notif <?= $currentPage === 'notifications' ? 'active' : '' ?>">
            Ilmoitukset
            <?php if ($unreadNotifs > 0): ?>
                <span class="nav-badge"><?= $unreadNotifs ?></span>
            <?php endif; ?>
        </a>
        <a href="index.php?page=profile" class="<?= $currentPage === 'profile' ? 'active' : '' ?>">Profiili</a>
        <a href="index.php?page=setting" class="<?= $currentPage === 'setting' ? 'active' : '' ?>">Asetukset</a>
        <a href="index.php?page=privacy" class="<?= $currentPage === 'privacy' ? 'active' : '' ?>">Tietosuojaseloste</a>
        <a href="logout.php" onclick="return confirm('Haluatko varmasti kirjautua ulos?');">Kirjaudu ulos</a>
    </nav>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div>