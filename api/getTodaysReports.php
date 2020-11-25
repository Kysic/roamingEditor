<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $json = $container->getJson();
    $reportsStorage = $container->getReportsStorage();

    $session->closeWrite();
    $session->checkLoggedIn();
    $session->checkHasPermission(P_SEE_LAST_115_REPORTS);


    $reports = $reportsStorage->getTodaysLast();

    $json->returnResult(array(
        'status' => 'success',
        'reports' => json_decode($reports)
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

