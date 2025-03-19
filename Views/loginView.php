<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Css/login.css">
    <title>Connexion</title>
</head>
<body>
    <div class="container">
        <h1>Connexion</h1>
        <?php if (isset($erreur)): ?>
            <p class="error"><?= $erreur ?></p>
        <?php endif; ?>
        <form method="post" action="login.php">
            <input type="email" id="email" name="email" placeholder="Email" required>
            <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Mot de passe" required>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>