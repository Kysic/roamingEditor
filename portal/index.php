<?php
// Start session in index page to avoid starting concurrent session on concurrent ajax call later
require_once('../api/lib/Container.php');
$container = new Container();
try {
    $session = $container->getSession();
} catch (Exception $e) {
    echo '<!-- '.$e->getMessage().' -->';
}
?>
<!doctype html>
<html lang="fr" ng-app="roamingPortal">
<!-- Roaming Editor - License GNU GPL - https://github.com/Kysic/roamingEditor -->
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="img/favicon.png"/>
  <link rel="stylesheet" href="css/main.css?v=2"/>
  <link rel="stylesheet" href="css/angular-loading-bar.min.css?v=1"/>
  <link rel="stylesheet" href="css/dialog-mobile.css"/>
  <script type="text/javascript" src="js/angular.min.js"></script>
  <script type="text/javascript" src="js/angular-route.min.js"></script>
  <script type="text/javascript" src="js/angular-loading-bar.min.js"></script>
  <script type="text/javascript" src="js/roamingPortal.js?v=2"></script>
  <link rel="stylesheet" href="../material-icon.css">
  <link rel="stylesheet" href="../material.teal-blue.min.css">
  <script defer type="text/javascript" src="../material.min.js"></script>
  <title>VINCI - Samu Social de Grenoble</title>
</head>
<body>
  <script type="text/javascript" src="js/mcx-dialog.js"></script>
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer" ng-controller="MainCtrl as main">
  <header class="mdl-layout__header">
    <div class="mdl-layout__header-row">
      <span class="mdl-layout-title mdl-layout--small-screen-only">{{main.route.current.shortTitle}}</span>
      <span class="mdl-layout-title mdl-layout--large-screen-only">{{main.route.current.longTitle}}</span>
      <div class="mdl-layout-spacer"></div>
      <div id="user-menu-button" ng-show="main.sessionInfo.loggedIn">
        <span>{{main.sessionInfo.user.username}}</span>
        <button class="mdl-button mdl-js-button mdl-button--icon">
           <i class="material-icons">person</i>
        </button>
      </div>
      <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="user-menu-button">
        <li class="mdl-menu__item" ng-click="main.setPassword()" ng-show="main.hasP('P_CHANGE_PASSWORD')">Changer de mot de passe</li>
        <li class="mdl-menu__item" ng-click="main.logout()" ng-show="main.hasP('P_LOG_OUT')">Se déconnecter</li>
      </ul>
    </div>
  </header>
  <div class="mdl-layout__drawer">
    <span class="mdl-layout-title">Navigation</span>
    <nav class="mdl-navigation">
      <a class="mdl-navigation__link" href="/"><i class="material-icons">home</i> Accueil</a>
      <a class="mdl-navigation__link" href="/portal/#!/roamingsList/"><i class="material-icons">assignment</i> Compte-rendus</a>
      <a class="mdl-navigation__link" href="/redirectionPlanning.php"><i class="material-icons">date_range</i> Planning maraudes</a>
      <a class="mdl-navigation__link" href="/meetings.php"><i class="material-icons">event</i> Calendrier réunions</a>
      <a class="mdl-navigation__link" href="/dokuwiki/"><i class="material-icons">school</i> Base connaissances</a>
      <a class="mdl-navigation__link" ng-href="/portal/#!/users"><i class="material-icons">contacts </i> Liste des membres</a>
      <a class="mdl-navigation__link" href="https://samu-social-grenoble.fr"><i class="material-icons">web</i> Site Web</a>
      <a class="mdl-navigation__link" href="https://www.facebook.com/SamuSocialGrenoble"><i class="material-icons">face</i> Page Facebook</a>
      <a class="mdl-navigation__link" href="/contactForm.php"><i class="material-icons">live_help</i> Aide</a>
    </nav>
  </div>
  <main class="mdl-layout__content"><div class="main-content">
    <div id="roamingPortalApp">
      <div ng-view></div>
    </div>
    <div>&nbsp;</div>
  </div></main>
</div>
</body>
</html>
