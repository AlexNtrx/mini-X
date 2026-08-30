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
        $success = true;
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
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $success ? 'Salasana vaihdettu' : 'Aseta uusi salasana' ?> - Mini X</title>
    <link rel="stylesheet" href="../css/forgot-password.css">
</head>
<body class="reset-body">
    <div class="reset-card">
        <div class="brand-logo">Mini X</div>

        <?php if ($success): ?>
            <div class="alert-box alert-success">
                <strong>Salasana päivitetty onnistuneesti!</strong><br>
                Voit nyt kirjautua sisään uudella salasanallasi.
            </div>
            <a href="../index.php" class="action-btn">Siirry kirjautumiseen</a>
        <?php elseif ($error && !isset($_POST["reset_password_post"])): ?>
            <h1 class="reset-title">Virhe palautuslinkissä</h1>
            <div class="alert-box alert-error">
                <strong>Virhe:</strong> <?= htmlspecialchars($error) ?>
            </div>
            <a href="../index.php" class="back-link">← Palaa kirjautumiseen</a>
        <?php else: ?>
            <h1 class="reset-title">Aseta uusi salasana</h1>
            <p class="reset-subtitle">Kirjoita uusi salasana alla oleviin kenttiin.</p>

            <?php if ($error): ?>
                <div class="alert-box alert-error">
                    <strong>Virhe:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="reset-password.php?token=<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="reset_password_post" value="1">

                <div class="form-group">
                    <label for="password">Uusi salasana</label>
                    <input type="password" id="password" name="password" required minlength="8" placeholder="Vähintään 8 merkkiä (sis. kirjaimia ja numeroita)" autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Toista uusi salasana</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" placeholder="Toista uusi salasana" autocomplete="new-password">
                </div>

                <button type="submit" class="submit-btn">Tallenna uusi salasana</button>
                <a href="../index.php" class="back-link">← Peruuta ja palaa kirjautumiseen</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
