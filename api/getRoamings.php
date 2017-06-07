<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $json = $container->getJson();
    $roamingsStorage = $container->getRoamingsStorage();

    $session->checkLoggedIn();
    $session->checkHasPermission(P_SEE_LAST_REPORT);

    $beginDate = new DateTime();
    $beginDate->sub(new DateInterval('P'.REPORT_OLD_LIMIT_DAYS.'D'));
    $endDate = new DateTime();
    $roamings = $roamingsStorage->getAll($beginDate->format('Y-m-d'), $endDate->format('Y-m-d'));

    $json->returnResult(array(
        'status' => 'success',
        'roamings' => $roamings
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

