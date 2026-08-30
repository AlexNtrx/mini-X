<?php
// Käsittelee salasanan palautuspyynnön ja lähettää sähköpostin
require_once __DIR__ . "/../functions/init.php";
$conn = dbConnect();

$email = trim($_POST['email'] ?? '');
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']);

if (empty($email)) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Ole hyvä ja anna sähköpostiosoite.']);
        exit;
    }
    header('Location: ../pages/forgot-password.php');
    exit;
}

$token = createPasswordResetToken($conn, $email);
$success = false;
$errorMessage = '';

if ($token) {
    $mailResult = sendPasswordResetEmail($email, $token);
    if ($mailResult === true) {
        $success = true;
    } else {
        $errorMessage = $mailResult;
    }
} else {
    $errorMessage = "Sähköpostiosoitetta ei löytynyt tai tili on poistettu käytöstä.";
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
    <link rel="stylesheet" href="../css/forgot-password.css">
</head>
<body class="reset-body">
    <div class="status-card">
        <div class="brand-logo">Mini X</div>
        <?php if ($success): ?>
            <div class="alert-box alert-success">
                <strong>Sähköposti lähetetty onnistuneesti!</strong><br>
                Tarkista postilaatikkosi palautuslinkkiä varten.
            </div>
            <a href="../index.php" class="action-btn">Palaa etusivulle</a>
        <?php else: ?>
            <div class="alert-box alert-error">
                <strong>Virhe:</strong><br>
                <?= htmlspecialchars($errorMessage) ?>
            </div>
            <a href="../pages/forgot-password.php" class="action-btn">Yritä uudelleen</a>
        <?php endif; ?>
    </div>
</body>
</html>
