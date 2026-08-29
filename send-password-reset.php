<?php
$email = trim($_POST['email'] ?? '');
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

if (empty($email)) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ole hyvä ja anna sähköpostiosoite.']);
        exit;
    }
    header('Location: pages/forgot-password.php');
    exit;
}

$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);
$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

require_once __DIR__ . "/functions/db.php";
$mysqli = dbConnect();

$sql = "UPDATE users
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("sss", $token_hash, $expiry, $email);
$stmt->execute();

$success = false;
$errorMessage = '';

if ($mysqli->affected_rows) {
    $mail = require __DIR__ . "/mailer.php";

    try {
        $mail->setFrom("onboarding@resend.dev", "Mini-X");
        $mail->addAddress($email);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        $resetLink = $protocol . $host . ($scriptDir ? $scriptDir : '') . "/reset-password.php?token=" . $token;

        $mail->Subject = "Salasanan palautus / Reset Password";
        $mail->Body = "
            <h2>Salasanan palautuspyyntö</h2>
            <p>Olet pyytänyt salasanan vaihtoa. Klikkaa alla olevaa linkkiä asettaaksesi uuden salasanan:</p>
            <p><a href='{$resetLink}' style='background-color: #1d9bf0; color: white; padding: 10px 18px; text-decoration: none; border-radius: 9999px; display: inline-block; font-weight: bold;'>Nollaa salasana tästä</a></p>
            <p>Tai kopioi tämä linkki selaimeesi: <br><a href='{$resetLink}'>{$resetLink}</a></p>
            <p><small>Linkki on voimassa 30 minuuttia.</small></p>
        ";
        $mail->AltBody = "Voit nollata salasanasi siirtymällä osoitteeseen: " . $resetLink;

        $mail->send();
        $success = true;
    } catch (Exception $e) {
        $errorMessage = "Sähköpostin lähetys epäonnistui: " . $mail->ErrorInfo;
    }
} else {
    $errorMessage = "Sähköpostiosoitetta ei löytynyt tai pyyntö on jo käsitelty.";
}

if ($isAjax) {
    header('Content-Type: application/json');
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Palautuslinkki on lähetetty sähköpostiisi! Tarkista postilaatikkosi.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $errorMessage
        ]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $success ? 'Sähköposti lähetetty' : 'Virhe' ?> - Mini X</title>
    <link rel="stylesheet" href="css/forgot-password.css">
</head>
<body class="reset-body">
    <div class="status-card">
        <div class="brand-logo">Mini X</div>
        <?php if ($success): ?>
            <div class="alert-box alert-success">
                <strong>Sähköposti lähetetty onnistuneesti!</strong><br>
                Tarkista postilaatikkosi palautuslinkkiä varten.
            </div>
            <a href="index.php" class="action-btn">Palaa etusivulle</a>
        <?php else: ?>
            <div class="alert-box alert-error">
                <strong>Virhe:</strong><br>
                <?= htmlspecialchars($errorMessage) ?>
            </div>
            <a href="pages/forgot-password.php" class="action-btn">Yritä uudelleen</a>
        <?php endif; ?>
    </div>
</body>
</html>