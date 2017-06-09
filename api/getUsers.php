<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $json = $container->getJson();
    $usersStorage = $container->getUsersStorage();

    $session->checkLoggedIn();
    $session->checkHasPermission(P_SEE_USERS_LIST);

    $users = $usersStorage->getAllUsers();

    $json->returnResult(array(
        'status' => 'success',
        'users' => $users
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

