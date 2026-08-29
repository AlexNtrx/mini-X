<?php
require_once "functions/init.php";
/** @var mysqli $conn */
$userId = (int)($_SESSION['user_id'] ?? 0);

$currentUser = null;
if (isset($conn) && $conn && $userId > 0) {
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $currentUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
$username = $currentUser['username'] ?? ($_SESSION['username'] ?? '');
$email = $currentUser['email'] ?? '';
$createdAt = !empty($currentUser['created_at']) ? date("d.m.Y", strtotime($currentUser['created_at'])) : '';
?>
<!DOCTYPE html>
<html lang="fi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asetukset - Mini X</title>
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/header.css">
    <link rel="stylesheet" href="./css/setting.css">
</head>

<body>
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include 'components/sidebar.php'; ?>

        <main class="feed">
            <header class="feed-header">
                <h1>Asetukset</h1>
            </header>

            <div class="setting-container">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <!-- Tilitiedot -->
                <section class="setting-section">
                    <h2 class="setting-section-title">Tilitiedot</h2>

                    <div class="setting-item">
                        <div class="setting-item-info">
                            <span class="setting-label">Käyttäjätunnus</span>
                            <span class="setting-value">@<?= htmlspecialchars($username) ?></span>
                        </div>
                        <a href="index.php?page=profile" class="setting-action-link">Muokkaa profiilia</a>
                    </div>

                    <div class="setting-item">
                        <div class="setting-item-info">
                            <span class="setting-label">Sähköposti</span>
                            <span class="setting-value"><?= !empty($email) ? htmlspecialchars($email) : '<span class="text-muted">Ei asetettu</span>' ?></span>
                        </div>
                    </div>

                    <form method="POST" class="setting-form">
                        <div class="form-group">
                            <label for="setting-email">Päivitä sähköpostiosoite</label>
                            <div class="input-with-button">
                                <input type="email" id="setting-email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="esim. kayttaja@example.com" required>
                                <button type="submit" name="update_email" class="setting-btn-primary">Tallenna</button>
                            </div>
                        </div>
                    </form>

                    <?php if (!empty($createdAt)): ?>
                    <div class="setting-item">
                        <div class="setting-item-info">
                            <span class="setting-label">Liittynyt</span>
                            <span class="setting-value"><?= htmlspecialchars($createdAt) ?></span>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>

                <!-- Salasanan vaihto -->
                <section class="setting-section">
                    <h2 class="setting-section-title">Turvallisuus</h2>
                    <form method="POST" class="setting-form">
                        <div class="form-group">
                            <label for="setting-password">Uusi salasana</label>
                            <input type="password" id="setting-password" name="password" placeholder="Vähintään 6 merkkiä" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="setting-confirm-password">Vahvista uusi salasana</label>
                            <input type="password" id="setting-confirm-password" name="confirm_password" placeholder="Toista uusi salasana" required minlength="6">
                        </div>
                        <div class="setting-form-actions">
                            <button type="submit" name="update_password" class="setting-btn-primary">Vaihda salasana</button>
                        </div>
                    </form>
                </section>

                <!-- Tilin hallinta -->
                <section class="setting-section">
                    <h2 class="setting-section-title">Tilin hallinta</h2>
                    <div class="setting-item">
                        <div class="setting-item-info">
                            <span class="setting-label">Kirjaudu ulos</span>
                            <span class="setting-desc">Päätä nykyinen istuntosi</span>
                        </div>
                        <a href="logout.php" class="setting-btn-secondary">Kirjaudu ulos</a>
                    </div>
                </section>
            </div>
        </main>
    </div>
    <script src="./script.js"></script>
</body>

</html>