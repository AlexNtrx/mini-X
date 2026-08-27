<?php
$email = $_POST['email'];
$token = bin2hex (random_bytes(16));

$token_hash = hash("sha256", $token);

$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

require_once __DIR__ . "/functions/db.php";
$mysqli = dbConnect();

$sql = "UPDATE users
        SET reset_token_hash = ?,
            reset_token_expires_at = ?
        WHERE email = ?";

$stmt = $mysqli->prepare($sql);

$stmt->bind_param("sss",$token_hash, $expiry,$email);

$stmt->execute();

if ($mysqli->affected_rows) {
    //  Ladataan mailer.php (Korjattu: polun kauttaviiva ja tiedostonimi)
    $mail = require __DIR__ . "/mailer.php";

    try {
        //  Lähettäjä ja vastaanottaja (Resend-testitilassa onboarding@resend.dev)
        $mail->setFrom("onboarding@resend.dev", "Mini-X");
        $mail->addAddress($email);

        //  Luodaan palautuslinkki (TÄRKEÄÄ: käytä $token-muuttujaa, älä $token_hashia)
        $resetLink = "http://localhost/mini-X/reset-password.php?token=" . $token;

        $mail->Subject = "Salasanan palautus / Reset Password";
        $mail->Body = "
            <h2>Salasanan palautuspyyntö</h2>
            <p>Olet pyytänyt salasanan vaihtoa. Klikkaa alla olevaa linkkiä asettaaksesi uuden salasanan:</p>
            <p><a href='{$resetLink}' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; display: inline-block;'>Nollaa salasana tästä</a></p>
            <p>Tai kopioi tämä linkki selaimeesi: <br><a href='{$resetLink}'>{$resetLink}</a></p>
            <p><small>Linkki on voimassa 30 minuuttia.</small></p>
        ";
        $mail->AltBody = "Voit nollata salasanasi siirtymällä osoitteeseen: " . $resetLink;

        //  Lähetetään sähköposti
        $mail->send();

       echo "
    <div style='font-family: sans-serif; text-align: center; margin-top: 50px;'>
        <h2 > Sähköposti lähetetty onnistuneesti!</h2>
        <p>Tarkista postilaatikkosi.</p>
        <br>
        <a href='index.php' style='display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>
            Palaa etusivulle
        </a>
    </div>
";

    } catch (Exception $e) {
        echo "Sähköpostin lähetys epäonnistui. Virhe: {$mail->ErrorInfo}";
    }
} else {
    // Jos sähköpostia ei löydy kannasta
    echo "Sähköpostiosoitetta ei löytynyt tai pyyntö on jo käsitelty.";
}
 ?>