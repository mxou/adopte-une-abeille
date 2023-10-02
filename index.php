<?php
session_start();
include 'bootstrap.php';

if (!isset($_SESSION['apiculteur'])) {
    header('Location: login.php');
    exit();
}

echo head('Accueil');
?>




<body>
    <header>
        <div class="head_ruche">
            <img class="avatar" src="./assets/img/avatar.png" alt="avatar" style="width: 20%;">
            <div class="today_info">
                <div><img src="./assets/svg/sunrise.svg">
                    <p class="sunrise">--H--</p>
                </div>
                <div><img src="./assets/svg/thermometer.svg">
                    <p class="temp">15°C</p>
                </div>
            </div>

        </div>
        <div>
            <h1 class="bjr">Bonjour
                <span><?php echo ucfirst($_SESSION["apiculteur"]["pnom"]) ?></span>👋
            </h1>
        </div>
    </header>
    <main>
        <div class="list_ruche">
            <div class="list_ruche_btn">
                <button onclick="window.location.href='./ruches_add.php'">Ajouter une ruche</button>
                <button onclick="window.location.href='./visite_add.php'">Ajouter une visite</button>
            </div>
            <?php

            $sql = "SELECT * FROM ruche WHERE id_apicul =" . $_SESSION['apiculteur']['id_apicul'];
            $ruches = $dbh->query($sql)->fetchAll();
            foreach ($ruches as $ruche) { ?>

                <div class="card_ruches">
                    <p>
                        <span><?php echo $ruche['nom_ruche']; ?></span>
                    </p>
                    <p>
                        <span>Emplacement :</span> <span><?php echo $ruche['loca_ruche']; ?></span>
                    </p>
                    <p>
                        <span>Date d'installation :</span> <span><?php echo $ruche['date_install']; ?></span>
                    </p>
                    <button class="editbtn"><img class="arrowdiv" src="./assets/svg/chevron-down.svg" alt="Petite flèche bas"></button>
                    <p></p>
                    <p>

                    </p>
                    <ul class="dropdown">
                        <li><a href="./ruche_delete.php?num=<?php echo $ruche['id_ruche'] ?>" style="color: red;" class="js-delete"><i class="fa-solid fa-trash"></i> Supprimer</a></li>
                        <li><a href="./ruche_edit.php?num=<?php echo $ruche['id_ruche']   ?>"><i class="fa-solid fa-pen"></i> Éditer</a></li>
                        <!-- <li><a href="./visite_add.php?num=<?php echo $ruche['id_ruche']   ?>" style="color: black;"><i class="fa-solid fa-add"></i> Aj. Visite</a></li> -->
                    </ul>
                </div>
            <?php
            } ?>

        </div>

    </main>
    <div class="menubar">
        <ul style="border:0px solid red;">
            <li><a class="menulink_home" href="./index.php"><img src="./assets/svg/home.svg" alt="Icone de maison"></a></li>
            <li><a class="menulink_stat" href="./visite_stats.php"><img src="./assets/svg/bar-chart-2.svg" alt="Icone de statistiques"></a></li>
            <li><a class="menulink_map" href="./carte.php"><img src="./assets/svg/map-pin.svg" alt="Icone de map-pin"></a></li>
            <li><a class="menulink_visit" href="./visite_list.php"><img src="./assets/svg/eye.svg" alt="Icone d'oeil"></a></li>
            <li><a class="menulink_exit" href="./logout.php"><img src="./assets/svg/log-out.svg" alt="Icone déconnéxion"></a></li>
        </ul>
    </div>
</body>
<script src="https://kit.fontawesome.com/45762c6469.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script>

document.querySelectorAll(".js-delete").forEach(link => {
            link.addEventListener("click", function(event) {
                // Afficher une boîte de dialogue de confirmation
                if (!confirm("Confirmez-vous la suppression de cette ruche ?")) {
                    // Si l'utilisateur annule, empêcher le traitement par défaut du lien (la redirection est annulée)
                    event.preventDefault();
                }
            });
        });

    const sunrise_dom = document.querySelector('.sunrise');
    const temp_dom = document.querySelector('.temp');
    let url_sunrise = "https://api.openweathermap.org/data/3.0/onecall?lat=45.648377&lon=0.1562369&exclude=minutely,hourly,daily,alerts&appid=ed3353766d1e7222247e9c1838a1b31c&units=metric";
    let blabla = fetch(url_sunrise);
    blabla.then((response) => {
        return response.json();
    }).then((data) => {
        console.log(data);
        let sunrise = data.current.sunrise;
        let temp = data.current.temp;
        temp = Math.round(temp);

        sunrise = new Date(sunrise * 1000).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });
        sunrise_dom.innerHTML = sunrise;
        temp_dom.innerHTML = temp + "°C";
    });



    const editbtns = document.querySelectorAll('.editbtn');
    const dropdown = document.querySelectorAll('.dropdown');
    const arrowdiv = document.querySelector('.arrowdiv');

    editbtns.forEach(btn => {
        let compt = 0;
        btn.addEventListener('click', function() {

            // console.log(this, this.nextSibling.nextSibling, this.nextElementSibling, this.closest('.card_ruches').querySelector('ul.dropdown') )
            if (compt === 0) {
                this.closest('.card_ruches').querySelector('ul.dropdown').style.display = "flex";
                compt = 1;
                this.style.transform = "rotate(180deg)";
            } else {
                this.closest('.card_ruches').querySelector('ul.dropdown').style.display = "none";
                this.style.transform = "rotate(0deg)";

                compt = 0;
            }
        });
    })
</script>

</html>