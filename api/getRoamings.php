<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $json = $container->getJson();
    $roamingsStorage = $container->getRoamingsStorage();

    $session->checkLoggedIn();
    $session->checkHasPermission(P_SEE_LAST_REPORT);

    $roamings = $roamingsStorage->getAll('2000-01-01', '2020-01-01');

    $json->returnResult(array(
        'status' => 'success',
        'roamings' => $roamings
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

