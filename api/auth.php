<?php

require_once('lib/auth.php');
require_once('lib/json.php');
define('GOOGLE_CONTACT_LIB', 'lib/googleContacts.php');
define('MAIL_LIB', 'lib/mail.php');

function signin() {
    checkHasPermission(P_LOG_IN, 'Vous n\'êtes pas autorisé à vous inscrire sur le site.');
    checkSessionToken(@$_POST['sessionToken']);
    $email = strtolower(@$_POST['email']);
    validateEmail($email);
    require_once(GOOGLE_CONTACT_LIB);
    $contacts = extractContacts();
    $isInVinciContacts = array_key_exists($email, $contacts);
    if ($isInVinciContacts) {
        $firstname = $contacts[$email]['firstname'];
        $lastname = $contacts[$email]['lastname'];
    } else {
        throw new ForbiddenException('Cette adresse mail n\'est pas repertoriée dans la liste des contacts du VINCI.');
    }
    require_once(USER_SQL_LIB);
    addUser($email, $firstname, $lastname);
    $user = getUserWithEmail($email);
    $mailToken = generateUserMailToken($user->userId);
    require_once(MAIL_LIB);
    sendSigninToken($user->email, $user->firstname, $user->lastname, $user->userId, $mailToken);
}
function setPassword() {
    checkSessionToken(@$_POST['sessionToken']);
    $password = @$_POST['password'];
    validatePassword($password);
    if ($password != @$_POST['passwordConfirm']) {
        throw new BadRequestException('Le mot de passe et sa confirmation doivent être identiques.');
    }
    if (isLoggedIn()) {
        checkHasPermission(P_CHANGE_PASSWORD, 'Vous n\'êtes pas autorisé à modifier votre mot de passe.');
        require_once(USER_SQL_LIB);
        changePassword(getSessionUser()->userId, $password);
        deleteUserAutologinId(getSessionUser()->userId);
    } else {
        checkHasPermission(P_RESET_PASSWORD, 'Vous n\'êtes pas autorisé à réinitialiser un mot de passe.');
        $userId = @$_POST['userId'];
        validateUserId($userId);
        $mailToken = @$_POST['mailToken'];
        validateMailTokenFormat($mailToken);
        require_once(USER_SQL_LIB);
        validateMailToken($userId, $mailToken);
        changePassword($userId, $password);
        resetUserMailToken($userId);
        deleteUserAutologinId($userId);
        setSessionUser(getUserWithId($userId));
    }
}
function resetPassword() {
    checkHasPermission(P_RESET_PASSWORD, 'Vous n\'êtes pas autorisé à réinitialiser un mot de passe.');
    checkSessionToken(@$_POST['sessionToken']);
    $email = strtolower(@$_POST['email']);
    validateEmail($email);
    require_once(USER_SQL_LIB);
    $user = getUserWithEmail($email);
    if (!$user) {
        throw new BadRequestException('Aucun compte utilisateur n\'est associé à cette adresse email.');
    }
    $mailToken = generateUserMailToken($user->userId);
    require_once(MAIL_LIB);
    sendResetPasswordToken($user->email, $user->firstname, $user->lastname, $user->userId, $mailToken);
}
function login() {
    checkHasPermission(P_LOG_IN, 'Vous n\'êtes pas autorisé à vous connecter sur le site.');
    checkSessionToken(@$_POST['sessionToken']);
    $email = strtolower(@$_POST['email']);
    validateEmail($email);
    $password = @$_POST['password'];
    validatePassword($password);
    require_once(USER_SQL_LIB);
    setSessionUser(checkAndGetUser($email, $password));
    resetUserMailToken(getSessionUser()->userId);
    if (@$_POST['stayLogged'] == 'true') {
        generateAutologinId(getSessionUser()->userId);
    }
}
function logout() {
    checkHasPermission(P_LOG_OUT, 'Vous n\'êtes pas autorisé à vous déconnecter du site.');
    checkSessionToken(@$_POST['sessionToken']);
    require_once(USER_SQL_LIB);
    deleteUserAutologinId(getSessionUser()->userId);
    resetAutologinIdCookie();
    deleteSessionUser();
}

try {

    mergeJsonParameterToPost();

    if ( @$_POST['action'] == 'login' ) {
        if (!isLoggedIn()) {
            login();
        }
    } else if ( @$_POST['action'] == 'logout' ) {
        if (isLoggedIn()) {
            logout();
        }
    } else if ( @$_POST['action'] == 'resetPassword' ) {
        resetPassword();
    } else if ( @$_POST['action'] == 'signin' && !isLoggedIn() ) {
        signin();
    } else if ( @$_POST['action'] == 'setPassword' ) {
        setPassword();
    } else if ( @$_POST['action'] ) {
        throw new BadRequestException('Action inattendue dans ce contexte.');
    }

    returnResult(array(
        'status' => 'success',
        'sessionToken' => getSessionToken(),
        'isLoggedIn' => isLoggedIn(),
        'user' => getSessionUser()
    ));

} catch (Exception $e) {
    returnError($e);
}

?>
