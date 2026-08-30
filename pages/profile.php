<?php
require_once "functions/init.php";
/** @var mysqli $conn */
$userId = (int)($_SESSION['user_id'] ?? 0);
$contents = (isset($conn) && $conn) ? getUserPosts($conn, $userId) : [];

$profileUser = null;
if (isset($conn) && $conn && $userId > 0) {
    $userStmt = $conn->prepare("SELECT avatar FROM users WHERE id = ? LIMIT 1");
    if ($userStmt) {
        $userStmt->bind_param("i", $userId);
        $userStmt->execute();
        $profileUser = $userStmt->get_result()->fetch_assoc();
        $userStmt->close();
    }
}
$avatarUrl = getUserAvatarUrl($profileUser['avatar'] ?? null);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profiili - Mini X</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/kortit.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/profile.css">
</head>
<body>
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include 'components/sidebar.php'; ?>

        <main class="feed">
            <!-- Profiili Header -->
            <header class="profile-header">
                <div class="header-info">
                    <h2><?= htmlspecialchars($_SESSION['username'] ?? '') ?></h2>
                    <span><?= count($contents) ?> julkaisua</span>
                </div>
            </header>

            <!-- Profiilikortti / Banner -->
            <div class="profile-card">
                <div class="profile-banner"></div>
                <div class="profile-details">
                    <div class="profile-avatar-row">
                        <div class="profile-avatar-wrapper">
                            <div class="profile-avatar" id="profile-avatar-container">
                                <?php if ($avatarUrl): ?>
                                    <img src="<?= $avatarUrl ?>" alt="Profiilikuva" class="profile-avatar-img" id="profile-avatar-img">
                                <?php else: ?>
                                    <span id="profile-avatar-fallback"><?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?></span>
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
                        <h3 class="profile-display-name"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></h3>
                        <span class="profile-handle">@<?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <!-- Edit Name Form -->
                    <form method="POST" class="profile-edit-form">
                        <div class="form-group">
                            <label for="username">Muokkaa nimeä:</label>
                            <div class="input-with-button">
                                <input type="text" id="username" name="username" value="<?= htmlspecialchars($_SESSION['username'] ?? '') ?>" required minlength="3">
                                <button type="submit" name="update_profile" class="save-profile-btn">Tallenna</button>
                            </div>
                        </div>
                    </form>
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
                        <?php include 'components/kortit.php'; ?>
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
        const originalAvatarHtml = document.getElementById('profile-avatar-container').innerHTML;

        function previewProfileAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById('profile-avatar-container');
                    container.innerHTML = `<img src="${e.target.result}" alt="Profiilikuva" class="profile-avatar-img">`;
                    
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
            if (container) container.innerHTML = originalAvatarHtml;
            
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
