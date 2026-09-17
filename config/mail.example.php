<?php
// Sähköpostipalvelimen konfiguraatio (Mail / SMTP Configuration Template)
// Kopioi tämä tiedosto nimelle mail.php ja päivitä omat tiedot
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'example_user');
define('SMTP_PASS', 'example_password');
define('SMTP_SECURE', 'tls'); // 'tls' tai 'ssl'

define('MAIL_FROM_ADDRESS', 'noreply@example.com');
define('MAIL_FROM_NAME', 'Mini-X');
