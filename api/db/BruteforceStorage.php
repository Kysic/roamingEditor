<?php

class BruteforceStorage {

    private $dbAccess;
    public function __construct($dbAccess) {
        $this->dbAccess = $dbAccess;
    }

    public function registerFailedAttempt($ip) {
        $query = 'INSERT INTO '.SQL_TABLE_BRUTEFORCE.' (accessIp) VALUES (:ip)';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':ip', $ip, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
    }

    public function getNbFailedAttemptsInPeriod($ip) {
        $this->cleanUpOldAttempts();
        $query = 'SELECT COUNT(*) FROM '.SQL_TABLE_BRUTEFORCE.' WHERE accessIp = :ip';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':ip', $ip, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return $request->fetchColumn();
    }

    private function cleanUpOldAttempts() {
        $query = 'DELETE FROM '.SQL_TABLE_BRUTEFORCE.
                 ' WHERE accessDate < (CURRENT_TIMESTAMP - INTERVAL '.BRUTEFORCE_PERIOD_IN_SECOND.' SECOND)';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $this->dbAccess->executeWithException($request);
    }

}

