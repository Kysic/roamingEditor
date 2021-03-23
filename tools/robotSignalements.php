#!/usr/bin/php

<?php

require_once('/var/www/vinci/api/lib/Container.php');

try {

  $container = new Container();

  $sender = getenv('SENDER');
  $recipient = getenv('RECIPIENT');

  list($from, $subject) = $container->getReporting115()->extractFromMailStdin();

  $reports = $container->getReportsStorage()->getTodaysLast();

  $container->getMail()->sendMail(
    $from,
    'Re: '.$subject,
    getAcknowledgeBody(json_decode($reports)),
    NOREPLY_EMAIL,
  );

} catch (Exception $e) {
  $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Error detected on signalements reception',
    'An error has occured while parsing the signalement file:'."\n".print_r($e, true)
  );
  throw $e;
}

function getAcknowledgeBody($reports) {
  $data = array_map('reportToText', $reports);
  $data = implode($data, "\n");
  $adminEmail = ADMIN_EMAIL;
  return <<<EOD
Bonsoir,

Ce message est envoyé automatiquement pour confirmer que les signalements ont été traités par notre automate et sont maintenant disponibles pour être récupérés depuis l'application utilisée par l'association VINCI pour gérer ses comptes rendus de maraude.

Si l'équipe de ce soir ne vous contacte pas en début de maraude ou que vous transmettez de nouveaux signalements au cours de la soirée, n'hésitez pas à joindre l'équipe de maraude par téléphone pour s'assurer qu'elle a bien vu qu'il y avait de nouveaux signalements et pu les récupérer.

A titre d'information, voici les données qui ont été extraites de votre email et sont désormais accessibles à l'équipe de maraude :

$data

Merci à vous pour les signalements, nous vous souhaitons une bonne soirée,

Bien cordialement,

--------------------------------------
Cet email est un message automatique, veuillez ne pas y répondre directement, votre message ne sera pas transmis à l'équipe de maraude.
Ce message a été mis en place en espérant qu'il pourrait vous être utile pour vous assurez que votre message a pu être traité correctement.
S'il vous est inutile et que vous préferez ne pas le recevoir, n'hésitez pas à nous contacter à l'adresse email $adminEmail pour le signaler.
EOD;
}

function reportToText($report) {
  return "- $report->prenom $report->nom, 👪 $report->compo, ☎ $report->telephone, 🚩 $report->lieu, 🙋 $report->besoins";
}