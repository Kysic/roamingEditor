<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $googlePlanning = $container->getGooglePlanning();
    $json = $container->getJson();

    $session->checkLoggedIn();
    $session->checkHasPermission(P_SEE_PLANNING);

    if ( !empty($_GET['roamingDate']) ) {
        $validator->validateRoamingDate(@$_GET['roamingDate']);
        $roamingDate = DateTime::createFromFormat('Y-m-d', $_GET['roamingDate']);
        $response = $googlePlanning->getRoamingOfDate($roamingDate->getTimestamp());
    } else {
        if ( !empty($_GET['from']) ) {
            $validator->validateRoamingDate(@$_GET['from']);
            $fromDate = DateTime::createFromFormat('Y-m-d', $_GET['from']);
        } else {
            $fromDate = new DateTime('first day of this month');
        }
        if ( !empty($_GET['to']) ) {
            $validator->validateRoamingDate(@$_GET['to']);
            $toDate = DateTime::createFromFormat('Y-m-d', $_GET['to']);
        } else {
            $toDate = new DateTime('last day of this month');
        }
        $response = array();
        $periodInterval = new DateInterval('P1D');
        $toDate->add($periodInterval);
        $period = new DatePeriod( $fromDate, $periodInterval, $toDate );
        foreach($period as $date) {
            $response[$date->format("Y-m-d")] = $googlePlanning->getRoamingOfDate($date->getTimestamp());
        }
    }
    $json->returnResult($response);

} catch (Exception $e) {
    $json->returnError($e);
}

