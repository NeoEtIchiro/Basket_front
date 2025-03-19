<?php
require_once 'auth.php';

// Base URL de l'API (à modifier selon votre configuration)
$apiBaseUrl = 'http://api-sport.alwaysdata.net/endoints/endpointJoueur.php';

// Fonction pour effectuer une requête API via cURL
function callApi($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    
    // Pour DELETE, passer l'id dans l'URL
    if ($method === 'DELETE' && $data && isset($data['id'])) {
        $url .= '?id=' . urlencode($data['id']);
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Préparer un tableau d'en-têtes HTTP
    $headers = [];

    if ($method === 'POST' || $method === 'PUT') {
        // On envoie du JSON dans le corps de la requête
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    // Exécution de la requête et récupération de la réponse
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['response' => $response, 'code' => $httpCode];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        // Préparation des données pour la mise à jour
        $data = [
            'id'              => $_POST['id'],
            'nom'             => $_POST['nom'],
            'prenom'          => $_POST['prenom'],
            'numero_licence'  => $_POST['numero_licence'],
            'date_naissance'  => $_POST['date_naissance'],
            'taille'          => $_POST['taille'],
            'poids'           => $_POST['poids'],
            'commentaire'     => $_POST['commentaire'],
            'statut'          => $_POST['statut']
        ];
        // Appel de l'API pour mettre à jour le joueur (méthode PUT)
        $result = callApi($apiBaseUrl . '/update', 'PUT', $data);
        header("Location: gestion_joueur.php");
        exit;
    } elseif (isset($_POST['add'])) {
        // Préparation des données pour l'ajout d'un joueur
        $data = [
            'nom'             => $_POST['nom'],
            'prenom'          => $_POST['prenom'],
            'numero_licence'  => $_POST['numero_licence'],
            'date_naissance'  => $_POST['date_naissance'],
            'taille'          => $_POST['taille'],
            'poids'           => $_POST['poids'],
            'commentaire'     => $_POST['commentaire'],
            'statut'          => $_POST['statut']
        ];
        // Appel de l'API pour ajouter un joueur (méthode POST)
        $result = callApi($apiBaseUrl . '/add', 'POST', $data);
        header("Location: gestion_joueur.php");
        exit;
    } elseif (isset($_POST['delete_joueur'])) {
        // Préparation de l'id pour la suppression
        $data = ['id' => $_POST['id']];
        // Appel de l'API pour supprimer le joueur (méthode DELETE, avec id placé dans l'URL)
        $result = callApi($apiBaseUrl . '/delete', 'DELETE', $data);
        header("Location: gestion_joueur.php");
        exit;
    }
}

// Récupération de tous les joueurs via l'API
$result = callApi($apiBaseUrl . '/all', 'GET');
$joueurs = json_decode($result['response'], true);

include_once __DIR__ . '/../Views/joueurView.php';
?>