<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Img2Share - Profil</title>
    <meta charset="utf-8">
    <link href="./css/profile.css" rel="stylesheet" media="all">
    <link href="./css/postMin.css" rel="stylesheet" media="all">
</head>
<body>
    <?php
        require('./utils/post.php');
        require('./utils/db.php');
        require("./components/header.php");

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
            session_destroy();
        }

        if(!isset($_SESSION['handle'])) {
            header("Location:login.php");
            die();
        }
    ?>

    <h1>Profil</h1>
    <div class="profile">
        <?php
            //// REQUETES POST (basiquement que des changements de données)
            // Changement de photo de profil
            if(isset($_FILES['pfp'])) {
                $pfp = file_get_contents($_FILES['pfp']['tmp_name']);
                changePfp($_SESSION['handle'], $pfp);
                header("location:profile.php?handle=".$_SESSION['handle']);
                die();
            }

            // Changement de bio
            if(isset($_POST['bio'])) {
                changeBio($_SESSION['handle'], $_POST['bio']);
                header("location:profile.php?handle=".$_SESSION['handle']);
                die();
            }

            // Changement de pseudo
            if(isset($_POST['username'])) {
                changeUsername($_SESSION['handle'], $_POST['username']);
                header("location:profile.php?handle=".$_SESSION['handle']);
                die();
            }

            // REQUETES GET (basiquement que des affichages de données)
            $handle = $_GET['handle'];
            $notMyProfile = ($_SESSION['handle'] !== $handle); // Si c'est pas mon profil
            
            // Action de follow ou de unfollow
            if(isset($_GET['action']) && $notMyProfile) {
                if($_GET['action'] === 'follow') {
                    follow($_SESSION['handle'], $_GET['handle']);
                } else if($_GET['action'] === 'unfollow') {
                    unfollow($_SESSION['handle'], $_GET['handle']);
                }
            }
            
            // Si on a pas de handle dans la requête get, on redirige vers explore
            if(!isset($_GET['handle'])) {
                header("location:explore.php");
                die();
            }

            // On récupère les données de l'utilisateur
            $userData = getInfo($handle);
            $followCount = getFollowersCount($handle);
            
            // Définition de la photo de profil
            if ($userData['profilePicture'] === null) {
                $profilePicture = './public/default.png';
            } else {
                $profilePicture = 'data:image/jpeg;base64,' . base64_encode($userData['profilePicture']);
            }

            // Affichage de la photo de profil avec possibilité de la changer
            echo ($notMyProfile) ? "<div class='placeholder'><img src='$profilePicture' alt='Photo de profil'></div>" : "<div class='placeholder'><a href='?handle=$handle&action=editpfp'><img src='$profilePicture' alt='Photo de profil'></a></div>";

            // Formulaire de changement de photo de profil qui apparaît seulement quand on a cliqué sur la photo de profil
            if(isset($_GET['action']) && $_GET['action'] === 'editpfp' && !$notMyProfile) {
                ?>
                <form action='profile.php' method='post' enctype='multipart/form-data'>
                    <input type='file' name='pfp' accept='image/png, image/jpeg' required>
                    <input type='submit' value='Changer'>
                </form>
                <?php
            }

            // Affichage du pseudo
            echo "<h2 class='pseudo' style='text-overflow: unset'>".$userData["username"]."</h2>";

            // Si c'est pas mon profil, on affiche pas le bouton pour éditer le pseudo
            echo (!$notMyProfile ? "<a href='?handle=$handle&action=editusername' class='edit-username'>Editer le pseudo</a> " : "");

            // Formulaire de changement de pseudo qui apparaît seulement quand on a cliqué sur "Editer le pseudo"
            if(isset($_GET['action']) && $_GET['action'] === 'editusername') {
                echo "<form action='profile.php' method='post'>";
                echo "<input type='text' name='username' placeholder='Entrez votre nouveau pseudo ici...' required>";
                echo "<input type='submit' value='Mettre à jour le pseudo'>";
                echo "</form>";
            }
            
            // Formulaire d'édition de la biographie qui apparaît seulement quand on a cliqué sur "Editer la bio"
            if (isset($_GET['action']) && $_GET['action'] == "editbio") {
                echo "<form action='profile.php' method='post'>";
                echo "<textarea name='bio' placeholder='Entrez votre nouvelle bio ici...'>".$userData['biography']."</textarea>";
                echo "<input type='submit' value='Mettre à jour la bio'>";
                echo "</form>";
            } else {
                echo (!$notMyProfile ? "<a href='?handle=$handle&action=editbio' class='edit-bio'>Editer la bio</a>" : ""); // Si c'est pas mon profil, on affiche pas le lien
            }
            
            // Affichage du nombre d'abonnés
            echo "<p>$followCount abonné".($followCount > 1 ? "s" : "")."</p>";

            // Si c'est pas mon profil, on affiche le bouton pour s'abonner ou se désabonner
            if($notMyProfile) {
                $isFollowing = isFollowing($_SESSION['handle'], $handle);
                
                if($isFollowing) {
                    echo "<a href='?handle=$handle&action=unfollow' class='follow-button'>Se désabonner</a>";
                } else {
                    echo "<a href='?handle=$handle&action=follow' class='follow-button'>S'abonner</a>";
                }
            }

            // Affichage de la biographie
            echo "<p class='bio' style='text-overflow: unset'>".$userData['biography']."</p><br><hr>";

            // Affichage des posts de l'utilisateur
            $userPosts = getPostsFromUser(25, $handle);
            foreach($userPosts as $post) {
                $post->displayPostMinimized();
            }
        ?>
    </div>
</body>
</html>
