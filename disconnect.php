<?php
    // Déconnexion de l'utilisateur
    session_start();
    session_unset();
    print_r($_SESSION);
    session_destroy();

    header('Location:index.php');
?>