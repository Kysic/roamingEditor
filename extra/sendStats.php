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

    $container->getMail()->sendMail(
        ADMIN_EMAIL,
        '[VINCI] Statistiques du '.$fromDate->format('d-m-Y').' au '.$toDate->format('d-m-Y'),
        $stats->csv_stats($roamingsStats, true)
    );

    echo 'done'."\n";

} catch (Exception $e) {
    $container->getMail()->sendMail(ADMIN_EMAIL, '[VINCI] Error detected while sending stats',
        'An error has occured while generating monthly stats : '."\n".print_r($e, true)
    );
    throw $e;
}

