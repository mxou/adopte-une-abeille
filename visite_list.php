<?php
session_start();
include 'bootstrap.php';

if (!isset($_SESSION['apiculteur'])) {
    header('Location: login.php');
}
// define("VISITED","OK");
$sql = "SELECT * FROM visite LEFT JOIN ruche ON visite.id_ruche = ruche.id_ruche WHERE id_apicul =" . $_SESSION['apiculteur']['id_apicul'];
$visites = $dbh->query($sql)->fetchAll();
$sql2 = "SELECT * FROM ruche WHERE id_apicul =" . $_SESSION['apiculteur']['id_apicul'];
$sql_stats = "SELECT COUNT(visite.id_visite) AS nbvisite, AVG(visite.strength) AS moyenne_strength FROM visite LEFT JOIN ruche ON visite.id_ruche = ruche.id_ruche WHERE ruche.id_apicul = :id_apiculteur";
$sql_comportement = "SELECT comportement, COUNT(*) AS count_comportement FROM visite LEFT JOIN ruche ON visite.id_ruche = ruche.id_ruche WHERE ruche.id_apicul = :id_apiculteur GROUP BY comportement ORDER BY count_comportement DESC LIMIT 1";
$stmt_comportement = $dbh->prepare($sql_comportement);
$stmt_comportement->execute([
    'id_apiculteur' => $_SESSION['apiculteur']['id_apicul'],
]);
$comportement = $stmt_comportement->fetch(PDO::FETCH_ASSOC);
$ruches = $dbh->query($sql2)->fetchAll();
$stmt_stats = $dbh->prepare($sql_stats);
$stmt_stats->execute([
    'id_apiculteur' => $_SESSION['apiculteur']['id_apicul'],
]);
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
echo head('Liste des visites');
?>

<body class="list">
    <div class="background">
        <img src="./assets/svg/back_1.svg">
        <img src="./assets/svg/back_2.svg">
    </div>

    <div class="ruche-add">
        <div>
            <!-- <a href="./index.php"><img src="./assets/svg/arrow-left.svg" alt=""></a> -->
            <div>
                <h1>
                    Liste des visites
                </h1>
                <!-- <button onclick="self.location.href='./visite_add.php'">+</button> -->
                <h2>
                    <?php echo date("d/m/Y"); ?>
                </h2>
            </div>
        </div>
    </div>
    <main>

        <div class="list_visite">
            <div class="selectContent">
                <select class="select_ruche">
                    <option value="0" selected>Toutes mes ruches</option>
                    <?php
                    foreach ($ruches as $ruche) {
                        echo "<option value='" . $ruche['id_ruche'] . "'>" . $ruche['nom_ruche'] . "</option>";
                    } ?>
                </select>
                <select class="select_month">
                    <option value="0" selected>Toutes les visites</option>
                    <option value="7">7 derniers jours</option>
                    <option value="30">30 derniers jours</option>
                    <option value="180">6 derniers mois</option>
                    <option value="365">12 derniers mois</option>
                </select>

            </div>
            <div id="visites-container">
                <?php
                if (empty($visites)) {
                    echo "<p>Pas de visite</p>";
                } else {
                    echo '<div id="stats"><ul><li>Nombre de visites: <span>' . $stats['nbvisite'] . ' </span></li><li>Force moyenne: <span>' . $stats['moyenne_strength'] . '</span></li><li>Comportement récurrent: <span>' . $comportement['comportement'] . '</span></li></ul></div>';

                    foreach ($visites as $visite) {
                        echo '<div style="width:80vw;" class="card_ruches">
                                <p>
                                    <span style="padding-right:30%">' . $visite['nom_ruche'] . '</span> ' . $visite['date_visite'] . '
                                </p>
                                <p>
                                    <span>Comportement :</span>' . $visite['comportement'] . '
                                </p>
                                <p>
                                    <span>Force :</span><meter value="' . $visite['strength'] . '" max="100" min="0" low="20" hight="90"></meter>
                                </p>
                                <p>
                                    <span>Commentaire :</span>' . $visite['commentaire'] . '
                                </p>
                                <button class="editbtn" style="transform: rotate(0deg);"><img class="arrowdiv" src="./assets/svg/chevron-down.svg" alt="Petite flèche bas"></button>
                                <ul class="dropdown" style="display: none;">
                                    <li><a href="./visite_delete.php?num=' . $visite['id_visite'] . '" style="color: red;" class="js-delete"><i class="fa-solid fa-trash"></i> Supprimer</a></li>
                                    <li><a href="./visite_edit.php?num=' . $visite['id_visite'] . '"><i class="fa-solid fa-pen"></i> Editer</a></li>
                                </ul>
                            </div>';
                    }
                }
                ?>
            </div>

            <div class="menubar">
                <ul style="border:0px solid red;">
                    <li><a class="menulink_home" href="./index.php"><img src="./assets/svg/home.svg" alt="Icone de maison"></a></li>
                    <li><a class="menulink_stat" href="./visite_stats.php"><img src="./assets/svg/bar-chart-2.svg" alt="Icone de statistiques"></a></li>
                    <li><a class="menulink_map" href="./carte.php"><img src="./assets/svg/map-pin.svg" alt="Icone de map-pin"></a></li>
                    <li><a class="menulink_visit" href="./visite_list.php"><img src="./assets/svg/eye.svg" alt="Icone d'oeil"></a></li>
                    <li><a class="menulink_exit" href="./logout.php"><img src="./assets/svg/log-out.svg" alt="Icone déconnexion"></a></li>
                </ul>
            </div>
</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Sélectionnez les éléments appropriés et attachez les événements
  function attachEvents() {
    document.querySelectorAll('.editbtn').forEach(btn => {
      btn.addEventListener('click', function() {
        const dropdown = this.closest('.card_ruches').querySelector('.dropdown');
        dropdown.style.display = (dropdown.style.display === "none") ? "flex" : "none";
        this.style.transform = (dropdown.style.display === "none") ? "rotate(0deg)" : "rotate(180deg)";
      });
    });

    document.querySelectorAll(".js-delete").forEach(link => {
      link.addEventListener("click", function(event) {
        if (!confirm("Confirmez-vous la suppression de cette ruche ?")) {
          event.preventDefault();
        }
      });
    });
  }

  attachEvents(); // Attache les événements initiaux

  // Cachez le dropdown lorsqu'on clique en dehors
  document.addEventListener('click', function(event) {
    if (!event.target.closest('.editbtn')) {
      document.querySelectorAll('.dropdown').forEach(dropdown => {
        dropdown.style.display = "none";
      });
      document.querySelectorAll('.editbtn').forEach(btn => {
        btn.style.transform = "rotate(0deg)";
      });
    }
  });

  const select_ruche = document.querySelector('.select_ruche');
  const select_month = document.querySelector('.select_month');
  const selectContent = document.querySelector('.selectContent');
  const visitesContainer = document.getElementById('visites-container');

  selectContent.addEventListener('change', function() {
    const id_ruche = select_ruche.value;
    const id_month = select_month.value;

    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_visites.php?id_ruche=' + id_ruche + '&id_month=' + id_month, true);
    xhr.onreadystatechange = function() {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          visitesContainer.innerHTML = xhr.responseText;
          attachEvents(); // Réattache les événements après avoir récupéré de nouvelles visites
        } else {
          console.error('Erreur AJAX : ' + xhr.status);
        }
      }
    };
    xhr.send();
  });
});
</script>
<script src="https://kit.fontawesome.com/45762c6469.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script>

</script>
</main>