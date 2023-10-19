<?php
$formError = '';
$mailSent = false;
$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
if (!empty($email) && !empty($message)) {
  try {
    require_once('api/lib/Container.php');
    $container = new Container();
    $container->getMail()->sendMail(ADMIN_EMAIL, '[AMICI] Demande d\'assistance',
      'Emetteur: '.$email."\r\n".
      'IP: '.$_SERVER['REMOTE_ADDR']."\r\n".
      'Navigateur: '.$_SERVER['HTTP_USER_AGENT']."\r\n".
      'Javascript: '.($_POST['javascript'] === 'true' ? 'true' : 'false')."\r\n".
      'Cookies: '.($_COOKIE['cookiesEnabled'] === 'true' ? 'true' : 'false')."\r\n".
      'Message:'."\r\n".$message
    );
    $mailSent = true;
  } catch (Exception $e) {
    $formError = 'Une erreur est survenue lors de l\'envoi de votre message.\nMerci d\'envoyer un mail à '.ADMIN_EMAIL;
  }
} else if (isset($_POST['email']) || isset($_POST['message'])) {
  $formError = 'Merci de renseigner votre adresse email et le message à envoyer.';
} else {
  setcookie('cookiesEnabled', 'true');
}
?>
<!doctype html>
<html lang="fr">
<!-- Roaming Editor - License GNU GPL - https://github.com/Kysic/roamingEditor -->
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AMICI - Demande d'assistance</title>
  <link rel="stylesheet" href="material.teal-blue.min.css">
  <link rel="icon" type="image/svg+xml" sizes="any" href="/favicon.svg"/>
  <link rel="alternate icon" type="image/png" sizes="512x512" href="/favicon.png">
  <link rel="alternate icon" type="image/x-icon" href="/favicon.ico">
  <link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
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
.mdl-card {
  width: 700px;
  margin: 15px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}
.mdl-card__title-text i.material-icons {
  color: #555;
  margin-right: 10px;
}
.mdl-card__supporting-text {
  padding: 0 15px 10px 15px;
  width: unset;
}
.mdl-card__supporting-text form {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
}
.mdl-card__supporting-text form > div {
  width: 500px;
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
.success {
  color: #2a6;
}
.error {
  color: #f44;
}
</style>
</head>
<body>

<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header mdl-layout--fixed-drawer">
  <header class="mdl-layout__header">
    <div class="mdl-layout__header-row">
      <span class="mdl-layout-title mdl-layout--small-screen-only">Aide</span>
      <span class="mdl-layout-title mdl-layout--large-screen-only">
        Demande d'assistance sur l'utilisation du site
      </span>
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
      <a class="mdl-navigation__link" href="/portal/#!/users"><i class="material-icons">contacts </i> Liste des membres</a>
      <a class="mdl-navigation__link" href="https://samu-social-grenoble.fr"><i class="material-icons">web</i> Site Web</a>
      <a class="mdl-navigation__link" href="https://www.facebook.com/AMICISamuSocialGrenoble"><i class="material-icons">face</i> Page Facebook</a>
      <a class="mdl-navigation__link" href="/contactForm.php"><i class="material-icons">live_help</i> Aide</a>
    </nav>
  </div>
  <main class="mdl-layout__content"><div class="main-content">

    <div class="mdl-card mdl-shadow--2dp">
      <div class="mdl-card__title">
        <h2 class="mdl-card__title-text"><i class="material-icons">live_help</i> Formulaire de demande d'assistance</h2>
      </div>
      <?php
if ($mailSent) {
?>
      <div class="mdl-card__supporting-text notification success">
         <i class="material-icons">check_circle</i> Votre message a bien été envoyé.
         Nous vous recontacterons dès que possible.
      </div>
<?php
} else {
?>
      <div class="mdl-card__supporting-text">
         Si vous rencontrez un problème dans l'utilisation du site, vous pouvez utiliser le formulaire suivant pour nous contacter et que l'on puisse résoudre cela ensemble.
      </div>
<?php
  if ($formError) {
?>
      <div class="mdl-card__supporting-text notification error">
         <i class="material-icons">error</i> <?php echo $formError; ?>
      </div>
<?php
  }
?>
      <div class="mdl-card__supporting-text">
        <form method="post">
          <input id='checkJavascript' name='javascript' type='hidden' value='false' />
          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <input class="mdl-textfield__input" type="text" id="email" name="email"
              pattern="[a-zA-Z0-9.!#$%&amp;’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)+" required>
            <label class="mdl-textfield__label" for="email">Votre adresse email</label>
          </div>

          <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
            <textarea class="mdl-textfield__input" type="text" rows= "8" id="message" name="message" required></textarea>
            <label class="mdl-textfield__label" for="message">Le problème rencontré (n'hésitez pas à détailler)</label>
          </div>

          <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored" type="submit">Envoyer</button>

        </form>
      </div>
      <script type="text/javascript">document.getElementById('checkJavascript').value = 'true';</script>
<?php
}
?>
      <div class="mdl-card__actions mdl-card--border">
        <a class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" href="/">
          Retour à l'accueil
        </a>
      </div>
    </div>


  </div></main>
</div>

</body>
</html>
