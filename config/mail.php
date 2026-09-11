<?php
// Sähköpostipalvelimen konfiguraatio (Mail / SMTP Configuration)
define('SMTP_HOST', 'smtp.example.com'); // Vaihda tämä oikeaan SMTP-palvelimen osoitteeseen
define('SMTP_PORT', 587);
define('SMTP_USER', 'example_user'); // Vaihda tämä oikeaan SMTP-käyttäjätunnukseen
define('SMTP_PASS', 'example_password'); // Vaihda tämä oikeaan SMTP-salasanahan
define('SMTP_SECURE', 'tls'); // 'tls' tai 'ssl'

define('MAIL_FROM_ADDRESS', 'onboarding@resend.dev');
define('MAIL_FROM_NAME', 'Mini-X');
