   <?php

require "function.php";
    $conn = dbConnect();
$contents = [];
if($conn){
    $contents = getShowContents();
}
?>


<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>minisome</title>
</head>
<body>
    <?php
if (!empty($contents)) {

    foreach ($contents as $content) {

        echo htmlspecialchars($content['author'], ENT_QUOTES, 'UTF-8');
        echo '<br>';

        echo htmlspecialchars($content['content'], ENT_QUOTES, 'UTF-8');
        echo '<br>';

        echo htmlspecialchars($content['created_at'], ENT_QUOTES, 'UTF-8');
        echo '<br><br>';
    }

} else {
    echo 'Ei julkaisuja.';
}
    ?>
</body>
</html>

