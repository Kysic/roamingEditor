<?php

require_once('lib/auth.php');
require_once('lib/json.php');
require_once('lib/googlePlanning.php');

try {

    checkLoggedIn();
    checkHasPermission(P_SEE_PLANNING);

    validateRoamingDate(@$_GET['roamingDate']);
    $roamingDate = DateTime::createFromFormat('Y-m-d', $_GET['roamingDate'])->getTimestamp();

    $roamingMonthData = extractRoamingsOfMonth($roamingDate);
    $roamingData = getRoamingOfDate($roamingMonthData, $roamingDate);

    $volunteers = array();
    for ($i = 1; $i <= 3; $i++) {
        if (trim($roamingData[$i]['name']) != '') {
            array_push($volunteers, $roamingData[$i]['name']);
        }
    }
    $response = array('tutor' => trim($roamingData[0]['name']), 'volunteers' => $volunteers);

    returnResult($response);

} catch (Exception $e) {
    returnError($e);
}

?>
