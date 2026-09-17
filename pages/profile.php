<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'profile.php') {
    header("Location: ../index.php?page=profile");
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
$contents = (isset($conn) && $conn) ? getUserPosts($conn, $userId) : [];

$profileUser = null;
if (isset($conn) && $conn && $userId > 0) {
    $userStmt = $conn->prepare("SELECT avatar, username, display_name FROM users WHERE id = ? LIMIT 1");
    if ($userStmt) {
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $profileUser = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
    }
}
$profileUsername = $profileUser['username'] ?? ($_SESSION['username'] ?? '');
$profileDisplayName = !empty($profileUser['display_name']) ? $profileUser['display_name'] : ($_SESSION['display_name'] ?? $profileUsername);
$avatarUrl = getUserAvatarUrl($profileUser['avatar'] ?? null);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($profileDisplayName) ?> - Mini X</title>
    <link rel="stylesheet" href="./css/main.css?v=1.1.1">
    <link rel="stylesheet" href="./css/kortit.css?v=1.1.1">
    <link rel="stylesheet" href="./css/sidebar.css?v=1.1.1">
    <link rel="stylesheet" href="./css/profile.css?v=1.1.1">
</head>
<body>
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="feed">
            <!-- Profiili Header -->
            <header class="profile-header">
                <button
                    type="button"
                    class="hamburger-button"
                    id="hamburgerButton"
                    aria-label="Avaa valikko"
                    aria-expanded="false">
                    &#9776;
                </button>
                <div class="header-info">
                    <h2><?= htmlspecialchars($profileDisplayName) ?></h2>
                    <span><?= count($contents) ?> julkaisua</span>
                </div>
            </header>

            <!-- Profiilikortti / Banner -->
            <div class="profile-card">
                <div class="profile-banner"></div>
                <div class="profile-details">
                    <div class="profile-avatar-row">
                        <div class="profile-avatar-wrapper">
                            <div class="profile-avatar <?= $avatarUrl ? 'clickable-avatar' : '' ?>" id="profile-avatar-container" <?= $avatarUrl ? 'onclick="openAvatarModal(\'' . htmlspecialchars($avatarUrl, ENT_QUOTES) . '\')"' : '' ?> title="<?= $avatarUrl ? 'Näytä profiilikuva' : '' ?>">
                                <?php if ($avatarUrl): ?>
                                    <img src="<?= $avatarUrl ?>" alt="Profiilikuva" class="profile-avatar-img" id="profile-avatar-img">
                                <?php else: ?>
                                    <span id="profile-avatar-fallback"><?= getUserInitials($profileDisplayName) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="profile-actions">
                            <a href="index.php?page=setting" class="profile-edit-btn">Muokkaa profiilia</a>
                        </div>
                    </div>

                    <div class="profile-name-section">
                        <h3 class="profile-display-name"><?= htmlspecialchars($profileDisplayName) ?></h3>
                        <span class="profile-handle">@<?= htmlspecialchars($profileUsername) ?></span>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Omat julkaisut -->
            <div class="profile-tabs">
                <div class="profile-tab active">Omat julkaisut</div>
            </div>

            <!-- Julkaisut (Vain omat julkaisut) -->
            <section class="posts">
                <?php if (!empty($contents)): ?>
                    <?php foreach ($contents as $content): ?>
                        <?php include __DIR__ . '/../components/kortit.php'; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-posts">
                        <p>Sinulla ei ole vielä julkaisuja.</p>
                        <a href="index.php?page=home" class="create-first-post-btn">Luo ensimmäinen julkaisusi</a>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="./js/script.js?v=1.1.1"></script>
</body>
</html>
