<?php
require "function.php";

$conn = dbConnect();
// tarksitetaan onko lomake lähetetty post metodilla
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["create_post"])){
        require "create_post.php";
    }elseif(isset($_POST["update_post"])){
        require "edit_post.php";
    }
   
}

$contents = [];
// getposts function
if ($conn) {
    $contents = getShowContents($conn);
}
?>

<!DOCTYPE html>
<html lang="fi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>miniX</title>

    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/kortit.css">
    <link rel="stylesheet" href="./css/sidebar.css">
    <link rel="stylesheet" href="./css/post.css">
    <link rel="stylesheet" href="./css/header.css">
</head>

<body>
   
    <div class="layout">
        <!-- sivupalkkis -->
        <?php include 'components/sidebar.php'; ?>
        <main class="feed">
            <header class="feed-header">
    <a href="#" class="header-tab active">Sinulle</a>
    <a href="#" class="header-tab">Seurataan</a>
</header>
           <!-- Luo julkaisu osio -->
            <?php include 'components/create-post.php'; ?>

            <!-- julkaisut -->
            <section class="posts">

                <?php if (!empty($contents)): ?>

                    <?php foreach ($contents as $content): ?>
                        <!-- kortit -->
                        <?php include 'components/kortit.php'; ?>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p>Ei julkaisuja</p>

                <?php endif; ?>

            </section>

        </main>

    </div>

</body>
<script src="script.js"></script>

</html>