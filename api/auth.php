<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $json = $container->getJson();
    $session = $container->getSession();
    $auth = $container->getAuth();

    $json->mergeJsonParameterToPost();

    if ( @$_GET['emulateRole'] ) {
        $auth->emulateRole(@$_GET['emulateRole']);
    }

    if ( @$_POST['action'] == 'login' ) {
        if (!$session->isLoggedIn()) {
            $auth->login(@$_POST['email'], @$_POST['password'], @$_POST['stayLogged'] == 'true', @$_POST['sessionToken']);
        }
    } else if ( @$_POST['action'] == 'logout' ) {
        if ($session->isLoggedIn()) {
            $auth->logout(@$_POST['sessionToken']);
        }
    } else if ( @$_POST['action'] == 'resetPassword' ) {
        $auth->resetPassword(@$_POST['email'], @$_POST['sessionToken']);
    } else if ( @$_POST['action'] == 'register' ) {
        $auth->register(@$_POST['email'], @$_POST['sessionToken']);
    } else if ( @$_POST['action'] == 'setPassword' ) {
        if ($session->isLoggedIn()) {
            $auth->changePassword(@$_POST['password'], @$_POST['passwordConfirm'], @$_POST['sessionToken']);
        } else {
            $auth->setPassword(@$_POST['password'], @$_POST['passwordConfirm'], @$_POST['userId'], @$_POST['mailToken'], @$_POST['sessionToken']);
        }
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

