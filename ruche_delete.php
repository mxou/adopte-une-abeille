<?php
session_start();
include 'bootstrap.php';

if (!isset($_SESSION['apiculteur'])) {
    header('Location: login.php');
} else {
    if (isset($_GET['num'])) {
        $id = (int) $_GET['num'];
        $sql = 'DELETE FROM ruche WHERE id_ruche = :id';
        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            ':id' => $id,
        ]);
    }
    header('Location: ./index.php');
}
?>