<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/view.css">
    <link href="./css/postMin.css" rel="stylesheet" media="all">
    <title>Post</title>
</head>
<body>
<?php
    // Vérification de la session 
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if(!isset($_SESSION['handle'])) {
        header("Location:index.php");
        die();
    }

    require('./utils/post.php');
    require('./utils/db.php');
    require('./components/header.php');

    // La page ne démarre JAMAIS si l'id n'est pas défini
    if(isset($_GET['id'])) {

        // Actions que l'on peut faire sur cette page
        // - Like
        // - Dislike
        if(isset($_GET['action'])) {
            if($_GET['action'] === 'like') {
              likePost($_SESSION['handle'], $_GET['id']);
            } else if($_GET['action'] === 'dislike') {
              dislikePost($_SESSION['handle'], $_GET['id']);
            }
        }

        // On récupère le post
        $postID = $_GET['id'];
        $post = getPostByID($postID);
        
        // On affiche le post s'il existe
        if($post) {
            $info = getInfo($post->getHandle()); // Récupération des informations de l'auteur

            // Si l'auteur n'a pas de photo de profil, on met une photo par défaut
            if($info['profilePicture'] === null) {
                $pfp = "public/default.png";
            } else {
                $pfp = "data:image/jpeg;base64," . base64_encode($info['profilePicture']);
            }

            // Affichage du post
            echo '<div class="post">';
            echo '<h2 class="post-title">' . $post->getTitle() . '</h2>'; // Titre

            // Entête du post
            echo '<div class="post-header">';
            echo '<img src="' . $pfp . '" alt="User Profile Picture" class="user-profile-picture">'; // Photo de profil
            echo '</div>';
            echo '<h3 class="username">' . $info['username'] . '</h3>'; // Pseudo

            // Contenu du post
            echo '<p class="post-content">' . $post->getDescription() . '</p>'; // Description

            // Affichage des images
            $images = $post->getImages();
            foreach($images as $image) {
                echo '<img src="data:image/jpeg;base64,' . base64_encode($image) . '" alt="Post Image" class="post-image">'; // Encodage de l'image (binaire -> base64)
            }
            
            // Pied de post
            echo '<div class="post-footer">';
            // 
            echo '<p class="likes">' . $post->likes . ' <a href="?action=like&id='.$post->getPostID().'"><img src="./public/like.svg" style="width:50px;vertical-align:middle;"></a> · ' . $post->dislikes . ' <a href="?action=dislike&id='.$post->getPostID().'"><img src="./public/dislike.svg" style="width:40px;vertical-align:middle;"></a></p>';
            echo '<i class="burger-menu-icon"></i>';
            echo '</div>';
            echo '</div>';
        } else {
            echo "Post not found";
        }
    } else {
        echo "Invalid post ID";
    }
?>

</body>
</html>
