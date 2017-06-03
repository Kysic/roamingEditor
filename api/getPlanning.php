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

    $validator->validateRoamingDate(@$_GET['roamingDate']);
    $roamingDate = DateTime::createFromFormat('Y-m-d', $_GET['roamingDate'])->getTimestamp();

    $roamingMonthData = $googlePlanning->extractRoamingsOfMonth($roamingDate);
    $roamingData = $googlePlanning->getRoamingOfDate($roamingMonthData, $roamingDate);

    $volunteers = array();
    for ($i = 1; $i <= 3; $i++) {
        if (trim($roamingData[$i]['name']) != '') {
            array_push($volunteers, $roamingData[$i]['name']);
        }
    }
    $response = array('tutor' => trim($roamingData[0]['name']), 'volunteers' => $volunteers);

    $json->returnResult($response);

} catch (Exception $e) {
    $json->returnError($e);
}

