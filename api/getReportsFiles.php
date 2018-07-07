<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $reportFiles = $container->getReportFiles();
    $json = $container->getJson();

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

    $reports = $reportFiles->listReports($fromDate, $toDate);

    $json->returnResult(array(
        'status' => 'success',
        'reports' => $reports
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

