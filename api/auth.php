<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $auth = $container->getAuth();
    $json = $container->getJson();

    $json->mergeJsonParameterToPost();

    if ( @$_POST['action'] == 'login' ) {
        if (!$session->isLoggedIn()) {
            $auth->login();
        }
    } else if ( @$_POST['action'] == 'logout' ) {
        if ($session->isLoggedIn()) {
            $auth->logout();
        }
    } else if ( @$_POST['action'] == 'resetPassword' ) {
        $auth->resetPassword();
    } else if ( @$_POST['action'] == 'signin' && !isLoggedIn() ) {
        $auth->signin();
    } else if ( @$_POST['action'] == 'setPassword' ) {
        $auth->setPassword();
    } else if ( @$_POST['action'] ) {
        throw new BadRequestException('Action inattendue dans ce contexte.');
    }

    $json->returnResult(array(
        'status' => 'success',
        'sessionToken' => $session->getToken(),
        'isLoggedIn' => $session->isLoggedIn(),
        'user' => $session->getUser()
    ));

} catch (Exception $e) {
    $json->returnError($e);
}

