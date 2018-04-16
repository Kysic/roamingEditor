<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $roamingsStorage = $container->getRoamingsStorage();
    $spreadsheetsGenerator = $container->getSpreadsheetsGenerator();
    $stats = $container->getStats();

    $session->checkLoggedIn();
    $session->checkHasPermission(P_GEN_STATS);

     if ( !empty($_GET['from']) ) {
        $validator->validateRoamingDate(@$_GET['from']);
        $fromDate = DateTime::createFromFormat('Y-m-d', $_GET['from']);
    } else {
        $fromDate = new DateTime();
        $fromDate->sub(new DateInterval('P10D'));
    }
    if ( !empty($_GET['to']) ) {
        $validator->validateRoamingDate(@$_GET['to']);
        $toDate = DateTime::createFromFormat('Y-m-d', $_GET['to']);
    } else {
        $toDate = new DateTime();
    }

    $roamingsDocs = $roamingsStorage->getDocsIds($fromDate->format('Y-m-d'), $toDate->format('Y-m-d'));

    $roamingsStats = $stats->extractStatsFromRoamingsReports($roamingsDocs);

    header('Content-Disposition: attachment; filename="stats-'.$fromDate->format('Y-m-d').'-'.$toDate->format('Y-m-d').'.csv"');
    header('Content-type: text/csv');
    echo $stats->csv_stats($roamingsStats);

} catch (Exception $e) {
    echo '<b>Error</b><br/><pre>'.$e.'</pre>';
}
