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
    $error = $statusError;
    unset($_SESSION['reset_error']);
} elseif ($status === 'sent') {
    $success = 'Sähköposti lähetetty onnistuneesti! Tarkista postilaatikkosi palautuslinkkiä varten.';
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palauta salasana - Mini X</title>
    <link rel="stylesheet" href="<?= $assetPrefix ?>css/forgot-password.css?v=1.1.2">
    <link rel="stylesheet" href="<?= $assetPrefix ?>css/toast.css?v=1.1.2">
</head>
<body class="forgot-body">
    <div class="forgot-wrapper">
        <div class="forgot-brand">Mini X</div>
        <h1 class="forgot-title">Palauta salasana</h1>

        <?php if ($status === 'sent'): ?>
            <p class="forgot-desc" style="color: #00ba7c; font-weight: 500;">
                Palautuslinkki on lähetetty sähköpostiisi. Tarkista postilaatikkosi.
            </p>
            <a href="<?= $indexPath ?>" class="action-btn">Palaa kirjautumiseen</a>
        <?php else: ?>
            <p class="forgot-desc">Syötä käyttäjätilisi sähköpostiosoite, niin lähetämme sinulle linkin salasanan vaihtamista varten.</p>

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

    <?php include __DIR__ . '/../components/toast.php'; ?>
    <script src="<?= $assetPrefix ?>js/toast.js?v=1.1.2"></script>
</body>
</html>