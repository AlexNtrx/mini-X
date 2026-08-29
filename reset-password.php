<?php
$token = $_GET["token"] ?? '';
$token_hash = hash("sha256", $token);

require_once __DIR__ . "/functions/db.php";
$mysqli = dbConnect();

$sql = "SELECT * FROM users
        WHERE reset_token_hash = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$error = null;
if ($user === null) {
    $error = "Palautuslinkki ei kelpaa tai se on jo käytetty.";
} elseif (strtotime($user["reset_token_expires_at"]) <= time()) {
    $error = "Palautuslinkki on vanhentunut. Pyydä uusi salasanan palautuslinkki.";
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aseta uusi salasana - Mini X</title>
    <link rel="stylesheet" href="css/forgot-password.css">
</head>
<body class="reset-body">
    <div class="reset-card">
        <div class="brand-logo">Mini X</div>
        <h1 class="reset-title">Aseta uusi salasana</h1>

        <?php if ($error): ?>
            <div class="alert-error">
                <strong>Virhe:</strong> <?= htmlspecialchars($error) ?>
            </div>
            <a href="index.php" class="back-link">← Palaa kirjautumiseen</a>
        <?php else: ?>
            <p class="reset-subtitle">Kirjoita uusi salasana alla oleviin kenttiin.</p>
            <form method="post" action="process-reset-password.php">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="form-group">
                    <label for="password">Uusi salasana</label>
                    <input type="password" id="password" name="password" required minlength="8" placeholder="Vähintään 8 merkkiä">
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Toista uusi salasana</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" placeholder="Toista salasana">
                </div>

                <button type="submit" class="submit-btn">Tallenna uusi salasana</button>
                <a href="index.php" class="back-link">← Peruuta ja palaa kirjautumiseen</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>