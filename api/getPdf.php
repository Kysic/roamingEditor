<?php

require_once('lib/auth.php');
require_once('lib/json.php');
require_once('lib/spreadSheetsGenerator.php');

try {

    checkLoggedIn();
    checkHasPermission(P_SEE_LAST_REPORT);

    $roamingId = @$_GET['roamingId'];
    if ( !$roamingId ) {
        throw new BadRequestException('Identifiant de maraude (roamingId) attendu.');
    }
    $roamingDate = getRoamingDate($roamingId);
    validateRoamingDate($roamingDate);
    $docId = getOrCreateDocId($roamingId, getSessionUser()->userId);
    $printUrl = docIdToPrintUrl($docId);

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="CR_'.$roamingDate.'.pdf"');
    echo file_get_contents($printUrl);

} catch (Exception $e) {
    returnError($e);
}

