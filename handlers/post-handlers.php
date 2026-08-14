<?php 
// tarksitetaan onko lomake lähetetty post metodilla
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["create_post"])){
          $author = trim($_POST["author"] ?? "");
    $content = trim($_POST["content"] ?? "");
    if ($author !== "" && $content !== "") {
        $success = addPost($conn, $author, $content);
        if($success){
          
          header("Location: index.php");
            exit;
        } 
    }
    // jos lomakkessa on update_post arvo, niin päivitetään julkaisu
    }elseif(isset($_POST["update_post"])){
         $id = $_POST["id"];
        $author = trim($_POST["author"]);
        $content = trim($_POST["content"]);

        if ($author !== "" && $content !== "") {

            updatePost($conn, $id, $author, $content);

            header("Location: index.php#post-" . $id);
            exit;
        }
    }
   
}
?>