<?php
session_start();
include 'bootstrap.php';

if (!isset($_SESSION['apiculteur'])) {
    header('Location: login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $num = (int) $_GET['num'];
    $sql2 = "SELECT * FROM ruche INNER JOIN visite ON ruche.id_ruche = visite.id_ruche  WHERE visite.id_visite = :num";
    $stmt = $dbh->prepare($sql2);
    $stmt->execute([
        ':num' => $num,
    ]);
    $ruche = $stmt->fetch();
    if (!$ruche) {
        header('Location: ./index.php');
    }
    echo head('Modifier une visite');
    $autresComportements = array("Calme", "Nerveuse", "Agressive", "Morte");
?>

    <body class="scroll-xb">
        <div>
            <form method="post">
                <input type="hidden" name="visite_id" value="<?php echo $ruche['id_visite'] ?>" />
                <label>Nom de la ruche:</label>
                <select id=ruche_id disabled>
                    <?php
                    echo "<option value='" . $ruche['id_ruche'] . "' selected>" . $ruche['nom_ruche'] . "</option>";
                    ?>
                </select>
                <label>Comportement:</label>
                <div class="classictxt">
                    <select name="comportement" id="comportement_list">
                        <?php
                        foreach ($autresComportements as $option) {
                            if ($option == $ruche['comportement']) { // Vérifier si l'option correspond au comportement sélectionné
                                echo "<option value='$option' selected>$option</option>"; // Afficher l'option sélectionnée
                            } else {
                                echo "<option value='$option'>$option</option>"; // Afficher les autres options
                            }
                        } ?>
                    </select>
                </div>
                <label>Force:</label>
                <input type="range" name="force" id="force" placeholder="Force" required="required" value="<?php echo ($ruche['strength']); ?>">
                <label>Commentaire:</label>
                <textarea type="text" name="commentaire" id="commentaire" placeholder="Ajouter un commentaire" required="required"><?php echo ($ruche['commentaire']); ?></textarea>
                <label>Date de la visite:</label>
                <input type="date" name="date_visite" placeholder="Date de la visite" required="required" value="<?php echo ($ruche['date_visite']); ?>" />
                <button type="submit" class="btn btn-primary btn-block btn-large">Modifier</button>
                <a href="./visite_delete.php?num=<?php echo $ruche['id_visite'] ?>" class="js-delete">Supprimer</a>
            </form>
        </div>
    </body>
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll(".js-delete").forEach(link => {
                link.addEventListener("click", function(event) {
                    // afficher un message simple de demande
                    if (confirm("Confirmez-vous la suppression de cette visite ?") == false) {
                        // si annuler, alors stopper le traitement par défaut du lien ( la redirection est annulée)
                        event.preventDefault();
                    }
                });
            });
            // ----------------- Gestion d'ajout des ruches -----------------
            // Vérification de la date d'installation minimum 6 mois avant la date du jour et maximum la date du jour
            let today = new Date();
            let dd = today.getDate();
            let mm = today.getMonth() + 1; //January is 0!
            let yyyy = today.getFullYear();

            if (dd < 10) {
                dd = '0' + dd;
            }
            if (mm < 10) {
                mm = '0' + mm;
            }

            let dateMax = yyyy + '-' + mm + '-' + dd;
            document.querySelector('input[type="date"]').setAttribute("max", dateMax);

            function subtract6Months(today) {
                // 👇 Make copy with "Date" constructor
                const dateCopy = new Date(today);

                dateCopy.setMonth(dateCopy.getMonth() - 6);

                return dateCopy;
            }

            const newDate = subtract6Months(today);
            let dd6 = newDate.getDate();
            let mm6 = newDate.getMonth() + 1; //January is 0!
            let yyyy6 = newDate.getFullYear();

            if (mm6 < 10) {
                mm6 = '0' + mm6;
            }
            if (dd6 < 10) {
                dd6 = '0' + dd6;
            }

            let dateMin = yyyy6 + "-" + mm6 + "-" + dd6;

            document.querySelector('input[type="date"]').setAttribute("min", dateMin);
            // Fin de la vérification de la date d'installation        

        });
    </script>

    </html>
<?php } else {
    if (isset($_POST['visite_id']) && isset($_POST['comportement']) && isset($_POST['force']) && isset($_POST['commentaire']) && isset($_POST['date_visite'])) {
        $id_visite = $_POST['visite_id'];
        $comportement = $_POST['comportement'];
        $date_visite = $_POST['date_visite'];
        $commentaire = $_POST['commentaire'];
        $force = $_POST['force'];
        $sql1 = "UPDATE visite SET comportement = '$comportement', date_visite = '$date_visite', commentaire = '$commentaire', strength = '$force' WHERE id_visite = '$id_visite'";
        $dbh->exec($sql1);
        if ($dbh) {
            header('Location: ./index.php');
        } else {
            echo "Erreur";
        }
    }
}


?>