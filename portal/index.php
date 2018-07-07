<?php
// Start session in index page to avoid starting concurrent session on concurrent ajax call later
require_once('../api/lib/Container.php');
$container = new Container();
$session = $container->getSession();
?>
<!doctype html>
<html lang="fr" ng-app="roamingPortal">
<!-- Roaming Editor - License GNU GPL - https://github.com/Kysic/roamingEditor -->
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="img/favicon.png"/>
  <link rel="stylesheet" href="css/main.css"/>
  <link rel="stylesheet" href="css/angular-loading-bar.min.css"/>
  <link rel="stylesheet" href="css/dialog-mobile.css"/>
  <script type="text/javascript" src="js/angular.min.js"></script>
  <script type="text/javascript" src="js/angular-route.min.js"></script>
  <script type="text/javascript" src="js/angular-loading-bar.min.js"></script>
  <script type="text/javascript" src="js/roamingPortal.js"></script>
  <title>VINCI - Samu Social de Grenoble</title>
</head>
<body>
  <script type="text/javascript" src="js/mcx-dialog.js"></script>
  <div id="roamingPortalApp">
    <div ng-view></div>
  </div>
  <div id="footer">
    &nbsp;
  </div>
</body>
</html>
