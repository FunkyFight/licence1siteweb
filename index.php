<!DOCTYPE html>
<html lang="fr">
<head>
<title>Img2Share - Accueil</title>
<meta charset="utf-8">
<link href="./css/index.css" rel="stylesheet" media="all">
</head>
<body>

<?php
    require('./utils/db.php');
    require("./components/header.php");

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
        session_destroy();
    }

    if(isset($_SESSION['handle'])) {
        header("Location:explore.php");
        die();
    }
?>

<h1> Img2Share </h1>
  <div class="hero-image">
    <div class="little-cards card">

    </div>
    <div class="big-cards card">

    </div>
    <div class="little-cards card">

    </div>
    <div class="big-cards card">

    </div>
    <div class="little-cards card">

    </div>
    <div class="big-cards card">

    </div>
    <div class="little-cards card">

    </div>
    <div class="big-cards card">

    </div>
</div>
</body>
</html>
