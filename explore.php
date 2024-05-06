<!DOCTYPE html>
<html lang="fr">
<head>
<title>Img2Share - Explorer</title>
<meta charset="utf-8">
<link href="./css/explore.css" rel="stylesheet" media="all">
<link href="./css/postMin.css" rel="stylesheet" media="all">
</head>
<body>
  <header>

  <?php 
    require('./utils/post.php');
    require('utils/db.php');
    require("components/header.php");

    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
      session_destroy();
    }

    if(!isset($_SESSION['handle'])) {
        header("Location:index.php");
        die();
    }

    
  ?>
  </header>

  <h1> Explorer </h1>
  <?php

    // Like et dislike
    if(count($_GET) > 0) {
      if($_GET['action'] === 'like') {
        likePost($_SESSION['handle'], $_GET['postID']);
      } else if($_GET['action'] === 'dislike') {
        dislikePost($_SESSION['handle'], $_GET['postID']);
      }
    }

    $newly = getNewlyPosts();

    foreach($newly as $post) {
      $post->displayPostMinimized();
      echo '<hr>';
    }

    
  ?>
</body>
</html>
