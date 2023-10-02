<?php
session_start();
include 'bootstrap.php';

if (!isset($_SESSION['apiculteur'])) {
    header('Location: login.php');
    exit;
}
// Vérifiez si une requête de recherche a été soumise
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $searchQuery = $_GET['query'];

    // Effectuez une requête SQL pour rechercher les ruches correspondantes
    $sql = "SELECT * FROM ruche WHERE nom_ruche LIKE :searchQuery";
    $stmt = $dbh->prepare($sql);
    $stmt->bindValue(':searchQuery', $searchQuery . '%');
    $stmt->execute();
    $results = $stmt->fetchAll();

    // Renvoie les résultats au format JSON
    header('Content-Type: application/json');
    echo json_encode($results);
    exit;
}
?>