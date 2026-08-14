<?php 
    if (isset($_POST["update_post"])) {

        $id = $_POST["id"];
        $author = trim($_POST["author"]);
        $content = trim($_POST["content"]);

        if ($author !== "" && $content !== "") {

            updatePost($conn, $id, $author, $content);

            header("Location: index.php#post-" . $id);
            exit;
        }
    }?>