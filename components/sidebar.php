<?php
require_once __DIR__ . "/../functions/init.php";
/** @var mysqli $conn */
$currentPage = $_GET['page'] ?? 'home';
$unreadNotifs = (isset($conn) && isset($_SESSION['user_id'])) ? getUnreadNotificationCount($conn, (int)$_SESSION['user_id']) : 0;
$sidebarAvatarUrl = getUserAvatarUrl($_SESSION['avatar'] ?? null);
?>
<button
    type="button"
    class="hamburger-button"
    id="hamburgerButton"
    aria-label="Avaa valikko"
    aria-expanded="false">
    ☰
</button>
<aside class="sidebar" id="sidebar">
    <div class="logo">Mini X</div>

    <nav>
        <div class="sidebar-user-badge">
            <div class="sidebar-avatar">
                <?php if ($sidebarAvatarUrl): ?>
                    <img src="<?= $sidebarAvatarUrl ?>" alt="Avatar" class="sidebar-avatar-img">
                <?php else: ?>
                    <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?>
                <?php endif; ?>
            </div>
            <div class="sidebar-user-info">
                <span class="sidebar-username"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
                <span class="sidebar-handle">@<?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
            </div>
        </div>
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
        <a href="logout.php" onclick="return confirm('Haluatko varmasti kirjautua ulos?');">Kirjaudu ulos</a>
    </nav>
</aside>
<div class="sidebar-overlay" id="sidebarOverlay"></div> 