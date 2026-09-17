<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'selaa.php') {
    $qs = !empty($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : '';
    header("Location: ../index.php?page=selaa" . $qs);
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
$q = trim($_GET['q'] ?? '');
$allUsers = (isset($conn) && $conn) ? getAllUsers($conn) : [];

// Näytetään pikavalinnoissa vain admin-käyttäjä
$displayUsers = array_values(array_filter($allUsers, function ($u) {
    return strtolower($u['username'] ?? '') === 'admin';
}));
if (empty($displayUsers) && !empty($allUsers)) {
    $displayUsers = [reset($allUsers)];
}

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
    <link rel="stylesheet" href="./css/main.css?v=1.2.0">
    <link rel="stylesheet" href="./css/kortit.css?v=1.2.0">
    <link rel="stylesheet" href="./css/sidebar.css?v=1.2.0">
    <link rel="stylesheet" href="./css/header.css?v=1.2.0">
    <link rel="stylesheet" href="./css/selaa.css?v=1.2.0">
</head>
<body>
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="feed">
            <?php
            $headerTitle = 'Selaa käyttäjiä';
            include __DIR__ . '/../components/header.php';
            ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="margin: 12px 16px;"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" style="margin: 12px 16px;"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

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
                <?php if (!empty($displayUsers)): ?>
                    <div class="user-chips-section">
                        <div class="user-chips-title">Käyttäjät:</div>
                        <div class="user-chips">
                            <?php foreach ($displayUsers as $u): ?>
                                <a 
                                    href="index.php?page=selaa&q=<?= urlencode($u['username']) ?>" 
                                    class="user-chip <?= ($q === $u['username']) ? 'active' : '' ?>"
                                    title="@<?= htmlspecialchars($u['username']) ?>"
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
                        <?php include __DIR__ . '/../components/kortit.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty-search">
                        <?= $q !== '' ? 'Ei julkaisuja käyttäjältä "@' . htmlspecialchars($q) . '".' : 'Kirjoita käyttäjänimi tai valitse käyttäjä ylhäältä nähdäksesi julkaisut.' ?>
                    </p>
                <?php endif; ?>
            </section>
        </main>
    </div>
    <script src="./js/script.js"></script>
</body>
</html>
