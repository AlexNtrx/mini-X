<?php
require_once __DIR__ . "/../functions/init.php";
$conn = dbConnect();

$token = trim($_GET["token"] ?? ($_POST["token"] ?? ''));
$error = null;
$success = false;

// Tarkistetaan palautuspyyntö ja tallennetaan uusi salasana
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reset_password_post"])) {
    $password = $_POST["password"] ?? '';
    $passwordConfirmation = $_POST["password_confirmation"] ?? '';

    $result = resetPasswordWithToken($conn, $token, $password, $passwordConfirmation);
    if ($result === true) {
        $success = "Salasana päivitetty onnistuneesti! Voit nyt kirjautua sisään uudella salasanallasi.";
    } else {
        $error = $result;
    }
} else {
    // Tarkistetaan palautustunnisteen voimassaolo sivulle saavuttaessa
    $tokenCheck = getUserByResetToken($conn, $token);
    if ($tokenCheck['error']) {
        $error = $tokenCheck['error'];
    }
}

$isDirectAccess = (basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'index.php');
$assetPrefix = $isDirectAccess ? '../' : './';
$indexPath = $isDirectAccess ? '../index.php' : 'index.php';
$formAction = $isDirectAccess ? ('reset-password.php?token=' . urlencode($token)) : ('index.php?page=reset-password&token=' . urlencode($token));
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= !empty($success) ? 'Salasana vaihdettu' : 'Aseta uusi salasana' ?> - Mini X</title>
    <link rel="stylesheet" href="<?= $assetPrefix ?>css/forgot-password.css?v=1.1.2">
    <link rel="stylesheet" href="<?= $assetPrefix ?>css/toast.css?v=1.1.2">
</head>
<body class="reset-body">
    <div class="reset-card">
        <div class="brand-logo">Mini X</div>

        <?php if ($success): ?>
            <h1 class="reset-title" style="color: #00ba7c;">Salasana päivitetty!</h1>
            <p class="reset-subtitle">Voit nyt kirjautua sisään uudella salasanallasi.</p>
            <a href="<?= $indexPath ?>" class="action-btn">Siirry kirjautumiseen</a>
        <?php elseif ($error && !isset($_POST["reset_password_post"])): ?>
            <h1 class="reset-title">Virhe palautuslinkissä</h1>
            <p class="reset-subtitle" style="color: #f4212e;"><?= htmlspecialchars($error) ?></p>
            <a href="<?= $indexPath ?>" class="back-link">← Palaa kirjautumiseen</a>
        <?php else: ?>
            <h1 class="reset-title">Aseta uusi salasana</h1>
            <p class="reset-subtitle">Kirjoita uusi salasana alla oleviin kenttiin.</p>

            <form method="post" action="<?= $formAction ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="reset_password_post" value="1">

                <div class="form-group">
                    <label for="password">Uusi salasana</label>
                    <input type="password" id="password" name="password" required minlength="6" placeholder="Vähintään 6 merkkiä (sis. kirjaimia ja numeroita)" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Toista uusi salasana</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6" placeholder="Toista uusi salasana" autocomplete="new-password">
                </div>

                <button type="submit" class="submit-btn">Tallenna uusi salasana</button>
                <a href="<?= $indexPath ?>" class="back-link">← Peruuta ja palaa kirjautumiseen</a>
            </form>
        <?php endif; ?>
    </div>

    <?php include __DIR__ . '/../components/toast.php'; ?>
    <script src="<?= $assetPrefix ?>js/toast.js?v=1.1.2"></script>
</body>
</html>
