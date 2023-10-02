<?php
session_start();
include 'bootstrap.php';
if (!isset($_SESSION['apiculteur'])) {
  header('Location: login.php');
  exit();
}
setlocale(LC_TIME, 'fr_FR');

$sql_month = "SELECT DISTINCT MONTH(date_visite) FROM visite";
$sql_year = "SELECT DISTINCT YEAR(date_visite) FROM visite";
$sql2 = "SELECT * FROM ruche WHERE id_apicul =" . $_SESSION['apiculteur']['id_apicul'];
$ruches = $dbh->query($sql2)->fetchAll();
$months = $dbh->query($sql_month)->fetchAll();
$years = $dbh->query($sql_year)->fetchAll();
echo head("Statistiques");
?>
<style>
  .hidden {
    display: none;
  }
</style>

<body class="list">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <header>
    <div class="ruche-add">
      <div>
        <div>
          <h1>
            Statistiques des visites
          </h1>
          <!-- <button onclick="self.location.href='./visite_add.php'">+</button> -->
          <h2>
            <?php echo date("d/m/Y"); ?>
          </h2>
        </div>
      </div>
    </div>
  </header>
  <main>
    <div class="selectContent selectlist">
      <select class="select_ruche">
        <option value="" disabled selected>Ruche</option>
        <?php
        foreach ($ruches as $ruche) {
          echo "<option value='" . $ruche['id_ruche'] . "'>" . $ruche['nom_ruche'] . "</option>";
        }
        ?>
      </select>
      <select class="select_year">
        <option value="" disabled selected>Année</option>
        <?php
        foreach ($years as $year) {
          echo "<option value='" . $year['YEAR(date_visite)'] . "'>" . $year['YEAR(date_visite)'] . "</option>";
        }
        ?>
      </select>
    </div>
    <div id="charts_container" class="list_visite hidden">
      <canvas id="appChart"></canvas>
    </div>


    <div class="menubar">
      <ul style="border:0px solid red;">
        <li><a class="menulink_home" href="./index.php"><img src="./assets/svg/home.svg" alt="Icone de maison"></a></li>
        <li><a class="menulink_stat" href="./visite_stats.php"><img src="./assets/svg/bar-chart-2.svg" alt="Icone de statistiques"></a></li>
        <li><a class="menulink_map" href="./carte.php"><img src="./assets/svg/map-pin.svg" alt="Icone de map-pin"></a></li>
        <li><a class="menulink_visit" href="./visite_list.php"><img src="./assets/svg/eye.svg" alt="Icone d'oeil"></a></li>
        <li><a class="menulink_exit" href="./logout.php"><img src="./assets/svg/log-out.svg" alt="Icone déconnéxion"></a></li>
      </ul>

      <script>
        document.addEventListener("DOMContentLoaded", function() {
          const currentPage = window.location.pathname;
          const iconhome = document.querySelector(".menulink_home img");
          const iconstat = document.querySelector(".menulink_stat img");
          const iconmap = document.querySelector(".menulink_map img");
          const iconvisit = document.querySelector(".menulink_visit img");
          const iconexit = document.querySelector(".menulink_exit img");

          if (currentPage.includes("index.php")) {
            iconhome.src = "./assets/svg/homeblue.svg";
          } else if (currentPage.includes("stat.php")) {
            iconstat.src = "./assets/svg/bar-chart-2blue.svg";
          } else if (currentPage.includes("carte.php")) {
            iconmap.src = "./assets/svg/map-pinblue.svg";
          } else if (currentPage.includes("visite_list.php")) {
            iconvisit.src = "./assets/svg/eyeblue.svg";
          }

        });
      </script>
    </div>

  </main>


  <script>
    function updateChart(chartData) {
      const chart = Chart.getChart('appChart');
      chart.data.datasets[0].data = chartData;
      chart.update();
    }

    window.addEventListener('DOMContentLoaded', function() {
      const select_ruche = document.querySelector('.select_ruche');
      const selectContent = document.querySelector('.selectContent');
      const chartsContainer = document.getElementById('charts_container');
      const select_year = document.querySelector('.select_year');

      const ctx = document.querySelector('#appChart');
      const chart = new Chart(ctx, {
        type: 'bar',
        data: {
          datasets: [{
            label: 'Moyenne des forces',
            data: [], // Laissez le tableau vide pour le moment
            borderColor: [
              'rgb(255, 99, 132)',
              'rgb(255, 159, 64)',
              'rgb(255, 205, 86)',
              'rgb(75, 192, 192)',
              'rgb(54, 162, 235)',
              'rgb(153, 102, 255)',
              'rgb(201, 203, 207)'
            ],
            backgroundColor: [
              'rgba(255, 99, 132, 0.2)',
              'rgba(255, 159, 64, 0.2)',
              'rgba(255, 205, 86, 0.2)',
              'rgba(75, 192, 192, 0.2)',
              'rgba(54, 162, 235, 0.2)',
              'rgba(153, 102, 255, 0.2)',
              'rgba(201, 203, 207, 0.2)'
            ],
          }]
        },
        options: {
          scales: {
            x: {
              ticks: {
                maxRotation: 45,
                minRotation: 0
              }
            },
            y: {
              suggestedMin: 0,
              suggestedMax: 100
            }
          }
        }
      });

      selectContent.addEventListener('change', function() {
        let id_ruche = select_ruche.value;
        let id_year = select_year.value;

        // Effectuer une requête AJAX
        let xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_charts.php?id_ruche=' + id_ruche + '&id_year=' + id_year, true);
        xhr.onreadystatechange = function() {
          if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
              // Mettre à jour le contenu de la balise <canvas> avec les nouvelles données
              const chartData = JSON.parse(xhr.responseText);
              updateChart(chartData);
              if (chartData.length > 0) {
                chartsContainer.classList.remove('hidden');
              } else {
                chartsContainer.classList.add('hidden');
              }
            } else {
              console.error('Erreur AJAX : ' + xhr.status);
            }
          }
        };
        xhr.send();
      });
    });
  </script>
</body>