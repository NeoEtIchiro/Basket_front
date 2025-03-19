<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Mon équipe</title>
    </head>
    <body>
    <?php
        require_once './Controllers/auth.php';
        // Redirection vers gestion_joueur.php
        header("Location: ./Controllers/joueurController.php");
        exit;
        ?>
    </body>
</html>