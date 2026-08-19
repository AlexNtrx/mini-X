<?php
require_once "functions/init.php";
/** @var mysqli $conn */
$userId = (int)($_SESSION['user_id'] ?? 0);
$contents = (isset($conn) && $conn) ? getUserPosts($conn, $userId) : [];
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
                        <div class="profile-avatar">
                            <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?>
                        </div>
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
    <script src="./script.js"></script>
</body>
</html>
