<?php
session_start();

if (!isset($_SESSION['utilisateur_id']) && !isset($_COOKIE['utilisateur_id'])) {
    header("Location: /Login/login.php");
    exit;
}

if (isset($_COOKIE['utilisateur_id'])) {
    $_SESSION['utilisateur_id'] = $_COOKIE['utilisateur_id'];
}
?>