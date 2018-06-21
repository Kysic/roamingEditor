<?php

require_once('lib/Container.php');

try {

    $email = @$_GET['email'];
    $password = @$_GET['password'];

    $container = new Container();
    $session = $container->getSession();
    $validator = $container->getValidator();
    $usersStorage = $container->getUsersStorage();
    $json = $container->getJson();

    $session->checkIsRoot();
    $validator->validateEmail($email);
    $validator->validatePassword($password);

    $user = $usersStorage->getUserWithEmail($email);
    if (!$user) {
        throw new BadRequestException('No user match this email');
    }
    $usersStorage->changePassword($user->userId, $password);

    $json->returnResult(array('status' => 'success', 'user' => $user));

} catch (Exception $e) {
    $json->returnError($e);
}

