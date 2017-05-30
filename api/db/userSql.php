<?php

define('USERS_TABLE', 'vcr_users');
define('AUTOLOGIN_TABLE', 'vcr_autologin');

require_once('db/sql.php');

function hashPasssword($salt, $password) {
    return hash('sha256', $salt.$password.'CLQRr4HFwdqr', true);
}

function generateSalt() {
	return openssl_random_pseudo_bytes(16);
}

function generateMailToken() {
	return openssl_random_pseudo_bytes(32);
}

function changePassword($userId, $newPassword) {
    $passwordSalt = generateSalt();
    $passwordHash = hashPasssword($passwordSalt, $newPassword);
    $query = 'UPDATE '.USERS_TABLE.
             ' SET passwordSalt=:passwordSalt, passwordHash=:passwordHash'.
             ' WHERE userId=:userId';
    $request = getPdo()->prepare($query);
    $request->bindValue(':userId', $userId, PDO::PARAM_INT);
    $request->bindValue(':passwordSalt', $passwordSalt, PDO::PARAM_STR);
    $request->bindValue(':passwordHash', $passwordHash, PDO::PARAM_STR);
    executeWithException($request);
}

function getAllUsers() {
	$query = 'SELECT userId, email, firstname, lastname, role, registrationDate'.
             ' FROM '.USERS_TABLE.
		     ' ORDER BY firstname, lastname';
	$request = getPdo()->prepare($query);
    executeWithException($request);
	return $request->fetchAll(PDO::FETCH_OBJ);
}

function getUserWithId($userId) {
    $query = 'SELECT userId, email, firstname, lastname, role, registrationDate'.
             ' FROM '.USERS_TABLE.
             ' WHERE userId = :userId';
    $request = getPdo()->prepare($query);
    $request->bindValue(':userId', $userId, PDO::PARAM_STR);
    executeWithException($request);
    return $request->fetch(PDO::FETCH_OBJ);
}

function getUserWithEmail($email) {
    $query = 'SELECT userId, email, firstname, lastname, role, registrationDate'.
             ' FROM '.USERS_TABLE.
             ' WHERE email = :email';
    $request = getPdo()->prepare($query);
    $request->bindValue(':email', $email, PDO::PARAM_STR);
    executeWithException($request);
    return $request->fetch(PDO::FETCH_OBJ);
}

function addUser($email, $firstname, $lastname) {
    if (getUserWithEmail($email)) {
        throw new BadRequestException('Un compte existe déjà pour cette adresse email, utilisez la procédure de mot de passe perdu si vous avez oublié votre mot de passe.');
    }
	$query = 'INSERT INTO '.USERS_TABLE.' (email, firstname, lastname) VALUES (:email, :firstname, :lastname)';
	$request = getPdo()->prepare($query);
	$request->bindValue(':email', $email, PDO::PARAM_STR);
	$request->bindValue(':firstname', $firstname, PDO::PARAM_STR);
	$request->bindValue(':lastname', $lastname, PDO::PARAM_STR);
    executeWithException($request);
}

function checkAndGetUser($email, $password) {
    $query = 'SELECT userId, email, firstname, lastname, role, registrationDate, passwordSalt, passwordHash'.
             ' FROM '.USERS_TABLE.
             ' WHERE email = :email';
    $request = getPdo()->prepare($query);
    $request->bindValue(':email', $email, PDO::PARAM_STR);
    executeWithException($request);
    $user = $request->fetch(PDO::FETCH_OBJ);
    if ( ! $user ) {
        throw new BadRequestException('Aucun compte utilisateur n\'est associé à cette adresse email.');
    } else if ( empty($user->passwordSalt) || empty($user->passwordHash) ) {
        throw new BadRequestException('Inscription non terminée, veuillez suivre les indications du mail envoyé lors de l\'inscription.');
    } else if ( hashPasssword($user->passwordSalt, $password) == $user->passwordHash ) {
        unset($user->passwordSalt);
        unset($user->passwordHash);
        return $user;
    } else {
        throw new BadRequestException('Identifiants invalides.');
    }
}

function updateUser($userId, $email, $firstname, $lastname) {
	$query = 'UPDATE '.USERS_TABLE.
	         ' SET email=:email, firstname=:firstname, lastname=:lastname'.
             ' WHERE userId=:userId';
	$request = getPdo()->prepare($query);
	$request->bindValue(':userId', $userId, PDO::PARAM_INT);
	$request->bindValue(':email', $email, PDO::PARAM_STR);
	$request->bindValue(':firstname', $firstname, PDO::PARAM_STR);
	$request->bindValue(':lastname', $email, PDO::PARAM_STR);
    executeWithException($request);
	return $request->errorCode();
}

