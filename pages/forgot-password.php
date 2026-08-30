<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Palauta salasana - Mini X</title>
    <link rel="stylesheet" href="../css/forgot-password.css">
</head>
<body class="forgot-body">
    <div class="forgot-wrapper">
        <div class="forgot-brand">Mini X</div>
        <h1 class="forgot-title">Palauta salasana</h1>
        <p class="forgot-desc">Syötä käyttäjätilisi sähköpostiosoite, niin lähetämme sinulle linkin salasanan vaihtamista varten.</p>
        
        <form class="forgot-form" method="post" action="../handlers/send-password-reset.php">
            <div class="form-group">
                <label for="email">Sähköposti</label>
                <input type="email" name="email" id="email" placeholder="esim. kayttaja@example.com" required autocomplete="email">
            </div>
            <button type="submit" class="forgot-submit-btn">Lähetä palautuslinkki</button>
            <a href="../index.php" class="back-link">← Palaa kirjautumiseen</a>
        </form>
    </div>
</body>
</html>