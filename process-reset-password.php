<?php
$token = $_POST["token"] ?? '';
$password = $_POST["password"] ?? '';
$password_confirmation = $_POST["password_confirmation"] ?? '';

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
    $error = "Palautuslinkki on vanhentunut.";
} elseif (strlen($password) < 8) {
    $error = "Salasanan on oltava vähintään 8 merkkiä pitkä.";
} elseif (!preg_match("/[a-z]/i", $password)) {
    $error = "Salasanan on sisällettävä vähintään yksi kirjain.";
} elseif (!preg_match("/[0-9]/", $password)) {
    $error = "Salasanan on sisällettävä vähintään yksi numero.";
} elseif ($password !== $password_confirmation) {
    $error = "Salasanat eivät täsmää.";
}

if ($error === null) {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "UPDATE users
            SET password = ?,
                reset_token_hash = NULL,
                reset_token_expires_at = NULL
            WHERE id = ?";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $password_hash, $user["id"]);
    $stmt->execute();
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $error ? 'Virhe' : 'Salasana vaihdettu' ?> - Mini X</title>
    <link rel="stylesheet" href="css/forgot-password.css">
</head>
<body class="reset-body">
    <div class="status-card">
        <div class="brand-logo">Mini X</div>
        <?php if ($error): ?>
            <div class="alert-box alert-error">
                <strong>Virhe:</strong><br>
                <?= htmlspecialchars($error) ?>
            </div>
            <a href="index.php" class="action-btn">Palaa kirjautumiseen</a>
        <?php else: ?>
            <div class="alert-box alert-success">
                <strong>Salasana päivitetty onnistuneesti!</strong><br>
                Voit nyt kirjautua sisään uudella salasanallasi.
            </div>
            <a href="index.php" class="action-btn">Kirjaudu sisään</a>
        <?php endif; ?>
    </div>
</body>
</html>