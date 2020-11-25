#!/usr/bin/php

<?php

require_once('/var/www/vinci/api/lib/Container.php');

try {

  $container = new Container();

  $sender = getenv("SENDER");
  $recipient = getenv("RECIPIENT");

  $container->getReporting115()->extractFromMailStdin();

} catch (Exception $e) {
  $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Error detected on signalements reception',
    'An error has occured while parsing the signalement file:'."\n".print_r($e, true)
  );
  throw $e;
}

?>
