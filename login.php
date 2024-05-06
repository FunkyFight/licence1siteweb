<!DOCTYPE html>
<html lang="fr">
<head>
<title>Img2Share - Connexion</title>
<meta charset="utf-8">
<link href="./css/login.css" rel="stylesheet" media="all">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

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


<body>
</header>
<h1> Login </h1>
  <form id="inscription" action="#" method="post">
  Handle : <input type="text" id="nom" name="handle" minlength="2" maxlength="20" required><i class='bx bxs-user'></i> <br>
  Mot de passe : <input type="password" id="mdp" name="password" minlength="8" maxlength="30" pattern="^(?=(.*[A-Z]){1,})(?=(.*[0-9]){1,})(?=(.*[!?#@]){1,})[A-Za-z0-9!?#@]{8,}$" required><i class='bx bxs-lock-alt'></i> <br>
    <input id="connect" type="submit" name="log" value="Se connecter"> <a id="create" href="./signin.php"> Pas de compte ? Créez en un ici. </a>
    </form>
</body>


<?php

    // Le code ne se déclenche que lorsqu'on envoie le formulaire de connexion
    if(count($_POST) > 0) {
        // Tentative de connexion
        if(!isset($_POST['handle']) || !isset($_POST['password'])) {
            echo "<div class='error-box'><h1 class='title'>Erreur !</h1><p class='description'>Tous les inputs doivent être remplis !</p></div>";
            die();
        }
        
        // On vérifie que le handle existe et que le mot de passe est correct
        $handle = $_POST['handle'];
        $password = $_POST['password'];

        if(handleAlreadyExists($handle)) {
            if(verifyPassword($handle, $password)) {
                // Tout est correct, on peut connecter l'utilisateur et lancer la session
                session_start();
                $_SESSION['handle'] = $handle;
                header("Location:explore.php");
            } else {
                echo "<div class='error-box'><h1 class='title'>Erreur lors de la connexion !</h1><p class='description'>Mot de passe incorrect</p></div>";
            }
        } else {
            echo "<div class='error-box'><h1 class='title'>Erreur lors de la connexion !</h1><p class='description'>Le pseudo n'existe pas</p></div>";
        }
    }
?>

</html>
