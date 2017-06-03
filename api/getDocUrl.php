<?php

require_once('lib/auth.php');
require_once('lib/json.php');
require_once('lib/spreadSheetsGenerator.php');

try {

    checkLoggedIn();
    checkHasPermission(P_EDIT_REPORT);

    $roamingId = @$_GET['roamingId'];
    if ( !$roamingId ) {
        throw new BadRequestException('Identifiant de maraude (roamingId) attendu.');
    }
    $roamingDate = getRoamingDate($roamingId);
    validateRoamingDate($roamingDate);
    $docId = getOrCreateDocId($roamingId, getSessionUser()->userId);
    returnResult(array(
        'status' => 'success',
        'docId' => $docId,
        'editUrl' => docIdToEditUrl($docId),
        'printUrl' => docIdToPrintUrl($docId)
    ));

} catch (Exception $e) {
    returnError($e);
}

