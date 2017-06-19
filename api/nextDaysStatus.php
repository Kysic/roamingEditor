<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $googlePlanning = $container->getGooglePlanning();
    $json = $container->getJson();

    $fromDate = new DateTime();
    $toDate = new DateTime();
    $toDate->add(new DateInterval('P4D'));

    $response = array();
    $periodInterval = new DateInterval('P1D');
    $period = new DatePeriod( $fromDate, $periodInterval, $toDate );
    foreach($period as $date) {
        $planning = $googlePlanning->getRoamingOfDate($date->getTimestamp());
        unset($planning['tutor']);
        unset($planning['teammates']);
        $response[$date->format('Y-m-d')] = $planning;
    }
    // Allow cross domain request
    header('Access-Control-Allow-Origin: *');
    $json->returnResult($response);

} catch (Exception $e) {
    $json->returnError($e);
}
