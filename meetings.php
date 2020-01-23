<?php
require_once('api/lib/Container.php');

$container = new Container();
$session = $container->getSession();
$error = '';
try {
    if (!$session->isLoggedIn()) {
        $newLocation = '/portal/#!/login//site:'.basename(__FILE__);
        header('Location: '.$newLocation);
        echo '<script type="text/javascript">document.location="'.$newLocation.'";</script>';
        echo 'Redirection en cours';
        exit(0);
    } else {
        $session->checkHasPermission(P_SEE_MEETING);
        $calendarURL = CALENDAR_PROVIDER_URL.CALENDAR_ID;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!doctype html>
<html lang="fr">
<!-- Roaming Editor - License GNU GPL - https://github.com/Kysic/roamingEditor -->
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>VINCI - Calendrier des réunions</title>
  <link rel="stylesheet" href="material-icon.css">
  <link rel="stylesheet" href="material.teal-blue.min.css">
  <link rel="icon" type="image/png" href="img/favicon.png">
  <script defer src="material.min.js" type="text/javascript"></script>
<style>
.mdl-layout {
  min-width: 350px;
}
.main-content {
  display: flex;
  flex-flow: row wrap;
  padding: 20px;
  justify-content: center;
}
.mdl-menu__item a {
  font-weight: unset;
  text-decoration: none;
  color: unset;
}
.mdl-card {
  width: 500px;
  padding: 20px;
  min-height: unset;
}
.mdl-card__supporting-text {
  padding: 0 15px 10px 15px;
  width: unset;
}
.calendar-container {
  position: absolute;
  top: 20px;
  left: 20px;
  bottom: 20px;
  right: 20px;
}
.calendar-iframe {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border: 0;
}
.notification {
  font-weight: bold;
  display: flex;
  align-items: center;
  justify-content: center;
}
.notification i.material-icons {
  margin-right: 10px;
  padding-bottom: 5px;
}
.error {
  color: #f44;
}
</style>
<script type="text/javascript">
function post(path, params, method='post') {
  const form = document.createElement('form');
  form.method = method;
  form.action = path;
  for (const key in params) {
    if (params.hasOwnProperty(key)) {
      const hiddenField = document.createElement('input');
      hiddenField.type = 'hidden';
      hiddenField.name = key;
      hiddenField.value = params[key];
      form.appendChild(hiddenField);
    }
  }
  document.body.appendChild(form);
  form.submit();
}
function logout() {
  post('/', { action: 'logout', sessionToken: '<?php echo $session->getToken();?>' });
}
</script>
</head>
<body>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
  <header class="mdl-layout__header">
    <div class="mdl-layout__header-row">
      <span class="mdl-layout-title mdl-layout--small-screen-only">Réunions</span>
      <span class="mdl-layout-title mdl-layout--large-screen-only">Calendrier des réunions et évènements du VINCI</span>
      <div class="mdl-layout-spacer"></div>
      <div id="user-menu-button">
        <span><?php echo $session->getUser()->username;?></span>
        <button class="mdl-button mdl-js-button mdl-button--icon">
           <i class="material-icons">person</i>
        </button>
      </div>
      <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="user-menu-button">
        <li class="mdl-menu__item"><a href="/portal/#!/setPassword">Changer de mot de passe</a></li>
        <li class="mdl-menu__item" onclick="logout()">Se déconnecter</li>
      </ul>
    </div>
  </header>
  <div class="mdl-layout__drawer">
    <span class="mdl-layout-title">Navigation</span>
    <nav class="mdl-navigation">
      <a class="mdl-navigation__link" href="/"><i class="material-icons">home</i> Accueil</a>
      <a class="mdl-navigation__link" href="/portal/#!/roamingsList/"><i class="material-icons">assignment</i> Compte-rendus</a>
      <a class="mdl-navigation__link" href="/redirectionPlanning.php"><i class="material-icons">date_range</i> Planning maraudes</a>
      <a class="mdl-navigation__link" href="/dokuwiki/"><i class="material-icons">school</i> Base connaissances</a>
      <a class="mdl-navigation__link" href="/portal/#!/users"><i class="material-icons">contacts </i> Liste des membres</a>
      <a class="mdl-navigation__link" href="https://samu-social-grenoble.fr"><i class="material-icons">web</i> Site Web</a>
      <a class="mdl-navigation__link" href="https://www.facebook.com/SamuSocialGrenoble"><i class="material-icons">face</i> Page Facebook</a>
      <a class="mdl-navigation__link" href="/contactForm.php"><i class="material-icons">live_help</i> Aide</a>
    </nav>
  </div>
  <main class="mdl-layout__content"><div class="main-content">

<?php
if ($error) {
?>
    <div class="mdl-card mdl-shadow--2dp">
      <div class="mdl-card__supporting-text notification error">
         <i class="material-icons">error</i> <?php echo $error; ?>
      </div>
    </div>
<?php
} else {
?>
    <div class="calendar-container">
        <iframe class="calendar-iframe" src="<?php echo $calendarURL; ?>&amp;mode=AGENDA&amp;showTitle=0" frameborder="0"
            scrolling="no"></iframe>
    </div>
<?php
}
?>

  </div></main>
</div>

</body>
</html>
