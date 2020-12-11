<?php
// Start session in index page to avoid starting concurrent session on concurrent ajax call later
require_once('../api/lib/Container.php');
$container = new Container();
$session = $container->getSession();
?>
<!doctype html>
<html lang="fr" ng-app="roamingEditor">
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="manifest" href="manifest.json">
  <link rel="icon" type="image/png" href="img/favicon.png"/>
  <link rel="stylesheet" href="css/main.css?v=201208">
  <meta name="application-name" content="Editeur compte rendu"/>
  <meta name="msapplication-square70x70logo" content="img/appIcon-70x70.jpg"/>
  <meta name="msapplication-square150x150logo" content="img/appIcon-150x150.jpg"/>
  <meta name="msapplication-wide310x150logo" content="img/appIcon-310x150.jpg"/>
  <meta name="msapplication-square310x310logo" content="img/appIcon-310x310.jpg"/>
  <meta name="msapplication-TileColor" content="#E7402D"/>
  <script src="js/angular.min.js"></script>
  <script src="js/angular-route.min.js"></script>
  <script src="js/angular-cookies.min.js"></script>
  <script src="js/roamingEditor.js?v=201208-3"></script>
  <script type="text/javascript" src="//maps.googleapis.com/maps/api/js?v=3.exp&libraries=places&key=GOOGLE_API_KEY&ver=3.exp"></script>
  <title>VINCI - Edition compte rendu</title>
</head>
<body>
  <div id="roamingEditorApp">
    <div ng-view></div>
  </div>
</body>
</html>

