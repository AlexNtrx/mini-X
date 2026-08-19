<?php
require_once "functions/init.php";
/** @var mysqli $conn */
$contents = (isset($conn) && $conn) ? getShowContents($conn) : [];
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>miniX</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/kortit.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/post.css">
    <link rel="stylesheet" href="./css/header.css">
</head>
<body>
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include 'components/sidebar.php'; ?>

        <main class="feed">
            <header class="feed-header">
                <h1>Etusivu</h1>
            </header>

            <!-- Luo julkaisu -->
            <?php include 'components/tekstikenttä.php'; ?>

            <!-- Julkaisut -->
            <section class="posts">
                <?php if (!empty($contents)): ?>
                    <?php foreach ($contents as $content): ?>
                        <?php include 'components/kortit.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-feed">Ei julkaisuja vielä.</p>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="./script.js"></script>
</body>
</html>
