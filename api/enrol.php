<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $googlePlanning = $container->getGooglePlanning();
    $json = $container->getJson();

    $session->checkLoggedIn();
    $session->checkHasPermission(P_ENROL);

    $json->mergeJsonParameterToPost();

    $session->checkToken(@$_POST['sessionToken']);

    $roamingDateStr = @$_POST['roamingDate'];
    $validator->validateRoamingDate($roamingDateStr);
    $roamingDate = DateTime::createFromFormat('Y-m-d', $roamingDateStr);

    $position = @$_POST['position'];
    $validator->validateEnrolPosition($position);

    $action = @$_POST['action'];
    $validator->validateEnrolAction($action);

    $response = $googlePlanning->enrol($roamingDate->getTimestamp(), $position, $session->getUser(), $action);

    $json->returnResult($response);

} catch (Exception $e) {
    $json->returnError($e);
}
