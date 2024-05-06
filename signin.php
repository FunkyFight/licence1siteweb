<!DOCTYPE html>
<html lang="fr">
<head>
<title>Img2Share - Inscription</title>
<meta charset="utf-8">
<link href="./css/signin.css" rel="stylesheet" media="all">
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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

<h1> Créer un compte </h1>
  <form id="inscription" action="#" method="post">
  Handle (doit être unique) : <input type="text" id="nom" name="handle" minlength="2" maxlength="20" required><i class='bx bxs-user'></i> <br>
  Nom d'utilisateur : <input type="text" id="nom" name="username" minlength="2" maxlength="20" required><i class='bx bxs-user'></i> <br>
  Centres d'intérêts : <select id="interests" name="interests">
<option value="" selected>(aucun)</option>
<option value="jeux videos">Jeux vidéos</option>
<option value="anime">Anime</option>
<option value="animaux">Animaux</option>
<option value="photo">Photographie</option>
</select>
<br>

  Mot de passe (8 à 30 cars, 1 maj, 1 car spécial): <input type="password" id="mdp" name="password" minlength="8" maxlength="30" pattern="^(?=(.*[A-Z]){1,})(?=(.*[0-9]){1,})(?=(.*[!?#@]){1,})[A-Za-z0-9!?#@]{8,}$" required><i class='bx bxs-lock-alt'></i> <br>
  Confirmez le mot de passe : <input type="password" id="mdpconf" name="passwordconf" minlength="8" maxlength="30" pattern="^(?=(.*[A-Z]){1,})(?=(.*[0-9]){1,})(?=(.*[!?#@]){1,})[A-Za-z0-9!?#@]{8,}$" required> <br>
    <input id="envoyer" type="submit" name="sign" value="S'inscrire">
    </form>
</body>
</html>

<?php
    // Le code ne se déclenche que lorsqu'on envoie le formulaire d'inscription
    if(count($_POST) > 0) {
        // Tentative d'enregistrement
        
        // On vérifie que tous les champs sont remplis
        if(!isset($_POST['handle']) || !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['passwordconf']) || !isset($_POST['interests'])) {
            echo "<div class='error-box'><h1 class='title'>Oups !</h1><p class='description'>Tu as oublié de remplir quelque chose.</p></div>";
            die();
        }
        
        // On vérifie que le handle n'existe pas déjà CAR il doit être unique !!!
        $handle = $_POST['handle'];
        $username = $_POST['username']; // Le nom d'utilisateur n'a pas besoin d'être unique

        if(!handleAlreadyExists($handle)) {
            // Maintenant on vérifie que les mots de passe sont les mêmes
            $password = $_POST['password'];
            if($password != $_POST['passwordconf']) {
                echo "<div class='error-box'><h1 class='title'>Oups !</h1><p class='description'>Les mots de passe ne correspondent pas.</p></div>";
                die();
            }

            // On hash le mot de passe pour plus de sécurité
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $interests = $_POST['interests']; // On récupère les centres d'intérêts

            // On enregistre l'utilisateur
            registerNewUser($handle, $username, $hashed_password, $interests);

            // On démarre la session
            session_start();
            $_SESSION['handle'] = $handle; // Le handle sert à savoir qui est constamment l'utilisateur et à savoir s'il est connecté
            header("Location:explore.php"); // On redirige l'utilisateur vers la page d'exploration
        } else {
            echo "<div class='error-box'><h1 class='title'>Oups !</h1><p class='description'>Cet handle n'est pas unique.</p></div>";
        }
    }
?>