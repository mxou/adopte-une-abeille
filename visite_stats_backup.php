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
?>
<style>
  .hidden{
    display:none;
  }
</style>
<body>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <main>
        <div class="selectContent">
            <select class="select_ruche">
              <option value="" disabled selected>Choisir une ruche</option>
                <?php
                foreach ($ruches as $ruche) {
                    echo "<option value='" . $ruche['id_ruche'] . "'>" . $ruche['nom_ruche'] . "</option>";
                }
                ?>
            </select>
            <select class="select_month">
              <option value="" disabled selected>Choisir un mois</option>
                <?php
                foreach ($months as $month) {
                    $monthNumber = $month['MONTH(date_visite)'];
                    $monthName = date('F', mktime(0, 0, 0, $monthNumber, 1));
                    echo "<option value='" . $monthNumber . "'>" . $monthName . "</option>";
                }
                ?>
            </select>
            <select class="select_year">
              <option value="" disabled selected>Choisir une année</option>
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
  const select_month = document.querySelector('.select_month');
  const select_year = document.querySelector('.select_year');

  const ctx = document.querySelector('#appChart');
  const chart = new Chart(ctx, {
    type: 'bar',
    data: {
      datasets: [{
        data: [], // Laissez le tableau vide pour le moment
        // borderWidth: 1
      }]
    },
    options: {
        scales: {
            y: {
                suggestedMin: 0,
                suggestedMax: 100
            }
        }
    }
  });
  
  selectContent.addEventListener('change', function() {
    let id_ruche = select_ruche.value;
    let id_month = select_month.value;
    let id_year = select_year.value;

    // Effectuer une requête AJAX
    let xhr = new XMLHttpRequest();
    xhr.open('GET', 'fetch_charts.php?id_ruche=' + id_ruche + '&id_month=' + id_month + '&id_year=' + id_year, true);
    xhr.onreadystatechange = function() {
      if (xhr.readyState === XMLHttpRequest.DONE) {
        if (xhr.status === 200) {
          // Mettre à jour le contenu de la balise <canvas> avec les nouvelles données
          const chartData = JSON.parse(xhr.responseText);
          updateChart(chartData);
          if(chartData.length > 0) {
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