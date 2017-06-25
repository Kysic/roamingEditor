<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $googleContacts = $container->getGoogleContacts();
    $json = $container->getJson();

    $session->checkLoggedIn();
    $session->checkHasPermission(P_SEE_USERS_LIST);

    $members = $googleContacts->extractContacts();

    $json->returnResult(array(
        'status' => 'success',
        'members' => $members
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

