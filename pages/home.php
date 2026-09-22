<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'home.php') {
    header("Location: ../index.php?page=home");
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
$availableRooms = function_exists('getAvailableRooms') ? getAvailableRooms() : [];
$activeRoom = trim($_GET['room'] ?? 'all');
if ($activeRoom !== 'all' && !isset($availableRooms[$activeRoom])) {
    $activeRoom = 'all';
}
$contents = (isset($conn) && $conn) ? getShowContents($conn, $activeRoom) : [];
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>miniX</title>
    <link rel="stylesheet" href="./css/main.css?v=<?= filemtime(__DIR__ . '/../css/main.css') ?>">
    <link rel="stylesheet" href="./css/kortit.css?v=<?= filemtime(__DIR__ . '/../css/kortit.css') ?>">
    <link rel="stylesheet" href="./css/sidebar.css?v=<?= filemtime(__DIR__ . '/../css/sidebar.css') ?>">
    <link rel="stylesheet" href="./css/post.css?v=<?= filemtime(__DIR__ . '/../css/post.css') ?>">
    <link rel="stylesheet" href="./css/header.css?v=<?= filemtime(__DIR__ . '/../css/header.css') ?>">
    <link rel="stylesheet" href="./css/toast.css?v=<?= filemtime(__DIR__ . '/../css/toast.css') ?>">
</head>
<body>
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="feed">
            <?php
            $headerTitle = 'Etusivu';
            include __DIR__ . '/../components/header.php';
            ?>

            <!-- Huoneiden suodatuspalkki (Pantip Room Pills) -->
            <div class="rooms-bar">
                <a href="index.php?page=home" class="room-chip <?= $activeRoom === 'all' ? 'active' : '' ?>">
                    <span class="room-chip-text">Kaikki</span>
                </a>
                <?php foreach ($availableRooms as $rKey => $rData): ?>
                    <a href="index.php?page=home&room=<?= urlencode($rKey) ?>" class="room-chip <?= $activeRoom === $rKey ? 'active' : '' ?>" title="<?= htmlspecialchars($rData['desc'], ENT_QUOTES, 'UTF-8') ?>">
                        <span class="room-chip-text"><?= htmlspecialchars($rData['name'], ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Luo julkaisu -->
            <?php include __DIR__ . '/../components/create-post.php'; ?>

            <!-- Julkaisut -->
            <section class="posts">
                <?php if (!empty($contents)): ?>
                    <?php foreach ($contents as $content): ?>
                        <?php include __DIR__ . '/../components/kortit.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-feed"><?= $activeRoom !== 'all' ? 'Ei julkaisuja tässä huoneessa vielä.' : 'Ei julkaisuja vielä.' ?></p>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="./js/toast.js?v=1.1.2"></script>
    <script src="./js/script.js?v=1.1.2"></script>
</body>
</html>
