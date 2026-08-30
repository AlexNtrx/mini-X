<?php
// Tietokannan konfiguraatio (Database Configuration)
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'minisome');

// Yhteensopivuus aiempien määrittelyjen kanssa
if (!defined('SERVER')) define('SERVER', DB_SERVER);
if (!defined('USERNAME')) define('USERNAME', DB_USERNAME);
if (!defined('PASSWORD')) define('PASSWORD', DB_PASSWORD);
if (!defined('DATABASE')) define('DATABASE', DB_DATABASE);
