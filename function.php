<?php 
    require "database.php";


 function dbConnect(){
 // Create connection
  $conn = new mysqli(SERVER, USERNAME, PASSWORD, DATABASE);
// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}else{
    return $conn;
}
}

function getShowContents(){ 
 $conn = dbConnect();
    $sql = "SELECT * FROM posts ORDER BY id DESC";
       $result = $conn->query($sql);
    $contents= [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $contents[] = $row;
        }
    }

    return $contents;
}

?>
