<?php
session_start();
include 'bootstrap.php';

if (!isset($_SESSION['apiculteur'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['num'])) {
        $num = (int) $_GET['num'];
        $sql2 = "SELECT * FROM ruche WHERE id_ruche = :num";
        $sth = $dbh->prepare($sql2);
        $sth->bindParam(':num', $num, PDO::PARAM_INT);
        $sth->execute();
        $ruche = $sth->fetch(PDO::FETCH_ASSOC);
        if (!$ruche) {
            header('Location: ./index.php');
            exit();
        }
        if (isset($ruche['loca_ruche'])) {
            $loca_ru = explode(',', $ruche['loca_ruche']);
        }
        echo head('Édition des ruches');
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
                            Édition de ruche
                        </h1>
                        <h2>
                            <?php echo date('d/m/Y'); ?>
                        </h2>
                    </div>
                    
                </div>
                <form method="post">
                    <input type="hidden" type="text" name="id_ruche" value="<?php echo $ruche['id_ruche'] ?>" />
                    <div class="classictxt">
                        <img src="./assets/svg/plus-square.svg" alt="">
                        <input type="text" name="nom_ruche" placeholder="Nom de la ruche" required="required" value="<?php echo $ruche['nom_ruche'] ?>" />
                    </div>
                    <div class="classictxt coords">
                        <img src="./assets/svg/map-pin.svg" alt="">
                        <input type="text" name="latitude" id="latitude" placeholder="Latitude" required="required" value="<?php echo trim($loca_ru[1]) ?>" />
                        <input type="text" name="longitude" id="longitude" placeholder="Longitude" required="required" value="<?php echo trim($loca_ru[0]) ?>" />
                        <input type="hidden" name="latitude_hidden" id="latitude_hidden">
                        <input type="hidden" name="longitude_hidden" id="longitude_hidden">
                    </div>
                    <button type="button" id="get-location">Obtenir la localisation</button>
                    <div class="classictxt">
                        <img src="./assets/svg/calendar.svg" alt="">
                        <input type="date" name="date_install" placeholder="Date d'installation" required="required" value="<?php echo $ruche['date_install'] ?>" />
                    </div>
                    <button type="submit" class="">Modifier</button>
                    <a href="index.php" class="annuler">Annuler</a>
                </form>
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
                latText.addEventListener("keyup", function() {
                    latHidden.value = latText.value;
                });

                longText.addEventListener("input", function() {
                    longHidden.value = longText.value;
                });
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
                    if (Number(longitude.value) <= -90 || Number(longitude.value) >= 90) {
                        alert("La longitude doit être comprise entre -180 et 180");
                        event.preventDefault();
                        return false;
                    }

                    // Vérification si le nouveau nom de la ruche n'existe pas déjà dans la base de données
                    let xhr = new XMLHttpRequest();
                    xhr.open('POST', 'check_ruche.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (this.responseText === "exists") {
                            alert("Le nom de ruche existe déjà dans la base de données.");
                            event.preventDefault();
                            return false;
                        } else {
                            form.submit();
                        }
                    };
                    xhr.send('nom_ruche=' + nom_ruche.value);

                    return false;
                });
            });
        </script>

    </html>
    <?php
    } else {
        header('Location: ./index.php');
        exit();
    }
} else {
    if (isset($_POST['id_ruche'], $_POST['nom_ruche'], $_POST['latitude_hidden'], $_POST['longitude_hidden'], $_POST['date_install'])) {
        $nom_ruche = $_POST['nom_ruche'];
        $loca_ruche = $_POST['longitude_hidden'] . "," . $_POST['latitude_hidden'];
        $date_install = $_POST['date_install'];
        $num = $_POST['id_ruche'];

        // Vérification si le nouveau nom de la ruche n'existe pas déjà dans la base de données
        $sql_check = "SELECT * FROM ruche WHERE nom_ruche = :nom_ruche";
        $stmt_check = $dbh->prepare($sql_check);
        $stmt_check->execute(['nom_ruche' => $nom_ruche]);
        $existingRuche = $stmt_check->fetch(PDO::FETCH_ASSOC);
        if ($existingRuche && $existingRuche['id_ruche'] != $num) {
            echo "exists";
            exit();
        }

        $sql = "UPDATE ruche SET nom_ruche = :nom_ruche, loca_ruche = :loca_ruche, date_install = :date_install WHERE id_ruche = :num";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            'nom_ruche' => trim($nom_ruche),
            'loca_ruche' => $loca_ruche,
            'date_install' => trim($date_install),
            'num' => $num
        ]);
        if ($dbh) {
            header('Location: ./index.php');
            exit();
        } else {
            echo "Erreur";
        }
    }
}
?>