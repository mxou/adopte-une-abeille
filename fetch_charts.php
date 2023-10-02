<?php
session_start();
include 'bootstrap.php';
if (!isset($_SESSION['apiculteur'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id_ruche']) && isset($_GET['id_year'])) {
        $id_ruche = (int) $_GET['id_ruche'];
        $id_year = (int) $_GET['id_year'];

        $sql = "SELECT AVG(strength) as avg_strength, date_visite FROM `visite` WHERE id_ruche = :id_ruche AND YEAR(date_visite) = :id_year GROUP BY MONTH(date_visite) ORDER BY MONTH(date_visite) ASC";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':id_ruche', $id_ruche, PDO::PARAM_INT);
        $stmt->bindParam(':id_year', $id_year, PDO::PARAM_INT);
        $stmt->execute();
        $visites = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $chartData = [];
        foreach ($visites as $visite) {
            $month = date('F', strtotime($visite['date_visite'])); // Récupérer le mois au format numérique
            $month_in_french = [
                'January' => 'Janvier',
                'February' => 'Février',
                'March' => 'Mars',
                'April' => 'Avril',
                'May' => 'Mai',
                'June' => 'Juin',
                'July' => 'Juillet',
                'August' => 'Août',
                'September' => 'Septembre',
                'October' => 'Octobre',
                'November' => 'Novembre',
                'December' => 'Décembre'
            ];
            $month=$month_in_french[$month];
            $chartData[] = [
                'x' => $month,
                'y' => $visite['avg_strength']
            ];
        }

        // Renvoyer les données du graphique en tant que réponse JSON
        header('Content-Type: application/json');
        echo json_encode($chartData);
        exit();
    }
}