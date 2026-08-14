<?php
require "database.php";

// dbConnect function
function dbConnect()
{
    // Create connection
    $conn = new mysqli(SERVER, USERNAME, PASSWORD, DATABASE);
    // Check connection
    if ($conn->connect_error) {
        die('Connection failed: ' . $conn->connect_error);
    } else {
        return $conn;
    }
}

// funtio, joka haetaan tiedot tietokannasta
function getShowContents($conn)
{

    $sql = "SELECT * FROM posts ORDER BY id DESC";
    $result = $conn->query($sql);
    $contents = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $contents[] = $row;
        }
    }
    // palataan haettut tiedot
    return $contents;
}

// funktio, joka lisää julkaisu tietokantaan
function addPost($conn, $author, $content)
{

    $sql = "INSERT INTO posts (author, content)VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("ss", $author, $content);

    return $stmt->execute();
}
// funktio, joka päivittää julkaisu tietokannassa
function updatePost($conn, $id, $author, $content)
{

    $sql = "UPDATE posts SET author = ?, content = ? WHERE id = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param( "ssi", $author, $content, $id
    );

    return $stmt->execute();
}
function deletePost($conn,$id){
    $sql = "DELETE FROM posts Where id = ?";
    $stmt =$conn->prepare($sql);
    $stmt->bind_param("i",$id);
    return $stmt->execute();
}