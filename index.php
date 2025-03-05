<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Mon équipe</title>
    </head>
    <body>
    <?php
        require_once __DIR__ . '/Login/auth.php';
        // Redirection vers gestion_joueur.php
        header("Location: Joueur/gestion_joueur.php");
        exit;
        ?>
    </body>
</html>