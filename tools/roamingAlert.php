#!/usr/bin/php

<?php

require_once('/var/www/vinci/api/lib/Container.php');

try {

    $container = new Container();
    $googlePlanning = $container->getGooglePlanning();

    $today = new DateTime();
    $planningToday = $googlePlanning->getRoamingOfDate($today->getTimestamp());

    if ($planningToday['status'] == 'unsure') {
        $emails = array(SECRETARIAT_EMAIL);
        $missing = empty(@$planningToday['tutor']) ? 'un tuteur' : 'des bénévoles';
        foreach ($emails as $email) {
            $container->getMail()->sendMail(
                $email,
                '[VINCI] Alerte, maraude du '.$today->format('d/m/Y').' incomplète',
                'Il manque '.$missing.' pour la maraude du '.$today->format('d/m/Y')
            );
        }
    }

} catch (Exception $e) {
    $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Error detected while checking roaming status',
        'An error has occured: '."\n".print_r($e, true)
    );
    throw $e;
}

