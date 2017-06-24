<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $reportFiles = $container->getReportFiles();
    $json = $container->getJson();

    $session->checkLoggedIn();
    $session->checkHasPermission(P_DELETE_REPORT);

    $roamingDate = @$_GET['roamingDate'];
    if ( !$roamingDate ) {
        throw new BadRequestException('Date de maraude (roamingDate) attendue.');
    }
    $validator->validateRoamingDate($roamingDate);

    $reportFiles->rmReportFile($roamingDate);

    $json->returnResult(array(
        'status' => 'success'
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

