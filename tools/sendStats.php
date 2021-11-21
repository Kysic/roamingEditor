#!/usr/bin/php
<?php

require_once('/var/www/vinci/api/lib/Container.php');

try {

    $container = new Container();
    $roamingsStorage = $container->getRoamingsStorage();
    $stats = $container->getStats();


    $fromDate = new DateTime('first day of last month');

    $toDate = new DateTime('last day of last month');

    $roamingsDocs = $roamingsStorage->getDocsIds($fromDate->format('Y-m-d'), $toDate->format('Y-m-d'));

    $roamingsStats = $stats->extractStatsFromRoamingsReports($roamingsDocs);

    $body = '
        <style type="text/css">
            table {
                border-collapse: collapse;
            }
            table, th, td {
                border: 1px solid #808080;
            }
            th, td {
                text-align: center;
                padding: 2px 5px;
            }
        </style>
        ';
    $body .= $stats->html_stats($roamingsStats);

    $emails = array(ADMIN_EMAIL, PRESIDENT_EMAIL);

    foreach ($emails as $email) {
        $container->getMail()->sendMail(
            $email,
            '[AMICI] Statistiques du '.$fromDate->format('d-m-Y').' au '.$toDate->format('d-m-Y'),
            $body,
            false,
            true
        );
    }

} catch (Exception $e) {
    $container->getMail()->sendMail(ADMIN_EMAIL, '[AMICI] Error detected while sending stats',
        'An error has occured while generating monthly stats : '."\n".print_r($e, true)
    );
    throw $e;
}
