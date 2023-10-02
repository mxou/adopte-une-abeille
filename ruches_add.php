<!-- SCRIPT AJOUT DE RUCHE RAPHAEL TIPHONET -->
<!-- A faire : nom de ruches existant(css), dimension les inputs -->
<?php
session_start();
include 'bootstrap.php';

if (!isset($_SESSION['apiculteur'])) {
    header('Location: login.php');
    exit();
}

if (isset($_POST['nom_ruche']) && isset($_POST['longitude_hidden']) && isset($_POST['latitude_hidden']) && isset($_POST['date_install'])) {
    $nom_ruche = $_POST['nom_ruche'];
    $loca_ruche = $_POST['longitude_hidden'] . "," . $_POST['latitude_hidden'];
    $date_install = $_POST['date_install'];

    if ($_FILES['photo_ruche']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo_ruche']['tmp_name'];
        $photo = base64_encode(file_get_contents($file));
    } else {
        // Gestion de l'erreur d'upload du fichier, si nécessaire
        $photo = null;
    }

    $sql = 'SELECT COUNT(*) AS ruche_count FROM ruche WHERE nom_ruche = :nom_ruche AND id_apicul = :id_apicul';
    $stmt = $dbh->prepare($sql);
    $stmt->execute([
        'nom_ruche' => $nom_ruche,
        'id_apicul' => $_SESSION['apiculteur']['id_apicul'],
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['ruche_count'] > 0) {
        $message = "La ruche existe déjà";
    } else {

        $sql = 'INSERT INTO ruche (`nom_ruche`,`loca_ruche`,`date_install`,`id_apicul`,`photo_ruche` )
            VALUES( :nom_ruche, :loca_ruche, :date_install, :id_apicul, :photo_ruche )';

        $sth = $dbh->prepare($sql);

        $sth->execute([
            'nom_ruche' => trim($nom_ruche),
            'loca_ruche' => trim($loca_ruche),
            'date_install' => $date_install,
            'id_apicul' => $_SESSION['apiculteur']['id_apicul'],
            'photo_ruche' => $photo,
        ]);

        header('Location: index.php');
        exit();
    }
}

echo head('Ajouter une ruche');
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
                    Ajouter une ruche
                </h1>
                <h2>
                    <?php echo date("d/m/Y"); ?>
                </h2>
            </div>
        </div>
        <div>
            <div id="ruche_error">
                <?php if (isset($message)) { ?>
                    <p style="color: red;"><?php echo $message; ?></p>
                <?php } ?>
            </div>

            <form method="post" enctype="multipart/form-data">

                <div class="classictxt">
                    <img src="./assets/svg/plus-square.svg" alt="">
                    <input type="text" name="nom_ruche" placeholder="Nom de la ruche" required="required" />
                </div>
                <div class="classictxt coords">
                    <img src="./assets/svg/map-pin.svg" alt="carte longitude">
                    <input type="text" name="latitude" id="latitude" placeholder="Latitude" required="required" />
                    <input type="text" name="longitude" id="longitude" placeholder="Longitude" required="required" />
                </div>
                <button type="button" id="get-location">Obtenir la localisation</button>
                <input type="hidden" name="latitude_hidden" id="latitude_hidden">
                <input type="hidden" name="longitude_hidden" id="longitude_hidden">
                <div class="classictxt">
                    <img src="./assets/svg/image.svg" alt="">
                    <input type="file" name="photo_ruche" id="photo_ruche" placeholder="Photo de la ruche" required="required" />
                </div>
                <!-- <div class="classictxt">
                    <img src="./assets/svg/align-left.svg" alt="">
                    <input type="text" name="description" id="description" placeholder="Description" required="required" />
                </div> -->
                <div class="classictxt">
                    <img src="./assets/svg/calendar.svg" alt="">
                    <input type="date" name="date_install" placeholder="Date d'installation" required="required" />
                </div>
                <button type="submit" class="">Ajouter</button>
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
        let mm = today.getMonth() + 1; // January is 0!
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
        let mm6 = newDate.getMonth() + 1; // January is 0!
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

        // Récupération de la localisation de l'utilisateur
        let button = document.getElementById("get-location");
        let latText = document.getElementById("latitude");
        let longText = document.getElementById("longitude");
        let form = document.querySelector("form");
        let latHidden = document.querySelector("#latitude_hidden");
        let longHidden = document.querySelector("#longitude_hidden")


        button.addEventListener("click", () => {
            event.preventDefault();
            navigator.geolocation.getCurrentPosition((position) => {
                let lat = position.coords.latitude;
                let long = position.coords.longitude;
                console.log(lat, long);
                latText.value = lat.toFixed(4);
                longText.value = long.toFixed(4);
                latHidden.value = lat.toFixed(4);
                longHidden.value = long.toFixed(4);
                latText.disabled = true;
                longText.disabled = true;
            });
        });
        // Fin de la récupération de la localisation de l'utilisateur
        latText.addEventListener("keyup", function() {
            latHidden.value = latText.value;
        });

        longText.addEventListener("input", function() {
            longHidden.value = longText.value;
        });
        // Validation des données du formulaire
        form.addEventListener('submit', (event) => {
            let nom_ruche = document.querySelector('input[name="nom_ruche"]');
            let latitude = document.querySelector('input[name="latitude"]');
            let longitude = document.querySelector('input[name="longitude"]');
            let date_install = document.querySelector('input[name="date_install"]');

            if (nom_ruche.value.length < 3) {
                alert("Le nom de la ruche doit contenir au moins 3 caractères");
                event.preventDefault();
                return false;
            }

            if (Number(latitude.value) <= -90 || Number(latitude.value) >= 90) {
                alert("La latitude doit être comprise entre -90 et 90");
                event.preventDefault();
                return false;
            }

            if (Number(longitude.value) <= -180 || Number(longitude.value) >= 180) {
                alert("La longitude doit être comprise entre -180 et 180");
                event.preventDefault();
                return false;
            }

        });

    });
</script>

</html>