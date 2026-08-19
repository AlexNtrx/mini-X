<?php
require_once "functions/init.php";
/** @var mysqli $conn */
$q = trim($_GET['q'] ?? '');
$allUsers = (isset($conn) && $conn) ? getAllUsers($conn) : [];

// taulukko, johon tallennetaan haetut julkaisut
$contents = [];

// Jos hakukenttä ei ole tyhjä, hae käyttäjän julkaisut
if ($q !== '' && isset($conn) && $conn) {
    $contents = searchPostsByUsername($conn, $q);
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selaa - Mini X</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/kortit.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/header.css">
    <link rel="stylesheet" href="./css/selaa.css">
</head>
<body>
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include 'components/sidebar.php'; ?>

        <main class="feed">
            <header class="feed-header">
                <a href="index.php?page=selaa" class="header-tab active">Selaa käyttäjiä</a>
            </header>

            <!-- Hakukenttä -->
            <section class="search-container">
                <form method="GET" action="index.php" class="search-form">
                    <input type="hidden" name="page" value="selaa">
                    <div class="search-input-wrapper">
                        <input 
                            type="text" 
                            name="q" 
                            class="search-input" 
                            placeholder="Hae käyttäjänimellä" 
                            value="<?= htmlspecialchars($q) ?>" 
                            autocomplete="off"
                        >
                    </div>
                    <button type="submit" class="search-btn">Hae</button>
                </form>

                <!-- Nopeat käyttäjävalinnat -->
                <?php if (!empty($allUsers)): ?>
                    <div class="user-chips-section">
                        <div class="user-chips-title">Käyttäjät:</div>
                        <div class="user-chips">
                            <?php foreach ($allUsers as $u): ?>
                                <a 
                                    href="index.php?page=selaa&q=<?= urlencode($u['username']) ?>" 
                                    class="user-chip <?= ($q === $u['username']) ? 'active' : '' ?>"
                                >
                                    @<?= htmlspecialchars($u['username']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Julkaisut -->
            <section class="posts">
                <?php if (!empty($contents)): ?>
                    <?php foreach ($contents as $content): ?>
                        <?php include 'components/kortit.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-search">
                        <?= $q !== '' ? 'Ei julkaisuja käyttäjältä "@' . htmlspecialchars($q) . '".' : 'Kirjoita käyttäjänมิ tai valitse käyttäjä ylhäältä nähdäksesi julkaisut.' ?>
                    </p>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="./script.js"></script>
</body>
</html>
