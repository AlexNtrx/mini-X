<?php
require_once "functions/init.php";
/** @var mysqli $conn */
$userId = (int)($_SESSION['user_id'] ?? 0);
$notifications = [];
if (isset($conn) && $conn) {
    $notifications = getUserNotifications($conn, $userId);
    // Merkitään ilmoitukset luetuiksi
    markNotificationsAsRead($conn, $userId);
}
?>
<!DOCTYPE html>
<html lang="fi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ilmoitukset - Mini X</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/header.css">
    <link rel="stylesheet" href="./css/notifications.css">
</head>

<body>
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include 'components/sidebar.php'; ?>

        <main class="feed">
            <header class="feed-header">
                <a href="index.php?page=notifications" class="header-tab active">Kaikki ilmoitukset</a>
            </header>
            <!-- ilmoitukset -->
            <section class="notifications-list">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <a href="index.php?page=home#post-<?= (int)$notif['post_id'] ?>" class="notification-item <?= !$notif['is_read'] ? 'unread' : '' ?>">
                            <div class="notif-icon-col">
                                <?php if ($notif['type'] === 'like'): ?>
                                    <span class="notif-icon notif-like">&#10084;&#65039;</span>
                                <?php else: ?>
                                    <span class="notif-icon notif-comment">&#128172;</span>
                                <?php endif; ?>
                            </div>
                            <div class="notif-content-col">
                                <div class="notif-text">
                                    <strong><?= htmlspecialchars($notif['actor_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <?php if ($notif['type'] === 'like'): ?>
                                        <span>tykkäsi julkaisustasi</span>
                                    <?php else: ?>
                                        <span>kommentoi julkaisuasi</span>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($notif['content_preview'])): ?>
                                    <div class="notif-preview">
                                        "<?= htmlspecialchars($notif['content_preview'], ENT_QUOTES, 'UTF-8') ?>"
                                    </div>
                                <?php endif; ?>
                                <span class="notif-time"><?= htmlspecialchars($notif['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    <!-- jos ilmoituksia ei ole, näytä viesti -->
                <?php else: ?>
                    <div class="empty-notifs">
                        <p>Ei ilmoituksia vielä.</p>
                        <span>Kun joku tykkää tai kommentoi julkaisuasi, näet sen täällä.</span>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="./script.js"></script>
</body>

</html>