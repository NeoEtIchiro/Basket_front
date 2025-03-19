<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $mot_de_passe = $_POST['mot_de_passe'];

    // URL de l'endpoint d'authentification de l'API-sport
    $apiUrl = 'http://api-auth.alwaysdata.net/endPointAuth.php';

    // Préparation des données à envoyer (en JSON)
    $data = [
        'login'    => $email,
        'password' => $mot_de_passe,
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode === 200 && isset($result['data'])) { 
        // Stockage du JWT dans un cookie (utilisé pour l'API-sport)
        setcookie('jwt', $result['data'], time() + (86400 * 30), "/"); // 30 jours
        // On peut également sauvegarder des infos en session si besoin
        $_SESSION['utilisateur_id'] = $email;
        header("Location: ../index.php");
        exit;
    } else {
        $erreur = "Email ou mot de passe incorrect." . $httpCode;
    }
}

include_once __DIR__ . '/../Views/loginView.php';
?>