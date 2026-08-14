<?php 
    $author = trim($_POST["author"] ?? "");
    $content = trim($_POST["content"] ?? "");
    if ($author !== "" && $content !== "") {
        $success = addPost($conn, $author, $content);
        if($success){
          
          header("Location: index.php");
            exit;
        } 
    }?>