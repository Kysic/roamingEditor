<?php

class UsersStorage {

    private $dbAccess;
    public function __construct($dbAccess) {
        $this->dbAccess = $dbAccess;
    }

    public function changePassword($userId, $newPassword) {
        $passwordSalt = $this->generateSalt();
        $passwordHash = $this->hashPasssword($passwordSalt, $newPassword);
        $query = 'UPDATE '.SQL_TABLE_USERS.
                 ' SET passwordSalt=:passwordSalt, passwordHash=:passwordHash'.
                 ' WHERE userId=:userId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':userId', $userId, PDO::PARAM_INT);
        $request->bindValue(':passwordSalt', $passwordSalt, PDO::PARAM_STR);
        $request->bindValue(':passwordHash', $passwordHash, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
    }

    public function getAllUsers() {
        $query = 'SELECT userId, email, firstname, lastname, role, registrationDate'.
                 ' FROM '.SQL_TABLE_USERS.
                 ' ORDER BY firstname, lastname';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $this->dbAccess->executeWithException($request);
        return $request->fetchAll(PDO::FETCH_OBJ);
    }

    public function getUserWithId($userId) {
        $query = 'SELECT userId, email, firstname, lastname, role, registrationDate'.
                 ' FROM '.SQL_TABLE_USERS.
                 ' WHERE userId = :userId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':userId', $userId, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return $request->fetch(PDO::FETCH_OBJ);
    }

    public function getUserWithEmail($email) {
        $query = 'SELECT userId, email, firstname, lastname, role, registrationDate'.
                 ' FROM '.SQL_TABLE_USERS.
                 ' WHERE email = :email';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':email', $email, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return $request->fetch(PDO::FETCH_OBJ);
    }

    public function addUser($email, $firstname, $lastname) {
        if ($this->getUserWithEmail($email)) {
            throw new BadRequestException('Un compte existe déjà pour cette adresse email, utilisez la procédure de mot de passe perdu si vous avez oublié votre mot de passe.');
        }
        $query = 'INSERT INTO '.SQL_TABLE_USERS.' (email, firstname, lastname) VALUES (:email, :firstname, :lastname)';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':email', $email, PDO::PARAM_STR);
        $request->bindValue(':firstname', $firstname, PDO::PARAM_STR);
        $request->bindValue(':lastname', $lastname, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
    }

    public function checkAndGetUser($email, $password) {
        $query = 'SELECT userId, email, firstname, lastname, role, registrationDate, passwordSalt, passwordHash'.
                 ' FROM '.SQL_TABLE_USERS.
                 ' WHERE email = :email';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':email', $email, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        $user = $request->fetch(PDO::FETCH_OBJ);
        if ( ! $user ) {
            throw new NotFoundException('Aucun compte utilisateur n\'est associé à cette adresse email.');
        } else if ( empty($user->passwordSalt) || empty($user->passwordHash) ) {
            throw new BadRequestException('Inscription non terminée, veuillez suivre les indications du mail envoyé lors de l\'inscription.');
        } else if ( $this->hashPasssword($user->passwordSalt, $password) == $user->passwordHash ) {
            unset($user->passwordSalt);
            unset($user->passwordHash);
            return $user;
        } else {
            throw new BadRequestException('Identifiants invalides.');
        }
    }

    public function updateUser($userId, $email, $firstname, $lastname) {
        $query = 'UPDATE '.SQL_TABLE_USERS.
                 ' SET email=:email, firstname=:firstname, lastname=:lastname'.
                 ' WHERE userId=:userId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':userId', $userId, PDO::PARAM_INT);
        $request->bindValue(':email', $email, PDO::PARAM_STR);
        $request->bindValue(':firstname', $firstname, PDO::PARAM_STR);
        $request->bindValue(':lastname', $email, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return $request->errorCode();
    }

    public function updateUserRole($userId, $newRole) {
        $query = 'UPDATE '.SQL_TABLE_USERS.
                 ' SET role=:role'.
                 ' WHERE userId=:userId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':userId', $userId, PDO::PARAM_INT);
        $request->bindValue(':role', $newRole, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return $request->errorCode();
    }

    public function generateUserMailToken($userId) {
        $mailToken = $this->generateMailToken();
        $query = 'UPDATE '.SQL_TABLE_USERS.
                 ' SET mailToken=:mailToken WHERE userId=:userId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':userId', $userId, PDO::PARAM_INT);
        $request->bindValue(':mailToken', $mailToken, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return base64_encode($mailToken);
    }

    public function validateMailToken($userId, $mailToken) {
        $mailToken = @base64_decode($mailToken);
        if (!$mailToken) {
            throw new BadRequestException('Token de validation invalide, assurez-vous d\'avoir copier correctement l\'URL reçue par email.');
        }
        $query = 'SELECT userId FROM '.SQL_TABLE_USERS.
                 ' WHERE userId=:userId AND mailToken=:mailToken';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':userId', $userId, PDO::PARAM_INT);
        $request->bindValue(':mailToken', $mailToken, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        $user = $request->fetch(PDO::FETCH_OBJ);
        if ( !$user  || $user->userId != $userId ) {
            throw new BadRequestException('Le lien de modification du mot de passe n\'est plus valide, veuillez suivre la procédure de mot de passe perdu.');
        }
    }

    public function resetUserMailToken($userId) {
        $query = 'UPDATE '.SQL_TABLE_USERS.
                 ' SET mailToken = NULL WHERE userId=:userId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':userId', $userId, PDO::PARAM_INT);
        $this->dbAccess->executeWithException($request);
    }

    private function hashPasssword($salt, $password) {
        return hash('sha256', $salt.$password.'CLQRr4HFwdqr', true);
    }

    private function generateSalt() {
        return openssl_random_pseudo_bytes(16);
    }

    private function generateMailToken() {
        return openssl_random_pseudo_bytes(32);
    }

    /// Persitent login ///
    public function deleteUserAutologinId($userId) {
        $query = 'DELETE FROM '.SQL_TABLE_AUTOLOGIN.' WHERE userId=:userId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':userId', $userId, PDO::PARAM_INT);
        $this->dbAccess->executeWithException($request);
    }
    public function getUserWithAutologinId($autologinId64) {
        $autologinId = @base64_decode($autologinId64);
        if (!$autologinId) {
            throw new BadRequestException('Autologin id invalide');
        }
        $this->deleteExpiredAutologinId();
        $query = 'SELECT userId, email, firstname, lastname, role, registrationDate'.
                 ' FROM '.SQL_TABLE_USERS.' JOIN '.SQL_TABLE_AUTOLOGIN.' USING(userId)'.
                 ' WHERE autologinId = :autologinId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':autologinId', $autologinId, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return $request->fetch(PDO::FETCH_OBJ);
    }
    public function createAutologinIdForUserId($userId) {
        $this->deleteExpiredAutologinId();
        // Create the new one
        $autologinId = openssl_random_pseudo_bytes(48);
        $query = 'INSERT INTO '.SQL_TABLE_AUTOLOGIN.' (autologinId, userId) VALUES (:autologinId, :userId)';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':autologinId', $autologinId, PDO::PARAM_STR);
        $request->bindValue(':userId', $userId, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return base64_encode($autologinId);
    }
    private function deleteExpiredAutologinId() {
        $query = 'DELETE FROM '.SQL_TABLE_AUTOLOGIN.
                 ' WHERE connectionDate < (CURRENT_TIMESTAMP - INTERVAL '.AUTOLOGIN_COOKIE_EXPIRATION.' SECOND)';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $this->dbAccess->executeWithException($request);
    }

}