function updateUserRole($userId, $newRole) {
	$query = 'UPDATE '.USERS_TABLE.
	         ' SET role=:role'.
             ' WHERE userId=:userId';
	$request = getPdo()->prepare($query);
	$request->bindValue(':userId', $userId, PDO::PARAM_INT);
	$request->bindValue(':role', $newRole, PDO::PARAM_STR);
    executeWithException($request);
	return $request->errorCode();
}

function generateUserMailToken($userId) {
    $mailToken = generateMailToken();
	$query = 'UPDATE '.USERS_TABLE.
	         ' SET mailToken=:mailToken WHERE userId=:userId';
	$request = getPdo()->prepare($query);
	$request->bindValue(':userId', $userId, PDO::PARAM_INT);
	$request->bindValue(':mailToken', $mailToken, PDO::PARAM_STR);
    executeWithException($request);
	return base64_encode($mailToken);
}

function validateMailToken($userId, $mailToken) {
    $mailToken = @base64_decode($mailToken);
    if (!$mailToken) {
        throw new BadRequestException('Token de validation invalide, assurez-vous d\'avoir copier correctement l\'URL reçue par email.');
    }
	$query = 'SELECT userId FROM '.USERS_TABLE.
	         ' WHERE userId=:userId AND mailToken=:mailToken';
	$request = getPdo()->prepare($query);
	$request->bindValue(':userId', $userId, PDO::PARAM_INT);
	$request->bindValue(':mailToken', $mailToken, PDO::PARAM_STR);
    executeWithException($request);
    $user = $request->fetch(PDO::FETCH_OBJ);
    if ( !$user  || $user->userId != $userId ) {
        throw new BadRequestException('Le lien de modification du mot de passe n\'est plus valide, veuillez suivre la procédure de mot de passe perdu.');
    }
}

function resetUserMailToken($userId) {
	$query = 'UPDATE '.USERS_TABLE.
	         ' SET mailToken = NULL WHERE userId=:userId';
	$request = getPdo()->prepare($query);
	$request->bindValue(':userId', $userId, PDO::PARAM_INT);
    executeWithException($request);
}

/// Persitent login ///
function deleteUserAutologinId($userId) {
    $query = 'DELETE FROM '.AUTOLOGIN_TABLE.' WHERE userId=:userId';
    $request = getPdo()->prepare($query);
	$request->bindValue(':userId', $userId, PDO::PARAM_INT);
    executeWithException($request);
}
function deleteExpiredAutologinId() {
	$query = 'DELETE FROM '.AUTOLOGIN_TABLE.
	         ' WHERE connectionDate < (CURRENT_TIMESTAMP - INTERVAL '.AUTOLOGIN_COOKIE_EXPIRATION.' SECOND)';
    $request = getPdo()->prepare($query);
    executeWithException($request);
}
function getUserWithAutologinId($autologinId64) {
    $autologinId = @base64_decode($autologinId64);
    if (!$autologinId) {
        throw new BadRequestException('Autologin id invalide');
    }
    deleteExpiredAutologinId();
    $query = 'SELECT userId, email, firstname, lastname, role, registrationDate'.
             ' FROM '.USERS_TABLE.' JOIN '.AUTOLOGIN_TABLE.' USING(userId)'.
             ' WHERE autologinId = :autologinId';
    $request = getPdo()->prepare($query);
    $request->bindValue(':autologinId', $autologinId, PDO::PARAM_STR);
    executeWithException($request);
    return $request->fetch(PDO::FETCH_OBJ);
}
function createAutologinIdForUserId($userId) {
    deleteExpiredAutologinId();
    // Create the new one
    $autologinId = openssl_random_pseudo_bytes(48);
	$query = 'INSERT INTO '.AUTOLOGIN_TABLE.' (autologinId, userId) VALUES (:autologinId, :userId)';
	$request = getPdo()->prepare($query);
	$request->bindValue(':autologinId', $autologinId, PDO::PARAM_STR);
	$request->bindValue(':userId', $userId, PDO::PARAM_STR);
    executeWithException($request);
    return base64_encode($autologinId);
}

?>
