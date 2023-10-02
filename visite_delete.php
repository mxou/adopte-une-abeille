<?php
session_start();
include 'bootstrap.php';

if (!isset($_SESSION['apiculteur'])) {
    header('Location: login.php');
}else{

if (isset($_GET['num'])) {
    $id = (int) $_GET['num'];
    $sql = 'DELETE FROM visite WHERE id_visite = :id';
    $stmt = $dbh->prepare($sql);
    $stmt->execute([
        ':id' => $id,
    ]);
}
header('Location: ./visite_list.php');
}