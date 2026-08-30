<?php
require_once __DIR__ . "/mailer.php";

// Luo salasanan palautustunnisteen (Token) ja tallentaa sen tietokantaan
function createPasswordResetToken($conn, $email)
{
    $token = bin2hex(random_bytes(16));
    $tokenHash = hash("sha256", $token);
    $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // Voimassa 30 minuuttia

    $stmt = $conn->prepare("UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ? AND deleted_at IS NULL");
    $stmt->bind_param("sss", $tokenHash, $expiry, $email);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    return $affected > 0 ? $token : false;
}

// Hakee käyttäjän palautustunnisteen (Token) perusteella ja tarkistaa voimassaolon
function getUserByResetToken($conn, $token)
{
    if (empty($token)) {
        return ['user' => null, 'error' => 'Palautuslinkki ei kelpaa.'];
    }

    $tokenHash = hash("sha256", $token);
    $stmt = $conn->prepare("SELECT id, username, email, reset_token_expires_at FROM users WHERE reset_token_hash = ? LIMIT 1");
    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return ['user' => null, 'error' => 'Palautuslinkki ei kelpaa tai se on jo käytetty.'];
    }

    if (strtotime($user["reset_token_expires_at"]) <= time()) {
        return ['user' => null, 'error' => 'Palautuslinkki on vanhentunut. Pyydä uusi salasanan palautuslinkki.'];
    }

    return ['user' => $user, 'error' => null];
}

// Asettaa käyttäjälle uuden salasanan palautustunnisteen avulla
function resetPasswordWithToken($conn, $token, $newPassword, $confirmPassword)
{
    if (strlen($newPassword) < 8) {
        return "Salasanan on oltava vähintään 8 merkkiä pitkä.";
    }

    if (!preg_match("/[a-z]/i", $newPassword)) {
        return "Salasanan on sisällettävä vähintään yksi kirjain.";
    }

    if (!preg_match("/[0-9]/", $newPassword)) {
        return "Salasanan on sisällettävä vähintään yksi numero.";
    }

    if ($newPassword !== $confirmPassword) {
        return "Salasanat eivät täsmää.";
    }

    $tokenCheck = getUserByResetToken($conn, $token);
    if ($tokenCheck['error']) {
        return $tokenCheck['error'];
    }

    $user = $tokenCheck['user'];
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $user["id"]);
    $success = $stmt->execute();
    $stmt->close();

    return $success ? true : "Salasanan päivittäminen epäonnistui.";
}

// Lähettää salasanan palautuslinkin käyttäjän sähköpostiin
function sendPasswordResetEmail($email, $token)
{
    try {
        $mail = getMailer();
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($email);

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        // Varmistetaan oikea polku pages/reset-password.php
        $basePath = preg_replace('/(\/pages|\/handlers)$/', '', $scriptDir);
        $resetLink = $protocol . $host . $basePath . "/pages/reset-password.php?token=" . urlencode($token);

        $mail->Subject = "Salasanan palautus / Reset Password - Mini X";
        $mail->Body = "
            <h2>Salasanan palautuspyyntö</h2>
            <p>Olet pyytänyt salasanan vaihtoa Mini X -palvelussa. Klikkaa alla olevaa painiketta asettaaksesi uuden salasanan:</p>
            <p style='margin: 20px 0;'>
                <a href='{$resetLink}' style='background-color: #1d9bf0; color: white; padding: 12px 24px; text-decoration: none; border-radius: 9999px; display: inline-block; font-weight: bold; font-family: sans-serif;'>Nollaa salasana tästä</a>
            </p>
            <p>Tai kopioi tämä linkki selaimeesi: <br><a href='{$resetLink}'>{$resetLink}</a></p>
            <p><small style='color: #888;'>Linkki on voimassa 30 minuuttia. Jos et ole pyytänyt salasanan nollausta, voit jättää tämän viestin huomiotta.</small></p>
        ";
        $mail->AltBody = "Voit nollata salasanasi siirtymällä osoitteeseen: " . $resetLink;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Sähköpostin lähetys epäonnistui: " . ($mail->ErrorInfo ?? $e->getMessage());
    }
}
