<?php
require_once __DIR__ . "/../config/database.php";

// Tietokantayhteys
function dbConnect()
{
    $conn = new mysqli(SERVER, USERNAME, PASSWORD, DATABASE);
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    }
    return $conn;
}
