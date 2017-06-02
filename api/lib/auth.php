<?php

require_once('conf/permissions.php');
define('API_KEYS_CONF', 'conf/apiKeys.php');
define('USER_SQL_LIB', 'db/userSql.php');

function isLoggedIn() {
    return isset($_SESSION['USER']);
}

function getSessionUser() {
    if (isLoggedIn()) {
        return $_SESSION['USER'];
    } else {
        global $ROLES_PERMISSIONS;
        return (object) array('role' => VISITOR, 'permissions' => $ROLES_PERMISSIONS[VISITOR]);
    }
}

function setSessionUser($user) {
    global $ROLES_PERMISSIONS;
    if (array_key_exists($user->role, $ROLES_PERMISSIONS)) {
        $user->permissions = $ROLES_PERMISSIONS[$user->role];
    } else {
        $user->permissions = $ROLES_PERMISSIONS[VISITOR];
    }
    $_SESSION['USER'] = $user;
}

function deleteSessionUser() {
    unset($_SESSION['USER']);
}

function hasPermission($permission) {
    return in_array($permission, getSessionUser()->permissions);
}

function getSessionToken() {
    if ( !isset($_SESSION['POST_TOKEN']) ) {
        $_SESSION['POST_TOKEN'] = base64_encode(openssl_random_pseudo_bytes(32));
    }
    return $_SESSION['POST_TOKEN'];
}

function checkSessionToken($tokenToCheck) {
    if ( getSessionToken() != $tokenToCheck ) {
        throw new BadRequestException('Sequence d\'actions invalide, vous avez sans doute rafraîchit une page contenant un formulaire déjà envoyé.');
    }
}

function checkLoggedIn() {
    if (!isLoggedIn()) {
        throw new UnauthenticatedException('Vous devez être identifié pour faire cette action.');
    }
}

function checkHasPermission($permission, $errorMsg = 'Vous n\'êtes pas authorisé à faire cette action.') {
    if (!hasPermission($permission)) {
        throw new ForbiddenException($errorMsg);
    }
}

function getOlderRoamingDate() {
    $now = strtotime('now');
    return strtotime('-'.REPORT_OLD_LIMIT_DAYS.' day', $now);
}
function getOlderRoamingDateStr() {
    return date('Y-m-d', getOlderRoamingDate());
}

function validateRoamingDate($roamingDate) {
    if ( !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $roamingDate) ) {
        throw new BadRequestException('Date de la maraude invalide, format attendu yyyy-mm-dd.');
    }
    if ($roamingDate < getOlderRoamingDateStr()) {
        checkHasPermission(P_SEE_ALL_REPORT);
    }
}

function validateUserId($userId) {
    if ( empty( $userId ) ) {
        throw new BadRequestException('Absence de l\'identifiant de l\'utilisateur non renseignée, assurez-vous d\'avoir copier entièrement l\'URL reçue par email.');
    } else if (!filter_var($userId, FILTER_VALIDATE_INT)) {
        throw new BadRequestException('Identifiant de l\'utilisateur invalide.');
    }
}

function validateEmail($email) {
    if ( empty( $email ) ) {
        throw new BadRequestException('Adresse email non renseignée');
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new BadRequestException('Adresse email invalide.');
    }
}

function validatePassword($password) {
    if ( empty( $password ) ) {
        throw new BadRequestException('Mot de passe non renseigné');
    } else if ( mb_strlen($password) > MAX_PASSWORD_LENGTH ) {
        throw new BadRequestException('Mot de passe trop long, '.MAX_PASSWORD_LENGTH.' caractères maximum.');
    } else if ( mb_strlen($password) < MIN_PASSWORD_LENGTH ) {
        throw new BadRequestException('Mot de passe trop court, '.MIN_PASSWORD_LENGTH.' caractères maximum.');
    }
}

function validateMailTokenFormat($mailToken) {
    if ( empty( $mailToken ) ) {
        throw new BadRequestException('Absence du token de validation, assurez-vous d\'avoir copier entièrement l\'URL reçue par email.');
    }
}

function validateRole($role) {
    global $ROLES_PERMISSIONS;
    if ( empty( $role ) ) {
        throw new BadRequestException('Nom du rôle absent.');
    } else if ( $role == VISITOR ) {
        throw new BadRequestException('Le rôle '.VISITOR.' ne peut être associé à un utilisateur.');
    } else if ( $role == ROOT ) {
        throw new BadRequestException('Le rôle '.ROOT.' ne peut être associé à un utilisateur via ce formulaire.');
    } else if (!array_key_exists($role, $ROLES_PERMISSIONS)) {
        throw new BadRequestException('Ce rôle est inconnu.');
    }
}

session_start();

/// Autologin ///
define('AUTOLOGIN_COOKIE_KEY' , 'vcrPersistentLogin');
function resetAutologinIdCookie() {
    setcookie(AUTOLOGIN_COOKIE_KEY, '', time() - AUTOLOGIN_COOKIE_EXPIRATION, '/');
}
function generateAutologinId($userId) {
    require_once(USER_SQL_LIB);
    $autologinId = createAutologinIdForUserId($userId);
    setcookie(AUTOLOGIN_COOKIE_KEY, $autologinId, time() + AUTOLOGIN_COOKIE_EXPIRATION, '/');
}
function connectWithAutologin($autologinId64) {
    require_once(USER_SQL_LIB);
    $user = getUserWithAutologinId($autologinId64);
    if (!$user) {
        throw new NotFoundException('Unrecognized autologin id');
    }
    setSessionUser($user);
}

if ( !isLoggedIn() && !empty($_COOKIE[AUTOLOGIN_COOKIE_KEY]) ) {
    try {
        connectWithAutologin($_COOKIE[AUTOLOGIN_COOKIE_KEY]);
    } catch (Exception $e) {
        resetAutologinIdCookie();
    }
}

?>
