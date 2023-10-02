<?php 
session_start();
require './bootstrap.php';

if (!isset($_SESSION['apiculteur'])) {
  header('Location: login.php');
}
echo head("Carte");
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8" />
  <title>Demo: Add custom markers in Mapbox GL JS</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" />
  <script src="https://api.tiles.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
  <link href="https://api.tiles.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css" rel="stylesheet" />
  <style>
    body {
      margin: 0;
      padding: 0;
    }

    #map {
      position: absolute;
      top: 0;
      bottom: 0;
      width: 100%;
    }

    .marker {
      background-image: url('mapbox-icon.png');
      background-size: cover;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      cursor: pointer;

    }

    .mapboxgl-ctrl-icon {
      transform: translate(-15.5%, -16%);
      opacity: 0.5;
    }




    .mapboxgl-popup {
      max-width: 200px;
    }

    .mapboxgl-popup-content {
      background-color: #ffffff;
      text-align: center;
      border-radius: 15px;
      font-size: 1em;
      font-family: 'Open Sans', sans-serif;
    }
  </style>
</head>

<body>
  <div id="loadscreen">
    <div class="loaderscreen"></div>
  </div>
  <div id="map">

    <input class="search" type="text" name="query" id="search-input" placeholder="Rechercher...">
    <div id="resultsContainer">
      <ul id="result-list"></ul>
    </div>
    <a class="addbutton" href="./ruches_add.php">+</a>

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

  </div>
  <?php
$sql = 'SELECT * FROM ruche WHERE id_apicul = :id_apicul';
$stmt = $dbh->prepare($sql);
$stmt->execute([
    'id_apicul' => $_SESSION['apiculteur']['id_apicul'],
]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

  ?>


  <script>
    const currentPage2 = window.location.pathname;
    const loadscreen = document.querySelector('#loadscreen');
    const loaderscreen = document.querySelector('.loaderscreen');


    if (currentPage2.includes("carte.php")) {

      setTimeout(() => {
        loadscreen.style.display = "none";
      }, "1000");
    }
// Gestion de la carte
    mapboxgl.accessToken = 'pk.eyJ1IjoicmFwaGl0aXRpIiwiYSI6ImNsZ3k0andyMDA1enEzZW05YjhtbnMzYXUifQ.kG7eG6VUDiHTfAbqGA74lg';

    const geojson = {
  "type": "FeatureCollection",
  "features": [
    <?php foreach ($result as $ruche) : ?> {
      "type": "Feature",
      "geometry": {
        "type": "Point",
        "coordinates": [<?php echo $ruche['loca_ruche']; ?>]
      },
      "properties": {
        "title": "<?php echo ucfirst($ruche['nom_ruche']); ?>",
        "description": 'coucou',
      }
    },
    <?php endforeach; ?>
  ]
};

    // Set bounds to San Francisco, California.
    const bounds = [
      [-0.0063, 45.7306], // Southwest coordinates
      [0.3461, 45.62874] // Northeast coordinates
    ];

    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/raphititi/clhbrtzes015601pg4h9ndu2c',
      center: [0.15738, 45.65137],
      zoom: 12,
      minZoom: 5,
    });
    map.addControl(
      new mapboxgl.GeolocateControl({
        positionOptions: {
          enableHighAccuracy: true
        },
        // When active the map will receive updates to the device's location as it changes.
        trackUserLocation: true,
        // Draw an arrow next to the location dot to indicate which direction the device is heading.
        showUserHeading: true
      })
    );
    const markers = [];
    // add markers to map
    for (const feature of geojson.features) {
      // create a HTML element for each feature
      const el = document.createElement('div');
      el.className = 'marker';
      el.style.backgroundImage = `url(./5737683.png)`;
      // make a marker for each feature and add it to the map
      const marker = new mapboxgl.Marker(el)
        .setLngLat(feature.geometry.coordinates)
        .setPopup(
          new mapboxgl.Popup({
            offset: 25
          }) // add popups
          .setHTML(
            `<h3>${feature.properties.title}</h3><p>${feature.properties.description}</p>`
          )
        )
        .addTo(map);
      markers.push(marker);
    }

    function centerOnMarker(ruche) {
      // Séparer les coordonnées en latitude et longitude
      const coordinates = ruche.loca_ruche.split(',');
      document.getElementById('resultsContainer').style.display = 'none';
      searchInput.value = null;
      // Vérifier si les coordonnées sont valides
      if (coordinates.length === 2) {
        const latitude = parseFloat(coordinates[0].trim());
        const longitude = parseFloat(coordinates[1].trim());

        // Centrer la carte sur les coordonnées de la ruche
        map.flyTo({
          center: [latitude, longitude],
          zoom: 14,
          essential: true
        });
        const tolerance = 0.000001;
        const marker = markers.find(m => Math.abs(m.getLngLat().lng - longitude) < tolerance && Math.abs(m.getLngLat().lat - latitude) < tolerance);

        if (marker) {
          marker.togglePopup();
        }
      }

    }

    // Fonction pour effectuer une requête AJAX lors de la saisie de recherche
    function searchRuches(query) {
      let xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
          if (xhr.status === 200) {
            let response = JSON.parse(xhr.responseText);
            updateResults(response);
          } else {
            console.error('Une erreur s\'est produite lors de la requête AJAX.');
          }
        }
      };

      // Effectuer la requête AJAX avec la chaîne de recherche en tant que paramètre
      xhr.open('GET', 'recherche.php?query=' + query);
      xhr.send();
    }

    // Fonction pour mettre à jour la liste de résultats avec les éléments filtrés
    function updateResults(results) {
      let resultList = document.getElementById('result-list');
      resultList.innerHTML = '';

      results.forEach(function(ruche) {
        let listItem = document.createElement('li');
        listItem.textContent = ruche.nom_ruche;
        listItem.addEventListener('click', function() {
          centerOnMarker(ruche);
        });
        resultList.appendChild(listItem);
      });
    }

    // Événement de saisie lors de la recherche
    const searchInput = document.getElementById('search-input');
    document.getElementById('resultsContainer').style.display = 'none';
    searchInput.addEventListener('input', function() {
      let query = searchInput.value.trim();

      if (query !== '') {
        searchRuches(query);
        showResultsContainer();
      } else {
        hideResultsContainer();
      }
    });

    // Fonction pour afficher la boîte de résultats
    function showResultsContainer() {
      document.getElementById('resultsContainer').style.display = 'block';
    }

    // Fonction pour masquer la boîte de résultats
    function hideResultsContainer() {
      document.getElementById('resultsContainer').style.display = 'none';
    }
  </script>
</body>

</html>