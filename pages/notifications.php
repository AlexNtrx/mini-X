<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'notifications.php') {
    header("Location: ../index.php?page=notifications");
    exit;
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../functions/init.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
if (!isset($conn) || !$conn) {
    $conn = dbConnect();
}
/** @var mysqli $conn */
$userId = (int)($_SESSION['user_id'] ?? 0);
$notifications = [];
if (isset($conn) && $conn) {
    $notifications = getUserNotifications($conn, $userId);
    // Merkitään ilmoitukset luetuiksi
    markNotificationsAsRead($conn, $userId);
}
$latestId = !empty($notifications) ? (int)$notifications[0]['id'] : 0;
?>
<!DOCTYPE html>
<html lang="fi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ilmoitukset - Mini X</title>
    <link rel="stylesheet" href="./css/main.css?v=1.1.2">
    <link rel="stylesheet" href="./css/sidebar.css?v=1.1.2">
    <link rel="stylesheet" href="./css/header.css?v=1.1.2">
    <link rel="stylesheet" href="./css/notifications.css?v=1.1.2">
</head>

<body>
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="feed">
            <?php
            $headerTitle = 'Ilmoitukset';
            include __DIR__ . '/../components/header.php';
            ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- ilmoitukset -->
            <section class="notifications-list" data-latest-id="<?= $latestId ?>">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notif): ?>
                        <a href="index.php?page=home#<?= $notif['type'] === 'comment' ? 'comments-' : 'post-' ?><?= (int)$notif['post_id'] ?>" class="notification-item <?= !$notif['is_read'] ? 'unread' : '' ?>" data-id="<?= (int)$notif['id'] ?>">
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
                                <span class="notif-time" title="<?= htmlspecialchars($notif['created_at'], ENT_QUOTES, 'UTF-8') ?>"><?= formatTimeAgo($notif['created_at']) ?></span>
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
    <script src="./js/script.js?v=1.1.2"></script>
</body>

</html>