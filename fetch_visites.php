<?php
session_start();
include 'bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id_ruche']) && isset($_GET['id_month'])) {
        $id_ruche = (int) $_GET['id_ruche'];
        $id_month = (int) $_GET['id_month'];

        // Vérifier si la valeur de l'ID de la ruche est 0 (toutes les ruches)
        if ($id_ruche == 0) {
            // Requête SQL pour récupérer toutes les visites
            $sql_stats = "SELECT COUNT(visite.id_visite) AS nbvisite, AVG(visite.strength) AS moyenne_strength FROM visite LEFT JOIN ruche ON visite.id_ruche = ruche.id_ruche WHERE ruche.id_apicul = :id_apiculteur";
            $sql_visites = "SELECT ruche.nom_ruche, visite.comportement, visite.strength, visite.commentaire, visite.id_visite, visite.date_visite FROM visite LEFT JOIN ruche ON visite.id_ruche = ruche.id_ruche WHERE ruche.id_apicul = :id_apiculteur";
            $sql_comportement = "SELECT comportement, COUNT(*) AS count_comportement FROM visite LEFT JOIN ruche ON visite.id_ruche = ruche.id_ruche WHERE ruche.id_apicul = :id_apiculteur GROUP BY comportement ORDER BY count_comportement DESC LIMIT 1";
        } else {
            // Requête SQL pour récupérer les visites en fonction de l'ID de la ruche
            $sql_stats = "SELECT COUNT(visite.id_visite) AS nbvisite, AVG(visite.strength) AS moyenne_strength FROM visite LEFT JOIN ruche ON visite.id_ruche = ruche.id_ruche WHERE ruche.id_apicul = :id_apiculteur AND visite.id_ruche = :id_ruche";
            $sql_visites = "SELECT ruche.nom_ruche, visite.comportement, visite.strength, visite.commentaire, visite.id_visite, visite.date_visite FROM visite LEFT JOIN ruche ON visite.id_ruche = ruche.id_ruche WHERE ruche.id_apicul = :id_apiculteur AND visite.id_ruche = :id_ruche";
            $sql_comportement = "SELECT comportement, COUNT(*) AS count_comportement FROM visite LEFT JOIN ruche ON visite.id_ruche = ruche.id_ruche WHERE ruche.id_apicul = :id_apiculteur AND visite.id_ruche = :id_ruche GROUP BY comportement ORDER BY count_comportement DESC LIMIT 1";
        }

        // Ajouter une condition pour la sélection de la date
        if ($id_month > 0) {
            $date_filter = date('Y-m-d', strtotime("-{$id_month} days"));
            $sql_stats .= " AND visite.date_visite >= :date_filter";
            $sql_visites .= " AND visite.date_visite >= :date_filter";
        }


        $stmt_stats = $dbh->prepare($sql_stats);
        $stmt_stats->bindParam(':id_apiculteur', $_SESSION['apiculteur']['id_apicul'], PDO::PARAM_INT);
        $stmt_comportement = $dbh->prepare($sql_comportement);

        if ($id_ruche != 0) {
            $stmt_comportement->execute([
                'id_apiculteur' => $_SESSION['apiculteur']['id_apicul'],
                'id_ruche' => $id_ruche,
            ]);

            $comportement = $stmt_comportement->fetch(PDO::FETCH_ASSOC);

            $stmt_stats = $dbh->prepare($sql_stats);
            $stmt_stats->bindParam(':id_apiculteur', $_SESSION['apiculteur']['id_apicul'], PDO::PARAM_INT);
            $stmt_stats->bindParam(':id_ruche', $id_ruche, PDO::PARAM_INT);

            if ($id_month > 0) {
                $stmt_stats->bindParam(':date_filter', $date_filter, PDO::PARAM_STR);
            }

            $stmt_stats->execute();
            $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt_comportement->execute([
                'id_apiculteur' => $_SESSION['apiculteur']['id_apicul'],
            ]);

            $comportement = $stmt_comportement->fetch(PDO::FETCH_ASSOC);

            $stmt_stats = $dbh->prepare($sql_stats);
            $stmt_stats->bindParam(':id_apiculteur', $_SESSION['apiculteur']['id_apicul'], PDO::PARAM_INT);

            if ($id_month > 0) {
                $stmt_stats->bindParam(':date_filter', $date_filter, PDO::PARAM_STR);
            }

            $stmt_stats->execute();
            $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
        }

        // Générer le code HTML des statistiques
        if (empty($stats['nbvisite'])) {
            $html_stats = '';
        } else if($comportement == null){
            $html_stats = '<div id="stats"><ul><li>Nombre de visites: <span>' . $stats['nbvisite'] . ' </span></li><li>Force moyenne: <span>' . $stats['moyenne_strength'] . '</span></li><li>Comportement récurrent: <span> Pas de comportement</span></li></ul></div>';
        }else{
            $html_stats = '<div id="stats"><ul><li>Nombre de visites: <span>' . $stats['nbvisite'] . ' </span></li><li>Force moyenne: <span>' . $stats['moyenne_strength'] . '</span></li><li>Comportement récurrent: <span>'.$comportement['comportement'].'</span></li></ul></div>';
        }

        // Envoyer le code HTML des statistiques
        echo $html_stats;

        $stmt_visites = $dbh->prepare($sql_visites);
        $stmt_visites->bindParam(':id_apiculteur', $_SESSION['apiculteur']['id_apicul'], PDO::PARAM_INT);

        if ($id_ruche != 0) {
            $stmt_visites->bindParam(':id_ruche', $id_ruche, PDO::PARAM_INT);
        }

        if ($id_month > 0) {
            $stmt_visites->bindParam(':date_filter', $date_filter, PDO::PARAM_STR);
        }

        $stmt_visites->execute();
        $visites = $stmt_visites->fetchAll();

        $html_visites = '';
        foreach ($visites as $visite) {
            $html_visites .= '<div style="width:80vw;" class="card_ruches">';
            $html_visites .= '<p><span style="padding-right:30%">' . $visite['nom_ruche'] . '</span>' . $visite['date_visite'] . '</p>';
            $html_visites .= '<p><span>Comportement :</span>' . $visite['comportement'] . '</p>';
            $html_visites .= '<p><span>Force :</span><meter value="' . $visite['strength'] . '" max="100" min="0" low="20" high="90"></meter></p>';
            $html_visites .= '<p><span>Commentaire :</span>' . $visite['commentaire'] . '</p>';
            $html_visites .= '<button class="editbtn" style="transform: rotate(0deg);"><img class="arrowdiv" src="./assets/svg/chevron-down.svg" alt="Petite flèche bas"></button>';
            $html_visites .= '<ul class="dropdown" style="display: none;">';
            $html_visites .= '<li><a href="./visite_delete.php?num=' . $visite['id_visite'] . '" style="color: red;"><i class="fa-solid fa-trash"></i> Supprimer</a></li>';
            $html_visites .= '<li><a href="./visite_edit.php?num=' . $visite['id_visite'] . '"><i class="fa-solid fa-pen"></i> Editer</a></li></ul></div>';
        }

        // Envoyer le code HTML des visites
        echo $html_visites;
    }
}
