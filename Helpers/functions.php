<?php
/*
    Fichier : /Helpers/functions.php
 */

/**
 * Retourne le contenu HTML du bloc d'en tête d'une page.
 * Deux CSS sont automatiquement intégré :
 *   - pico.css
 *   - custom.css
 *
 * @param string title le titre de la page.
 * @return string
 */
function head(string $title = ''): string
{
    return  <<<HTML_HEAD
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
 
  <link rel="stylesheet" href="./assets/css/style.css">
  <title>$title</title>
</head>

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
} else if  (currentPage.includes("visite_stats.php")) {
iconstat.src = "./assets/svg/bar-chart-2blue.svg";
} else if  (currentPage.includes("carte.php")) {
iconmap.src = "./assets/svg/map-pinblue.svg";
} else if  (currentPage.includes("visite_list.php")) {
iconvisit.src = "./assets/svg/eyeblue.svg";
} 

});

</script>
HTML_HEAD;
}


/**
 * Retourne vrai si la méthode d'appel est GET.
 */
function isGetMethod(): bool
{
    return  ($_SERVER['REQUEST_METHOD'] === 'GET') ;
}

/**
 * Retourne vrai si la méthode d'appel est POST.
 */
function isPostMethod(): bool
{
    return  ($_SERVER['REQUEST_METHOD'] === 'POST') ;
}
