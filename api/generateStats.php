<?php

require_once('lib/Container.php');

define('IDX_SRC_INTERVENTION', 4);
define('IDX_NB_ADULTS', 5);
define('IDX_NB_CHILDREN', 6);

function docIdToCsvUrl($docId) {
    return GOOGLE_DOC_URL.$docId.GOOGLE_DOC_CMD_CSV;
}

function getNbVolunteers($volunteers) {
    $str = preg_replace('/;|([\s,;]et[\s,;])/', ',', $volunteers);
    $str = preg_replace('/,[\s,]*/', ',', $str);
    return substr_count($str, ',') + 1;
}

function extractStatsFromRoamingReport($docId) {
    $reportCsvUrl = docIdToCsvUrl($docId);
    $csv = array_map('str_getcsv', file($reportCsvUrl));
    $srcInterventions = extractSrcIntervention($csv);
    return array(
        'date' => $csv[0][2],
        'nbVolunteer' => getNbVolunteers($csv[2][2]),
        'nbIntervention' => $csv[5][2],
        'nbAdult' => $csv[7][2],
        'nbChild' => $csv[8][2],
        'nbEncounter' => $csv[6][2],
        'nbBlanket' => $csv[9][2],
        'nbTent' => $csv[10][2],
        'src115' => $srcInterventions['115'],
        'srcRoaming' => $srcInterventions['Maraude']
    );
}

function extractSrcIntervention($csv) {
    $srcInterventions = array();
    for ($i = 18; $i < count($csv); $i++) {
        if (count($csv[$i]) > 6) {
            if ($csv[$i][IDX_NB_ADULTS] > 0 || $csv[$i][IDX_NB_CHILDREN] > 0) {
                $srcIntervention = $csv[$i][IDX_SRC_INTERVENTION];
                if (array_key_exists($srcIntervention, $srcInterventions)) {
                    $srcInterventions[$srcIntervention]++;
                } else {
                    $srcInterventions[$srcIntervention] = 1;
                }
            }
        }
    }
    return $srcInterventions;
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
         .$stats['nbChild'].';'.$stats['nbEncounter'].';'.$stats['nbBlanket'].';'.$stats['nbTent'].';'
         .$stats['src115'].';'.$stats['srcRoaming'].';'."\n";
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
        'nbTent' => 'Nombres de tentes',
        'src115' => 'Signalement 115',
        'srcRoaming' => 'Rencontre Maraude'
    );
    header('Content-Disposition: attachment; filename="stats-'.$fromDate->format('Y-m-d').'-'.$toDate->format('Y-m-d').'.csv"');
    header('Content-type: text/csv');
    print_stat_csv($headers);
    array_map('print_stat_csv', $roamingsStats);

} catch (Exception $e) {
    echo '<b>Error</b><br/><pre>'.$e.'</pre>';
}
