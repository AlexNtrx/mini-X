<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'profile.php') {
    $qs = !empty($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : '';
    header("Location: ../index.php?page=profile" . $qs);
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
$loggedInUserId = (int)($_SESSION['user_id'] ?? 0);
$targetUsername = trim($_GET['u'] ?? '');
$targetId = (int)($_GET['id'] ?? 0);

$profileUser = null;
if (!empty($targetUsername)) {
    $userStmt = $conn->prepare("SELECT id, avatar, username, display_name FROM users WHERE username = ? AND deleted_at IS NULL LIMIT 1");
    if ($userStmt) {
        $userStmt->bind_param("s", $targetUsername);
        $userStmt->execute();
        $profileUser = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
    }
} elseif ($targetId > 0) {
    $userStmt = $conn->prepare("SELECT id, avatar, username, display_name FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    if ($userStmt) {
        $userStmt->bind_param("i", $targetId);
        $userStmt->execute();
        $profileUser = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
    }
} else {
    // Oletuksena kirjautunut käyttäjä
    $userStmt = $conn->prepare("SELECT id, avatar, username, display_name FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    if ($userStmt) {
        $userStmt->bind_param("i", $loggedInUserId);
        $userStmt->execute();
        $profileUser = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
    }
}

$userFound = ($profileUser !== null);
$viewUserId = $userFound ? (int)$profileUser['id'] : 0;
$isOwnProfile = ($viewUserId === $loggedInUserId);

$profileUsername = $profileUser['username'] ?? '';
$profileDisplayName = !empty($profileUser['display_name']) ? $profileUser['display_name'] : $profileUsername;
$avatarUrl = $userFound ? getUserAvatarUrl($profileUser['avatar'] ?? null) : null;

$contents = ($userFound && isset($conn) && $conn) ? getUserPosts($conn, $viewUserId) : [];
$customization = ($userFound && isset($conn) && $conn) ? getUserCustomization($conn, $viewUserId) : [
    'theme_accent'   => '#1d9bf0',
    'theme_banner'   => '',
    'theme_bg_type'  => 'default',
    'bio'            => '',
    'song_title'     => '',
    'song_artist'    => '',
    'song_artwork'   => '',
    'song_url'       => '',
];

$bannerUrl = $userFound ? getUserBannerUrl($customization['theme_banner'] ?? null) : null;
$presetThemes = getHi5PresetThemes();

// Accent-värin käsittely ja RGB-arvojen laskenta
$accentHex = sanitizeHexColor($customization['theme_accent'] ?? '#1d9bf0');
$r = hexdec(substr($accentHex, 1, 2));
$g = hexdec(substr($accentHex, 3, 2));
$b = hexdec(substr($accentHex, 5, 2));
$accentRgb = "$r, $g, $b";

// Taustatyylin määrittely
$bgType = $customization['theme_bg_type'] ?? 'default';
$bgVal = $customization['theme_bg_val'] ?? '';
$bgStyle = getProfileBackgroundStyle($bgType, $bgVal);
$bgPresets = getHi5BackgroundPresets();
$currentBgType = $bgType;
$currentBgVal = $bgVal;
$customBgUrl = getUserBackgroundUrl($currentBgVal);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $userFound ? htmlspecialchars($profileDisplayName) . ' (@' . htmlspecialchars($profileUsername) . ')' : 'Käyttäjää ei löydy' ?> - Mini X</title>
    <link rel="stylesheet" href="./css/main.css?v=1.1.2">
    <link rel="stylesheet" href="./css/kortit.css?v=1.1.2">
    <link rel="stylesheet" href="./css/sidebar.css?v=1.1.2">
    <link rel="stylesheet" href="./css/profile.css?v=1.2.0">
    <link rel="stylesheet" href="./css/profile-modal.css?v=1.3.2">
    <?php if (!empty($customization['song_url'])): ?>
        <link rel="stylesheet" href="./css/retro-player.css?v=1.4.2">
    <?php endif; ?>

    <style>
        :root {
            --profile-accent: <?= $accentHex ?>;
            --profile-accent-rgb: <?= $accentRgb ?>;
        }

        .profile-avatar {
            border-color: var(--profile-accent) !important;
        }

        .profile-tab.active {
            color: var(--profile-accent) !important;
            border-color: var(--profile-accent) !important;
        }

        .profile-edit-btn {
            border-color: var(--profile-accent) !important;
            color: var(--profile-accent) !important;
        }

        .profile-edit-btn:hover {
            background-color: rgba(var(--profile-accent-rgb), 0.15) !important;
        }

        <?php if (!empty($bgStyle)): ?>
        html, body.hi5-profile-page {
            <?= $bgStyle ?>
        }
        body.hi5-profile-page .feed {
            background-color: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        <?php endif; ?>
    </style>
</head>
<body class="<?= !empty($bgStyle) ? 'hi5-profile-page' : '' ?>">
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="feed">
            <?php if (!$userFound): ?>
                <!-- Käyttäjää ei löytynyt -->
                <header class="profile-header">
                    <button type="button" class="hamburger-button" id="hamburgerButton" aria-label="Avaa valikko">&#9776;</button>
                    <div class="header-info">
                        <h2>Profiili</h2>
                    </div>
                </header>
                <div style="padding: 40px 20px; text-align: center;">
                    <h3 style="color: #e7e9ea; margin-bottom: 12px;">Käyttäjää ei löytynyt</h3>
                    <p style="color: #71767b; margin-bottom: 24px;">Etsimääsi käyttäjätunnusta ei ole olemassa tai tili on poistettu.</p>
                    <a href="index.php?page=selaa" class="setting-btn-primary" style="text-decoration: none; display: inline-block;">Selaa käyttäjiä</a>
                </div>
            <?php else: ?>
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
                        <span><?= count($contents) ?> <?= count($contents) === 1 ? 'julkaisu' : 'julkaisua' ?></span>
                    </div>
                </header>

                <!-- Profiilikortti / Banner -->
                <div class="profile-card">
                    <div class="profile-banner" <?= !empty($bannerUrl) ? 'style="background-image: url(\'' . $bannerUrl . '\'); background-size: cover; background-position: center ' . (int)($customization['banner_pos_y'] ?? 50) . '%; height: 160px;"' : '' ?>></div>
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
                                <?php if ($isOwnProfile): ?>
                                    <button type="button" class="profile-edit-btn" onclick="openEditProfileModal()">Muokkaa profiilia</button>
                                <?php else: ?>
                                    <span style="font-size: 12px; color: var(--profile-accent); font-weight: 600; padding: 6px 14px; border-radius: 9999px; border: 1px solid var(--profile-accent); background: rgba(var(--profile-accent-rgb), 0.1);">
                                        Hi5 Profile
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="profile-name-section">
                            <h3 class="profile-display-name"><?= htmlspecialchars($profileDisplayName) ?></h3>
                            <span class="profile-handle">@<?= htmlspecialchars($profileUsername) ?></span>
                        </div>

                        <!-- Hi5 Bio / Status -->
                        <?php if (!empty($customization['bio'])): ?>
                            <div class="profile-bio-box">
                                <span class="profile-bio-badge">STATUS</span>
                                <span class="profile-bio-text"><?= htmlspecialchars($customization['bio']) ?></span>
                            </div>
                        <?php elseif ($isOwnProfile): ?>
                            <button type="button" class="profile-add-hint-btn" onclick="openEditProfileModal()">
                                + Lisää tilaviesti
                            </button>
                        <?php endif; ?>

                        <!-- Instagram Story Style Music Sticker Component -->
                        <?php include __DIR__ . '/../components/music-player.php'; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Julkaisut -->
                <div class="profile-tabs">
                    <div class="profile-tab active">
                        <?= $isOwnProfile ? 'Omat julkaisut' : 'Käyttäjän julkaisut' ?>
                    </div>
                </div>

                <section class="posts">
                    <?php if (!empty($contents)): ?>
                        <?php foreach ($contents as $content): ?>
                            <?php include __DIR__ . '/../components/kortit.php'; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-posts">
                            <p><?= $isOwnProfile ? 'Sinulla ei ole vielä julkaisuja.' : 'Käyttäjällä ei ole vielä julkaisuja.' ?></p>
                            <?php if ($isOwnProfile): ?>
                                <a href="index.php?page=home" class="create-first-post-btn">Luo ensimmäinen julkaisusi</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <?php if ($isOwnProfile): ?>
        <?php include __DIR__ . '/../components/profile-modal.php'; ?>
        <?php include __DIR__ . '/../components/avatar-cropper-modal.php'; ?>
    <?php endif; ?>

    <script src="./js/script.js?v=1.1.2"></script>
    <?php if (!empty($customization['song_url'])): ?>
        <script src="./js/retro-player.js?v=1.4.2"></script>
    <?php endif; ?>
    <?php if ($isOwnProfile): ?>
        <script src="./js/profile-modal.js?v=1.3.3"></script>
    <?php endif; ?>
</body>
</html>
