<?php

require_once('lib/Container.php');

function docIdToCsvUrl($docId) {
    return GOOGLE_DOC_URL.$docId.GOOGLE_DOC_CMD_CSV;
}

function extractStatsFromRoamingReport($docId) {
    $reportCsvUrl = docIdToCsvUrl($docId);
    $csv = array_map('str_getcsv', file($reportCsvUrl));
    return array(
        'date' => $csv[0][2],
        'nbVolunteer' => substr_count($csv[2][2], ',') + 1,
        'nbIntervention' => $csv[5][2],
        'nbAdult' => $csv[7][2],
        'nbChild' => $csv[8][2],
        'nbEncounter' => $csv[6][2],
        'nbBlanket' => $csv[9][2],
        'nbTent' => $csv[10][2]
    );
}

function extractStatsFromRoamingsReports($roamingsDocs) {
    $roamingsStats = array();
    foreach ($roamingsDocs as $roamingDoc) {
        $roamingStats = extractStatsFromRoamingReport($roamingDoc->docId);
        $roamingStats['date'] = $roamingDoc->roamingDate;
        array_push($roamingsStats, $roamingStats);
    }
    return $roamingsStats;
}

function print_stat_csv($stats) {
    echo $stats['date'].';'.$stats['nbVolunteer'].';'.$stats['nbIntervention'].';'.$stats['nbAdult'].';'
         .$stats['nbChild'].';'.$stats['nbEncounter'].';'.$stats['nbBlanket'].';'.$stats['nbTent'].';'."\n";
}

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $roamingsStorage = $container->getRoamingsStorage();
    $spreadsheetsGenerator = $container->getSpreadsheetsGenerator();

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

    $roamingsStats = extractStatsFromRoamingsReports($roamingsDocs);

    $headers = array(
        'date' => 'Jour',
        'nbVolunteer' =>'Nombre de bénévoles',
        'nbIntervention' => 'Nombre d\'interventions',
        'nbAdult' => 'Nombres d\'adultes',
        'nbChild' => 'Nombres d\'enfants',
        'nbEncounter' => 'Total personnes rencontrées',
        'nbBlanket' => 'Nombre de couvertures',
        'nbTent' => 'Nombres de tentes'
    );
    header('Content-Disposition: attachment; filename="stats-'.$_GET['from'].'-'.$_GET['to'].'.csv"');
    header('Content-type: text/csv');
    print_stat_csv($headers);
    array_map('print_stat_csv', $roamingsStats);

} catch (Exception $e) {
    echo '<b>Error</b><br/><pre>'.$e.'</pre>';
}
