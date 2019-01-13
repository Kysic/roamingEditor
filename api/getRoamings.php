<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $json = $container->getJson();
    $roamingsStorage = $container->getRoamingsStorage();

    $session->closeWrite();
    $session->checkLoggedIn();
    $session->checkHasPermission(P_SEE_LAST_REPORT);

    if ( !empty($_GET['from']) ) {
        $validator->validateRoamingDate(@$_GET['from']);
        $fromDate = DateTime::createFromFormat('Y-m-d', $_GET['from']);
    } else {
        $fromDate = new DateTime();
        $fromDate->sub(new DateInterval('P'.REPORT_OLD_LIMIT_DAYS.'D'));
    }
    if ( !empty($_GET['to']) ) {
        $validator->validateRoamingDate(@$_GET['to']);
        $toDate = DateTime::createFromFormat('Y-m-d', $_GET['to']);
    } else {
        $toDate = new DateTime();
    }

    $roamings = $roamingsStorage->getAll($fromDate->format('Y-m-d'),
                                         $toDate->format('Y-m-d'),
                                         $session->hasPermission(P_SEE_REPORT_PHONE));

    $json->returnResult(array(
        'status' => 'success',
        'roamings' => $roamings
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

