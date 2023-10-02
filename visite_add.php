<?php
session_start();
include 'bootstrap.php';

if (!isset($_SESSION['apiculteur'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['ruche_id']) && isset($_POST['comportement']) && isset($_POST['force']) && isset($_POST['commentaire']) && isset($_POST['date_visite'])) {
    $id_ruche = $_POST['ruche_id'];
    $comportement = $_POST['comportement'];
    $date_visite = $_POST['date_visite'];
    $commentaire = $_POST['commentaire'];
    $force = $_POST['force'];

    $sql1 = "INSERT INTO visite (date_visite, comportement, strength, commentaire, id_ruche) VALUES (:date_visite, :comportement, :strength, :commentaire, :id_ruche)";
    $stmt = $dbh->prepare($sql1);
    $stmt->execute([
        'date_visite' => $date_visite,
        'comportement' => $comportement,
        'strength' => $force,
        'commentaire' => $commentaire,
        'id_ruche' => $id_ruche,
    ]);
    if ($stmt) {
        header('Location: index.php');
        exit();
    } else {
        echo "Erreur";
    }
}
else{
$sql = "SELECT nom_ruche, id_ruche FROM ruche WHERE id_apicul = " . $_SESSION['apiculteur']['id_apicul'];
$stmt = $dbh->prepare($sql);
$stmt->execute();
$ruches = $stmt->fetchAll();
echo head()

?>

<body class="scroll-xb">
<div class="background">
        <img src="./assets/svg/back_1.svg">
        <img src="./assets/svg/back_2.svg">
    </div>
    <div class="ruche-add">
        <div>
            <a href="./index.php"><img src="./assets/svg/arrow-left.svg" alt=""></a>
            <div>
                 <h1>
                     Ajouter une visite
                 </h1>
                 <h2>
                     <?php echo date("d/m/Y"); ?>
                 </h2>
            </div>
        </div>
        <div>
            <form method="post">
            <div class="comment">
                <label>Nom de la ruche:</label>
                <select name="ruche_id" id="ruche_id">
                    <option value="0" disabled selected>Choisir une ruche</option>
                    <?php
                    foreach ($ruches as $ruche) {
                        echo "<option value='" . $ruche['id_ruche'] . "'>" . $ruche['nom_ruche'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
            <label>Comportement:</label>
            <select name="comportement" id="comportement_list">
                <option value="Calme">Calme</option>
                <option value="Nerveuse">Nerveuse</option>
                <option value="Agressive">Agressive</option>
                <option value="Morte">Morte</option>
                </select>
            </div>
            <div class="comment">
                <label>Force:</label>
                <input type="range" name="force" id="force" placeholder="Force" required="required">
            </div>
            <label>Commentaire:</label>
            <div>
                <textarea type="text" name="commentaire" id="commentaire" placeholder="Ajouter un commentaire" required="required"></textarea>
            </div>
            <label>Date:</label>
            <div class="classictxt">
            
                <input type="date" name="date_visite" placeholder="Date de la visite" required="required" />
            </div>
                <button type="submit" class="btn btn-primary btn-block btn-large">Ajouter</button>
            </form>
        </div>
    </div>
</body>
<script>
    window.addEventListener('DOMContentLoaded', function() {

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

        // Récupération de la localisation de l'utilisateu
        let form = document.querySelector("form");

        form.addEventListener('submit', (event) => {

            let rucheId = document.querySelector('#ruche_id');

            if (Number(rucheId.value) === 0) {
                alert("Veuillez choisir une ruche.");
                event.preventDefault();
                return false;
            }

            return true;
        });



    });
</script>

</html>
<?php } ?>