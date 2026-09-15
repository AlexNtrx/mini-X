<?php
require_once __DIR__ . "/../config/database.php";

// Tietokantayhteys
function dbConnect()
{
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}
