<?php

require_once('lib/Container.php');

try {

    $container = new Container();
    $session = $container->getSession();
    $json = $container->getJson();
    $validator = $container->getValidator();
    $usersStorage = $container->getUsersStorage();

    $session->checkLoggedIn();
    $session->checkHasPermission(P_SEE_USERS_LIST);

    $json->mergeJsonParameterToPost();

    $session->checkToken(@$_POST['sessionToken']);

    $userId = @$_POST['userId'];
    $validator->validateUserId($userId);
    if ($userId == $session->getUser()->userId) {
        throw new BadRequestException('Vous ne pouvez pas modifier votre propre rôle.');
    }
    $role = @$_POST['role'];
    $validator->validateRole($role);
    $user = $usersStorage->getUserWithId($userId);
    if (!$user) {
        throw new NotFoundException('Aucun compte utilisateur trouvé pour l\identifiant '.$userId.'.');
    } else if ($user->role == ROOT) {
        throw new BadRequestException('Vous ne pouvez pas changer le rôle des utilisateurs '.ROOT.'.');
    }
    $usersStorage->updateUserRole($userId, $role);

    $json->returnResult(array('status' => 'success'));

} catch (Exception $e) {
    $json->returnError($e);
}
