<?php

require_once('lib/Container.php');

function validateRoamingVersion($roamingVersion) {
    if ( !filter_var($roamingVersion, FILTER_VALIDATE_INT) ) {
        throw new BadRequestException('Version de la maraude invalide, nombre entier attendu.');
    }
}

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $json = $container->getJson();
    $roamingsStorage = $container->getRoamingsStorage();

    $session->checkLoggedIn();
    $session->checkHasPermission(P_SAVE_ROAMINGS);

    $dataObject = json_decode(file_get_contents('php://input'));
    if (!$dataObject) {
        throw new BadRequestException('Unable to parse post body as json.');
    }
    $roaming = @$dataObject->roaming;
    if (!$roaming) {
        throw new BadRequestException('No roaming found in post body.');
    }
    $validator->validateRoamingDate($roaming->date);
    validateRoamingVersion($roaming->version);
    unset($roaming->synchroStatus);
    $roamingsStorage->add($roaming, $session->getUser()->userId);
    $json->returnResult(array('status' => 'success'));
} catch (Exception $e) {
    $json->returnError($e);
}

