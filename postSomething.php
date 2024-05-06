<!DOCTYPE html>
<html lang="fr">
<head>
<title>Img2Share - Publication d'Image</title>
<meta charset="utf-8">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<link href="./css/post.css" rel="stylesheet" media="all">
</head>
<body>

<?php 
    require('./utils/post.php');
    require('./utils/db.php');
    require("./components/header.php");
?>

<h1> Post </h1>
<form id="post" action="#" method="post" enctype="multipart/form-data">
  Titre: <input type="text" id="title" name="title" required><br>
  Description: <input type="text" id="description" name="description" required><i class='bx bx-message-rounded-dots' ></i><br>
  Cochez cette case si votre image contient du contenu sensible (violence, gore): <input type="checkbox" id="nsfw" name="nsfw"><br>
  Tags: <input type="text" id="tags" name="tags"><br>
  Vos images: <input type="file" id="images[]" name="images[]" accept="image/png, image/jpeg" multiple><br>
  <input id="connect" type="submit" name="submit" value="Publier">
  </form>
</body>
</html>

<?php

    if(session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if(!isset($_SESSION['handle'])) {
        header('Location:index.html');
        die();
    }

    // Ne se déclenche que lorsque l'utilisateur poste un truc
    if(count($_POST) > 0) {
        // On prépare le post
        $title = $_POST['title'];
        $description = $_POST['description'];
        $tags = explode(',', $_POST['tags']); // On sépare les tags par des virgules

        // Ce tableau contiendra les images
        $images = array();
        
        // On récupère les images
        if(isset($_FILES['images'])) {
            foreach ($_FILES['images']['tmp_name'] as $key => $image) {
                if (!empty($_FILES['images']['tmp_name'][$key])) {
                    $images[] = file_get_contents($image); // On lit le contenu de l'image sous forme de binaire (important pour l'enregistrer en blob après)
                }
            }
        }
        
        // On récupère le handle de l'utilisateur
        $handle = $_SESSION['handle'];

        // On vérifie si l'utilisateur a coché la case NSFW
        $nsfw = isset($_POST['nsfw']) ? $_POST['nsfw'] : 0;
        if($nsfw == null) {
            $nsfw = 0;
        }

        // On crée un objet Post
        $post = new Post($title, $description, $tags, $images, $nsfw, $handle, -1);


        // On enregistre le post dans la base de données
        try {
            $postID = uploadPost($post);
            echo "Post uploaded successfully!";
        } catch (PDOException $e) {
            echo "Error uploading post: " . $e->getMessage();
        }
        
        header("Location:./view.php?id=".$postID);
    }
?>
