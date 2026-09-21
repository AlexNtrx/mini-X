<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isDirectAccess = (basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'index.php');
$assetPrefix = $isDirectAccess ? '../' : './';
$indexPath = $isDirectAccess ? '../index.php' : 'index.php';
$handlerPath = $isDirectAccess ? '../handlers/send-password-reset.php' : 'handlers/send-password-reset.php';

$status = $_GET['status'] ?? '';
$statusError = $_SESSION['reset_error'] ?? 'Sähköpostiosoitetta ei löytynyt tai tili on poistettu käytöstä.';
if ($status === 'error') {
    unset($_SESSION['reset_error']);
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palauta salasana - Mini X</title>
    <link rel="stylesheet" href="<?= $assetPrefix ?>css/forgot-password.css?v=1.1.2">
</head>
<body class="forgot-body">
    <div class="forgot-wrapper">
        <div class="forgot-brand">Mini X</div>
        <h1 class="forgot-title">Palauta salasana</h1>

        <?php if ($status === 'sent'): ?>
            <div class="alert-box alert-success">
                <strong>Sähköposti lähetetty onnistuneesti!</strong><br>
                Tarkista postilaatikkosi palautuslinkkiä varten.
            </div>
            <a href="<?= $indexPath ?>" class="action-btn">Palaa kirjautumiseen</a>
        <?php else: ?>
            <p class="forgot-desc">Syötä käyttäjätilisi sähköpostiosoite, niin lähetämme sinulle linkin salasanan vaihtamista varten.</p>

            <?php if ($status === 'error'): ?>
                <div class="alert-box alert-error">
                    <strong>Virhe:</strong><br>
                    <?= htmlspecialchars($statusError) ?>
                </div>
            <?php endif; ?>

            <form class="forgot-form" method="post" action="<?= $handlerPath ?>">
                <div class="form-group">
                    <label for="email">Sähköposti</label>
                    <input type="email" name="email" id="email" placeholder="esim. kayttaja@example.com" required autocomplete="email">
                </div>
                <button type="submit" class="forgot-submit-btn">Lähetä palautuslinkki</button>
                <a href="<?= $indexPath ?>" class="back-link">← Palaa kirjautumiseen</a>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>