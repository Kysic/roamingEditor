<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $json = $container->getJson();
    $roamingsStorage = $container->getRoamingsStorage();
    $spreadsheetsGenerator = $container->getSpreadsheetsGenerator();
    $reportFiles = $container->getReportFiles();

    $session->closeWrite();
    $session->checkLoggedIn();
    $session->checkHasPermission(P_SEE_LAST_REPORT);

    $roamingId = @$_GET['roamingId'];
    if ( !$roamingId ) {
        throw new BadRequestException('Identifiant de maraude (roamingId) attendu.');
    }

    if ($reportFiles->reportFileExists($roamingId)) {
        $roamingDate = $roamingId;
        $validator->validateRoamingDate($roamingDate);
        $printUrl = $reportFiles->getFileUrl($roamingDate);
    } else {
        $roamingDate = $roamingsStorage->getDate($roamingId);
        $validator->validateRoamingDate($roamingDate);
        $docId = $spreadsheetsGenerator->getOrCreateDocId($roamingId, $session->getUser()->userId);
        $printUrl = $spreadsheetsGenerator->docIdToPrintUrl($docId);
    }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="VINCI_CR_'.$roamingDate.'.pdf"');
    echo file_get_contents($printUrl);

} catch (Exception $e) {
    $json->returnError($e);
}

