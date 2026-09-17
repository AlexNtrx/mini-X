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
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/kortit.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/profile.css">
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

                        <!-- Avatar Actions Form -->
                        <form method="POST" enctype="multipart/form-data" class="profile-avatar-form" id="profile-avatar-form">
                            <input type="file" id="profile-avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;" onchange="previewProfileAvatar(this)">
                            
                            <div class="profile-avatar-actions">
                                <div id="avatar-save-actions" class="avatar-save-actions" style="display: none;">
                                    <button type="submit" name="update_avatar" class="save-avatar-btn">Tallenna uusi kuva</button>
                                    <button type="button" class="cancel-avatar-btn" onclick="cancelProfileAvatar()">Peruuta</button>
                                </div>
                                <label for="profile-avatar-input" class="change-avatar-btn" id="btn-change-avatar">
                                    Vaihda kuva
                                </label>
                                <?php if ($avatarUrl): ?>
                                    <button type="submit" name="delete_avatar" class="delete-avatar-btn" id="btn-delete-avatar" onclick="return confirm('Haluatko varmasti poistaa profiilikuvasi?');">Poista kuva</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <div class="profile-name-section">
                        <div class="profile-name-header">
                            <div>
                                <h3 class="profile-display-name"><?= htmlspecialchars($profileDisplayName) ?></h3>
                                <span class="profile-handle">@<?= htmlspecialchars($profileUsername) ?></span>
                            </div>
                            <button type="button" class="profile-edit-toggle-btn" id="btn-toggle-profile-edit" data-form-target="profile-edit-form-wrapper" onclick="toggleProfileEditForm()">
                                Muokkaa nimeä
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <!-- Edit Name Form (piilotettu oletuksena) -->
                    <div id="profile-edit-form-wrapper" style="display: none;">
                        <form method="POST" class="profile-edit-form">
                            <div class="form-group">
                                <label for="display_name">Muokkaa nimeä:</label>
                                <div class="input-with-button">
                                    <input type="text" id="display_name" name="display_name" value="<?= htmlspecialchars($profileDisplayName) ?>" required minlength="1" maxlength="25" placeholder="Nimi (enintään 25 merkkiä)">
                                    <button type="submit" name="update_display_name" class="save-profile-btn">Tallenna</button>
                                    <button type="button" class="cancel-avatar-btn" onclick="toggleProfileEditForm(false)">Peruuta</button>
                                </div>
                            </div>
                        </form>
                    </div>
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

    <script src="./js/script.js"></script>
    <script>
        function toggleProfileEditForm(force) {
            const wrapper = document.getElementById('profile-edit-form-wrapper');
            const toggleBtn = document.getElementById('btn-toggle-profile-edit');
            if (!wrapper) return;
            const isCurrentlyOpen = wrapper.style.display !== 'none';
            const nextState = force !== undefined ? force : !isCurrentlyOpen;
            wrapper.style.display = nextState ? 'block' : 'none';
            if (toggleBtn) {
                toggleBtn.textContent = nextState ? 'Sulje' : 'Muokkaa nimeä';
            }
            if (nextState) {
                const input = document.getElementById('display_name');
                if (input) setTimeout(() => input.focus(), 100);
            } else {
                const form = wrapper.querySelector('form');
                if (form) form.reset();
            }
        }

        // Suljetaan profiilin muokkauslomake, jos klikataan sen ulkopuolelle
        document.addEventListener('click', (e) => {
            const wrapper = document.getElementById('profile-edit-form-wrapper');
            const toggleBtn = document.getElementById('btn-toggle-profile-edit');
            if (wrapper && wrapper.style.display !== 'none') {
                const wasClickInside = wrapper.contains(e.target);
                const wasClickOnTrigger = toggleBtn && toggleBtn.contains(e.target);
                if (!wasClickInside && !wasClickOnTrigger) {
                    toggleProfileEditForm(false);
                }
            }
        });

        // Suljetaan muokkauslomake painettaessa Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const wrapper = document.getElementById('profile-edit-form-wrapper');
                if (wrapper && wrapper.style.display !== 'none') {
                    toggleProfileEditForm(false);
                }
            }
        });

        <?php if (!empty($error) && isset($_POST['update_display_name'])): ?>
        document.addEventListener('DOMContentLoaded', () => {
            toggleProfileEditForm(true);
        });
        <?php endif; ?>

        const originalAvatarHtml = document.getElementById('profile-avatar-container').innerHTML;

        function previewProfileAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById('profile-avatar-container');
                    if (container) {
                        container.classList.add('clickable-avatar');
                        container.setAttribute('title', 'Näytä profiilikuva');
                        container.onclick = function() {
                            openAvatarModal(e.target.result);
                        };
                        container.innerHTML = `<img src="${e.target.result}" alt="Profiilikuva" class="profile-avatar-img">`;
                    }
                    
                    const saveActions = document.getElementById('avatar-save-actions');
                    const changeBtn = document.getElementById('btn-change-avatar');
                    const deleteBtn = document.getElementById('btn-delete-avatar');
                    
                    if (saveActions) saveActions.style.display = 'inline-flex';
                    if (changeBtn) changeBtn.style.display = 'none';
                    if (deleteBtn) deleteBtn.style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function cancelProfileAvatar() {
            const input = document.getElementById('profile-avatar-input');
            if (input) input.value = '';
            
            const container = document.getElementById('profile-avatar-container');
            if (container) {
                container.innerHTML = originalAvatarHtml;
                <?php if ($avatarUrl): ?>
                container.classList.add('clickable-avatar');
                container.setAttribute('title', 'Näytä profiilikuva');
                container.onclick = function() {
                    openAvatarModal(<?= json_encode($avatarUrl) ?>);
                };
                <?php else: ?>
                container.classList.remove('clickable-avatar');
                container.removeAttribute('title');
                container.onclick = null;
                <?php endif; ?>
            }
            
            const saveActions = document.getElementById('avatar-save-actions');
            const changeBtn = document.getElementById('btn-change-avatar');
            const deleteBtn = document.getElementById('btn-delete-avatar');
            
            if (saveActions) saveActions.style.display = 'none';
            if (changeBtn) changeBtn.style.display = 'inline-flex';
            if (deleteBtn) deleteBtn.style.display = 'inline-flex';
        }
    </script>
</body>
</html>
