<?php

require_once('lib/auth.php');
require_once('lib/json.php');
require_once('db/roamingSql.php');

function validateRoamingVersion($roamingVersion) {
    if ( !filter_var($roamingVersion, FILTER_VALIDATE_INT) ) {
        throw new BadRequestException('Version de la maraude invalide, nombre entier attendu.');
    }
}

try {

    checkLoggedIn();
    checkHasPermission(P_SAVE_ROAMINGS);

    $dataObject = json_decode(file_get_contents('php://input'));
    if (!$dataObject) {
        throw new BadRequestException('Unable to parse post body as json.');
    }
    $roaming = $dataObject->roaming;
    if (!$roaming) {
        throw new BadRequestException('No roaming found in post body.');
    }
    validateRoamingDate($roaming->date);
    validateRoamingVersion($roaming->version);
    addRoaming($roaming, getSessionUser()->userId);
    returnResult(array('status' => 'success'));
} catch (Exception $e) {
    returnError($e);
}

?>
