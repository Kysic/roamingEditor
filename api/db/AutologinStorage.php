<?php

class AutologinStorage {

    private $dbAccess;

    public function __construct($dbAccess) {
        $this->dbAccess = $dbAccess;
    }

    public function getUserIdFromAutologin($autologinId64, $autologinToken64) {
        $autologinId = @base64_decode($autologinId64);
        $this->deleteExpiredAutologinId();
        $query = 'SELECT userId, autologinTokenHash'.
                 ' FROM '.SQL_TABLE_AUTOLOGIN.
                 ' WHERE autologinId = :autologinId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':autologinId', $autologinId, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        $autologin = $request->fetch(PDO::FETCH_OBJ);
        if ($autologin) {
            $autologinToken = @base64_decode($autologinToken64);
            $autologinTokenHash = $this->hashAutologinToken($autologinToken);
            if ($autologin->autologinTokenHash === $autologinTokenHash) {
                return $autologin->userId;
            } else {
                $this->deleteAutologin($autologinId64);
                throw new SecurityException('Incorrect autologin token detected');
            }
        }
        return NULL;
    }
    public function createAutologinFor($userId) {
        $this->deleteExpiredAutologinId();
        $autologinToken = $this->generateAutologinToken();
        $autologinTokenHash = $this->hashAutologinToken($autologinToken);
        $nbAttempt = 10;
        do {
            $nbAttempt--;
            $autologinId = $this->generateAutologinId();
            $query = 'INSERT INTO '.SQL_TABLE_AUTOLOGIN.
                     ' (autologinId, autologinTokenHash, userId) VALUES (:autologinId, :autologinTokenHash, :userId)';
            $request = $this->dbAccess->getPdo()->prepare($query);
            $request->bindValue(':autologinId', $autologinId, PDO::PARAM_STR);
            $request->bindValue(':autologinTokenHash', $autologinTokenHash, PDO::PARAM_STR);
            $request->bindValue(':userId', $userId, PDO::PARAM_INT);
            $result = $request->execute();
        } while ($request->errorCode() === '23000' && $nbAttempt > 0);
        $this->dbAccess->raiseExceptionIfHasFailed($request);
        return array(base64_encode($autologinId), base64_encode($autologinToken));
    }
    public function updateAutologin($autologinId64) {
        $autologinId = @base64_decode($autologinId64);
        $autologinToken = $this->generateAutologinToken();
        $autologinTokenHash = $this->hashAutologinToken($autologinToken);
        $query = 'UPDATE '.SQL_TABLE_AUTOLOGIN.
                 ' SET autologinTokenHash=:autologinTokenHash, connectionDate=CURRENT_TIMESTAMP'.
                 ' WHERE autologinId=:autologinId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':autologinId', $autologinId, PDO::PARAM_STR);
        $request->bindValue(':autologinTokenHash', $autologinTokenHash, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return base64_encode($autologinToken);
    }
    public function deleteAutologin($autologinId64) {
        $autologinId = @base64_decode($autologinId64);
        $query = 'DELETE FROM '.SQL_TABLE_AUTOLOGIN.' WHERE autologinId=:autologinId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':autologinId', $autologinId, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
    }
    public function deleteAllUserAutologins($userId) {
        $query = 'DELETE FROM '.SQL_TABLE_AUTOLOGIN.' WHERE userId=:userId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':userId', $userId, PDO::PARAM_INT);
        $this->dbAccess->executeWithException($request);
    }

    private function deleteExpiredAutologinId() {
        $query = 'DELETE FROM '.SQL_TABLE_AUTOLOGIN.
                 ' WHERE connectionDate < (CURRENT_TIMESTAMP - INTERVAL '.AUTOLOGIN_DB_EXPIRATION.' SECOND)';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $this->dbAccess->executeWithException($request);
    }
    private function generateAutologinId() {
        return openssl_random_pseudo_bytes(16);
    }
    private function generateAutologinToken() {
        return openssl_random_pseudo_bytes(32);
    }
    private function hashAutologinToken($token) {
        return hash('sha256', $token, true);
    }

}

