<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $googlePlanning = $container->getGooglePlanning();
    $json = $container->getJson();

    $session->closeWrite();
    $session->checkLoggedIn();
    $session->checkHasPermission(P_EDIT_PLANNING);

    if ( !empty($_GET['monthId']) ) {
        $monthId = $_GET['monthId'];
        $validator->validateMonthId($monthId);
    } else {
        $monthId = date('Y-m');
    }

    $planningUrl = $googlePlanning->getUrl($monthId);
    $json->returnResult(array(
        'status' => 'success',
        'editUrl' => $planningUrl
    ));

} catch (Exception $e) {
    $json->returnError($e);
}
