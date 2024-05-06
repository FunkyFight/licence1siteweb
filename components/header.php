<?php

    // Commencer la session si elle n'est pas déjà démarrée
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $isConnected = false;

    // Vérification de la connexion
    if(isset($_SESSION['handle'])) {
        $isConnected = true;
        $handle = $_SESSION['handle'];
        $userData = getInfo($handle);
        if ($userData['profilePicture'] === null) {
            $profilePicture = 'public/default.jpg';
        } else {
            $profilePicture = 'data:image/jpeg;base64,' . base64_encode($userData['profilePicture']);
        }
    }

   
?>



<header>
    <div class="navbar">
        <h2> Img2Share </h2>
        <ul>
        <?php
            if ($isConnected) {
                // Tout ça apparaît ssi l'utilisateur est connecté
                echo '<li><a href="./profile.php?handle='.$_SESSION['handle'].'"><button type="button" > <span></span>Mon profil</button> </a></li>';
                echo '<li><a href="./explore.php"><button type="button" ><span></span>Explorer</button></a></li>';
                echo '<li><a href="./postSomething.php"><button type="button" ><span></span>Poster</button></a></li>';
                echo '<li><a href="./disconnect.php"><button type="button" ><span></span>Déconnexion</button></a></li>';
            } else {
                // Sinon, on affiche les boutons de connexion et d'inscription
                echo '<li><a href="./login.php"><button type="button"><span></span>Connexion/Inscription</button></a></li>';
            }
        ?>
        </ul>
    </div>
</header>

