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
$contents = (isset($conn) && $conn) ? getShowContents($conn) : [];
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>miniX</title>
    <link rel="stylesheet" href="./css/main.css?v=1.1.1">
    <link rel="stylesheet" href="./css/kortit.css?v=1.1.1">
    <link rel="stylesheet" href="./css/sidebar.css?v=1.1.1">
    <link rel="stylesheet" href="./css/post.css?v=1.1.1">
    <link rel="stylesheet" href="./css/header.css?v=1.1.1">
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

            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="margin: 12px 16px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" style="margin: 12px 16px;"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- Luo julkaisu -->
            <?php include __DIR__ . '/../components/tekstikenttä.php'; ?>

            <!-- Julkaisut -->
            <section class="posts">
                <?php if (!empty($contents)): ?>
                    <?php foreach ($contents as $content): ?>
                        <?php include __DIR__ . '/../components/kortit.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-feed">Ei julkaisuja vielä.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="./js/script.js"></script>
</body>
</html>
