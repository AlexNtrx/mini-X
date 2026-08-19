<?php
session_start();
session_unset();  // Poistaa kaikki istuntomuuttujat
session_destroy(); // Tuhoaa istunnon

header("Location: index.php");
exit();