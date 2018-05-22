<?php

$formError = '';
$mailSent = false;
$names = filter_var(@$_POST['names'], FILTER_SANITIZE_STRING);
$date = filter_var(@$_POST['date'], FILTER_SANITIZE_STRING);
$place = filter_var(@$_POST['place'], FILTER_SANITIZE_STRING);
$phoneNumber = filter_var(@$_POST['phoneNumber'], FILTER_SANITIZE_STRING);
$language = filter_var(@$_POST['language'], FILTER_SANITIZE_STRING);
$constitution = filter_var(@$_POST['constitution'], FILTER_SANITIZE_STRING);
$observations = filter_var(@$_POST['observations'], FILTER_SANITIZE_STRING);
$author = filter_var(@$_POST['author'], FILTER_SANITIZE_STRING);
try {
    require_once('api/lib/Container.php');
    $container = new Container();
    $session = $container->getSession();
    if (!$session->isLoggedIn()) {
        $newLocation = '/portal/#!/login//site:'.basename(__FILE__);
        header('Location: '.$newLocation);
        echo '<script type="text/javascript">document.location="'.$newLocation.'";</script>';
        echo 'Redirection en cours';
        exit;
    }
    if (!empty($names) && !empty($phoneNumber))  {
        try {
            require_once('api/lib/Container.php');
            $container = new Container();
            $emails = array(SECRETARIAT_EMAIL, $session->getUser()->email);
            foreach ($emails as $email) {
                $container->getMail()->sendMail($email, '[VINCI] Fiche de signalement '.$names,
                  'Signalé par '.$author.' identifié en tant que '.$session->getUser()->firstname.' '.$session->getUser()->lastname."\r\n\r\n".
                  '         Nom des personnes : '.$names."\r\n".
                  '      Date de la rencontre : '.$date."\r\n".
                  '       Lieu de la recontre : '.$place."\r\n".
                  '       Numéro de téléphone : '.$phoneNumber."\r\n".
                  '             Langue parlée : '.$language."\r\n".
                  'Constitution de la famille : '.$constitution."\r\n".
                  'Observations complémentaires :'."\r\n".$observations
                );
            }
            $mailSent = true;
        } catch (Exception $e) {
            $formError = 'Une erreur est survenue lors de l\'envoi de votre message.\nMerci d\'envoyer un mail à '.ADMIN_EMAIL;
        }
    }
    if (empty($date)) {
        $meetingDate = new DateTime();
        // If it's not yet 20h, the roaming hasn't start so we consider that the report
        // concerns someone meet in yersteday roaming
        if ($meetingDate->format('H') < 20) {
            $meetingDate->sub(new DateInterval('P1D'));
        }
        $date = $meetingDate->format('d/m/Y');
    }
} catch (Exception $e) {
    $formError = $e->getMessage();
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" href="img/favicon.png" />
<link rel="stylesheet" href="css/main.css"/>
<title>Faire un signalement</title>
<style>
.info {
  text-align: center;
  max-width: 800px;
  margin: auto;
}
.reportForm {
  text-align: center;
  max-width: 800px;
  margin: 20px auto;
}
.fieldGroup {
  padding: 2px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.fieldGroup label {
  flex: 1 0 120px;
  text-align: right;
  margin-right: 15px;
}
.fieldGroup input, .fieldGroup textarea {
  flex: 1 0 300px;
  text-align: left;
}
#observations {
  height: 120px;
}
.fieldGroup input[type=submit] {
  margin: auto;
  flex: 0 1 200px;
  text-align: center;
}
</style>
</head>
<body>
  <h1>Formulaire de signalement</h1>
<?php
if ($mailSent) {
?>
  <p>
    <div class="success info">
      Votre signalement a bien été envoyé, merci.
    </div>
  </p>
  <p>
    <a href="">Faire un autre signalement</a>
  </p>
<?php
} else {
?>
  <p>
    <div class="info">
      Formulaire de signalement de personnes en difficultées à destination du secrétariat de l'association.
    </div>
    <form class="reportForm" method="post">
      <input id='checkJavascript' name='javascript' type='hidden' value='false' />
      <div class='error'><?php echo $formError; ?></div>
      <div class="fieldGroup">
        <label for='names'>Personnes rencontrées</label>
        <input id='names' name='names' type='text' required='true'
            placeholder="Nom et prénom des personnes rencontrées"
            title="Nom et prénom des personnes rencontrées"
            value="<?php echo $names; ?>" />
      </div>
      <div class="fieldGroup">
        <label for='phoneNumber'>Téléphone</label>
        <input id='phoneNumber' name='phoneNumber' type='text' required='true'
            placeholder="Numéro de téléphone des personnes ou d'un contact"
            title="Numéro de téléphone des personnes ou d'un contact"
            value="<?php echo $phoneNumber; ?>" />
      </div>
      <div class="fieldGroup">
        <label for='date'>Date</label>
        <input id='date' name='date' type='text'
            placeholder="Date de la rencontre"
            title="Date de la rencontre"
            value="<?php echo $date; ?>" />
      </div>
      <div class="fieldGroup">
        <label for='place'>Lieu</label>
        <input id='place' name='place' type='text'
            placeholder="Lieu de la rencontre"
            title="Lieu de la rencontre"
            value="<?php echo $place; ?>" />
      </div>
      <div class="fieldGroup">
        <label for='language'>Langue</label>
        <input id='language' name='language' type='text'
            placeholder="Langue parlée"
            title="Langue parlée"
            value="<?php echo $language; ?>" />
      </div>
      <div class="fieldGroup">
        <label for='constitution'>Constitution</label>
        <input id='constitution' name='constitution' type='text'
            placeholder="Consitution de la famille"
            title="Consitution de la famille"
            value="<?php echo $constitution; ?>" />
      </div>
      <div class="fieldGroup">
        <label for='author'>Auteur</label>
        <input id='author' name='author' type='text'
            placeholder="Auteur de la fiche"
            title="Auteur de la fiche"
            value="<?php echo $author; ?>" />
      </div>
      <div class="fieldGroup">
        <label for="observations">Observations</label>
        <textarea id='observations' name='observations'
            title="Observations complémentaires"><?php echo $observations; ?></textarea>
      </div>
      <div class="fieldGroup">
        <input type="submit" value="Envoyer" />
      </div>
    </form>
  </p>
<?php
}
?>
  <p>
    <a href="/">Retour à l'accueil</a>
  </p>
</body>
</html>
