<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $json = $container->getJson();
    $roamingsStorage = $container->getRoamingsStorage();
    $spreadsheetsGenerator = $container->getSpreadsheetsGenerator();

    $session->closeWrite();
    $session->checkLoggedIn();
    $session->checkHasPermission(P_EDIT_REPORT);

    $roamingId = @$_GET['roamingId'];
    if ( !$roamingId ) {
        throw new BadRequestException('Identifiant de maraude (roamingId) attendu.');
    }
    $roamingDate = $roamingsStorage->getDate($roamingId);
    $validator->validateRoamingDate($roamingDate);
    $docId = $spreadsheetsGenerator->getOrCreateDocId($roamingId, $session->getUser()->userId);
    $json->returnResult(array(
        'status' => 'success',
        'docId' => $docId,
        'editUrl' => $spreadsheetsGenerator->docIdToEditUrl($docId),
        'printUrl' => $spreadsheetsGenerator->docIdToPrintUrl($docId)
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

