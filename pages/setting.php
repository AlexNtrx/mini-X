<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'setting.php') {
    header("Location: ../index.php?page=setting");
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

$currentUser = null;
if (isset($conn) && $conn && $userId > 0) {
    $stmt = $conn->prepare("SELECT id, username, email, avatar, created_at FROM users WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $currentUser = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}
$username = $currentUser['username'] ?? ($_SESSION['username'] ?? '');
$email = $currentUser['email'] ?? '';
$avatarUrl = getUserAvatarUrl($currentUser['avatar'] ?? null);
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
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="feed">
            <?php
            $headerTitle = 'Asetukset';
            include __DIR__ . '/../components/header.php';
            ?>

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

                    <!-- Profiilikuva -->
                    <div class="setting-item setting-item-avatar">
                        <div class="setting-avatar-left">
                            <div class="setting-avatar-preview" id="setting-avatar-container">
                                <?php if ($avatarUrl): ?>
                                    <img src="<?= $avatarUrl ?>" alt="Profiilikuva" class="avatar-preview-img" id="setting-avatar-img">
                                <?php else: ?>
                                    <div class="avatar-fallback" id="setting-avatar-fallback">
                                        <?= getUserInitials($username) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="setting-item-info">
                                <span class="setting-username-title"><?= htmlspecialchars($username) ?></span>
                                <span class="setting-handle">@<?= htmlspecialchars($username) ?></span>
                            </div>
                        </div>

                        <form method="POST" enctype="multipart/form-data" class="setting-avatar-form" id="setting-avatar-form">
                            <input type="file" id="setting-avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;" onchange="previewSettingAvatar(this)">
                            
                            <div class="setting-avatar-actions">
                                <div id="setting-avatar-save-actions" class="setting-avatar-save-actions" style="display: none;">
                                    <button type="submit" name="update_avatar" class="setting-btn-primary">Tallenna uusi kuva</button>
                                    <button type="button" class="setting-btn-secondary" onclick="cancelSettingAvatar()">Peruuta</button>
                                </div>
                                <label for="setting-avatar-input" class="setting-btn-secondary" id="btn-setting-change-avatar">
                                    Vaihda kuva
                                </label>
                                <?php if ($avatarUrl): ?>
                                    <button type="submit" name="delete_avatar" class="setting-btn-danger" id="btn-setting-delete-avatar" onclick="return confirm('Haluatko varmasti poistaa profiilikuvasi?');">Poista kuva</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <form method="POST" class="setting-form">
                        <div class="form-group">
                            <label for="setting-username">Päivitä käyttäjätunnus</label>
                            <div class="input-with-button">
                                <input type="text" id="setting-username" name="username" value="<?= htmlspecialchars($username) ?>" placeholder="esim. kayttaja" required minlength="3" maxlength="20">
                                <button type="submit" name="update_profile" class="setting-btn-primary">Tallenna</button>
                            </div>
                        </div>
                    </form>

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
                <section class="setting-section setting-danger-zone">
                    <h2 class="setting-section-title">Tilin hallinta</h2>
                    <div class="setting-item">
                        <div class="setting-item-info">
                            <span class="setting-label">Poista tili</span>
                            <span class="setting-desc">Poista tilisi ja piilota julkaisusi</span>
                        </div>
                        <button type="button" id="btn-open-delete-modal" class="setting-btn-danger">Poista tili</button>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Poista tili -vahvistusmodaali -->
    <div id="delete-account-modal" class="setting-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
        <div class="setting-modal-card">
            <div class="setting-modal-header">
                <h3>Haluatko poistaa tilisi?</h3>
            </div>
            <p class="setting-modal-desc">
                Tämä toiminto asettaa tilisi 30 päivän tauolle ja piilottaa profiilisi muilta. Voit palauttaa tilisi kirjautumalla sisään 30 päivän kuluessa, jonka jälkeen tili poistetaan pysyvästi. Vahvista antamalla salasanasi.
            </p>
            <form method="POST" id="form-delete-account" class="setting-form">
                <div class="form-group">
                    <label for="delete-confirm-password">Salasana</label>
                    <input type="password" id="delete-confirm-password" name="confirm_password" placeholder="Kirjoita salasanasi" required autocomplete="current-password">
                </div>
                <div class="setting-modal-actions">
                    <button type="button" id="btn-cancel-delete" class="setting-btn-secondary">Peruuta</button>
                    <button type="submit" name="delete_account" class="setting-btn-danger-solid">Poista tili</button>
                </div>
            </form>
        </div>
    </div>

    <script src="./js/script.js"></script>
    <script>
        const originalSettingAvatarHtml = document.getElementById('setting-avatar-container') ? document.getElementById('setting-avatar-container').innerHTML : '';

        function previewSettingAvatar(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                if (file.size > 3 * 1024 * 1024) {
                    alert('Kuvan koko saa olla enintään 3 MB.');
                    input.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    const container = document.getElementById('setting-avatar-container');
                    if (container) {
                        container.innerHTML = `<img src="${e.target.result}" alt="Profiilikuva" class="avatar-preview-img">`;
                    }
                    
                    const saveActions = document.getElementById('setting-avatar-save-actions');
                    const changeBtn = document.getElementById('btn-setting-change-avatar');
                    const deleteBtn = document.getElementById('btn-setting-delete-avatar');
                    
                    if (saveActions) saveActions.style.display = 'inline-flex';
                    if (changeBtn) changeBtn.style.display = 'none';
                    if (deleteBtn) deleteBtn.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        }

        function cancelSettingAvatar() {
            const input = document.getElementById('setting-avatar-input');
            if (input) input.value = '';
            
            const container = document.getElementById('setting-avatar-container');
            if (container) container.innerHTML = originalSettingAvatarHtml;
            
            const saveActions = document.getElementById('setting-avatar-save-actions');
            const changeBtn = document.getElementById('btn-setting-change-avatar');
            const deleteBtn = document.getElementById('btn-setting-delete-avatar');
            
            if (saveActions) saveActions.style.display = 'none';
            if (changeBtn) changeBtn.style.display = 'inline-flex';
            if (deleteBtn) deleteBtn.style.display = 'inline-flex';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const deleteModal = document.getElementById('delete-account-modal');
            const openDeleteBtn = document.getElementById('btn-open-delete-modal');
            const cancelDeleteBtn = document.getElementById('btn-cancel-delete');
            const passwordInput = document.getElementById('delete-confirm-password');

            const openModal = () => {
                if (deleteModal) {
                    deleteModal.classList.add('active');
                    deleteModal.setAttribute('aria-hidden', 'false');
                    if (passwordInput) {
                        passwordInput.value = '';
                        setTimeout(() => passwordInput.focus(), 150);
                    }
                }
            };

            const closeModal = () => {
                if (deleteModal) {
                    deleteModal.classList.remove('active');
                    deleteModal.setAttribute('aria-hidden', 'true');
                }
            };

            openDeleteBtn?.addEventListener('click', openModal);
            cancelDeleteBtn?.addEventListener('click', closeModal);

            deleteModal?.addEventListener('click', (e) => {
                if (e.target === deleteModal) closeModal();
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && deleteModal?.classList.contains('active')) {
                    closeModal();
                }
            });
        });
    </script>
</body>

</html>