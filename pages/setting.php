<?php
require_once "functions/init.php";
/** @var mysqli $conn */
$userId = (int)($_SESSION['user_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="fi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ilmoitukset - Mini X</title>
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
            <section class="setting-list">
                <div class="setting-items">
                   <span class="">Oma tilisi <br><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
            </div>
                <div class="setting-items">Käyttäjätunnus <br>@<?= htmlspecialchars($_SESSION['username'] ?? '') ?></span></div>
                     <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>
                <form method="POST" class="profile-edit-form">
                <div class="setting-items">Vaihda salasanasi <div class="input-with-button"> <input type="password" id="password" name="password" 
                value="" required minlength="6">
               vahvista <input type="password" id="username" name="confirm_password" 
                value="" required minlength="6">
                <button type="submit" name="update_password" class="save-profile-btn">Tallenna</button>
                    </div>
                </div>
            </form>
                <div class="setting-items">Vaihda sähköpostisi<div class="input-with-button"> <input type="email" id="username" name="username"   require>
                <button type="submit" name="update_email" class="save-profile-btn">Tallenna</button>
                    </div></div>
                <div class="setting-items">Poista tilisi 
                    <button>Poista</button></div>
            </section>
        </main>
    </div>
    <script src="./script.js"></script>
</body>

</html>