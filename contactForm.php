<?php
$formError = '';
$mailSent = false;
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$message = filter_var($_POST['message'], FILTER_SANITIZE_STRING);
if (!empty($email) && !empty($message))  {
  try {
    require_once('api/lib/Container.php');
    $container = new Container();
    $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Demande d\'assistance',
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
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" href="img/favicon.png" />
<title>Demande d'assistance</title>
<style>
body {
  text-align: center;
  color: #d9dadc;
  background: #3d4045;
  padding: 20px;
}
a {
  color: #d9dadc;
  font-weight: bold;
  text-decoration: underline;
}
a:hover {
  color: #fff;
}
.error {
  color: #f44;
  font-weight: bold;
}
.info {
  text-align: center;
  max-width: 800px;
  margin: auto;
}
.form {
  margin: 20px;
}
.fieldGroup {
  padding: 10px;
}
#message {
  max-width: 800px;
  width: 90%;
  height: 300px;
}
.success {
  color: #5d5;
  font-weight: bold;
}
</style>
</head>
<body>
  <h1>Formulaire de demande d'assistance sur l'utilisation du site</h1>
  <p>
<?php
if ($mailSent) {
?>
    <div class="success info">
      Votre message a bien été envoyé. Nous vous recontacterons dès que possible.
    </div>
<?php
} else {
?>
    <div class="info">
      Si vous rencontrez un problème dans l'utilisation du site, n'hésitez pas à utiliser le formulaire
      suivant pour nous contacter et que l'on puisse résoudre cela ensemble.
    </div>
    <form class="form" method="post">
      <input id='checkJavascript' name='javascript' type='hidden' value='false' />
      <div class='error'><?php echo $formError; ?></div>
      <div class="fieldGroup">
        <label for='email'>Email</label>
        <input id='email' type='text' name='email' placeholder="Votre adresse email" value="<?php echo $email; ?>"
            title="Merci de renseiger votre adresse email pour que l'on puisse vous répondre" />
      </div>
      <div class="fieldGroup">
        <label for="message">Votre message (n'hésitez pas à donner le plus de détails possibles sur le problème)</label><br>
        <textarea id='message' name='message'
        title="Quel est le problème rencontré ? A quel moment ? Que voyez vous à l'écran à ce moment là ? Est-ce pareil avec un autre navigateur ?"
        ><?php echo $message; ?></textarea>
      </div>
      <div class="fieldGroup">
        <input type="submit" value="Envoyer" />
      </div>
    </form>
    <script type="text/javascript">document.getElementById('checkJavascript').value = 'true';</script>
<?php
}
?>
  </p>
  <p>
    <a href="/">Retour à l'accueil</a>
  </p>
</body>
</html>
