<?php

class RoamingsStorage {

    private $dbAccess;

    public function __construct($dbAccess) {
        $this->dbAccess = $dbAccess;
    }

    public function add($roaming, $userId) {
        $query = 'INSERT INTO '.SQL_TABLE_ROAMINGS.' (roamingDate, version, creationUserId, rawJson)'.
                 ' VALUES (:roamingDate, :version, :creationUserId, :rawJson)';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':roamingDate', $roaming->date, PDO::PARAM_STR);
        $request->bindValue(':version', $roaming->version, PDO::PARAM_INT);
        $request->bindValue(':creationUserId', $userId, PDO::PARAM_INT);
        $request->bindValue(':rawJson', json_encode($roaming), PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
    }

    public function getAll($beginDate, $endDate) {
        $query = 'SELECT roamingId, rawJson FROM '.SQL_TABLE_ROAMINGS.' r1'.
                 ' WHERE :beginDate <= r1.roamingDate AND r1.roamingDate <= :endDate'.
                 ' AND r1.version = (select max(r2.version) from '.SQL_TABLE_ROAMINGS.' r2 where r1.roamingDate = r2.roamingDate)';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':beginDate', $beginDate, PDO::PARAM_STR);
        $request->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return $request->fetchAll(PDO::FETCH_FUNC|PDO::FETCH_GROUP|PDO::FETCH_UNIQUE, 'json_decode');
    }

    public function getRoamingId($roamingDate) {
        $query = 'SELECT roamingId FROM '.SQL_TABLE_ROAMINGS.' r1 WHERE roamingDate = :roamingDate '.
                 ' AND r1.version = (select max(r2.version) from '.SQL_TABLE_ROAMINGS.' r2 where r2.roamingDate = :roamingDate)';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':roamingDate', $roamingDate, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        $report = $request->fetch(PDO::FETCH_OBJ);
        return $report ? $report->roamingId : 0;
    }

    public function reportExistsFor($roamingDate) {
        $query = 'SELECT max(roamingId) maxId FROM '.SQL_TABLE_ROAMINGS.' WHERE roamingDate = :roamingDate';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':roamingDate', $roamingDate, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        $report = $request->fetch(PDO::FETCH_OBJ);
        return $report && $report->maxId > 0;
    }

    public function getDocsIds($beginDate, $endDate) {
        $query = 'SELECT roamingDate, docId FROM '.SQL_TABLE_ROAMINGS.' r1'.
                 ' WHERE :beginDate <= r1.roamingDate AND r1.roamingDate <= :endDate AND docId IS NOT NULL'.
                 ' AND r1.version = (select max(r2.version) from '.SQL_TABLE_ROAMINGS.' r2 where r1.roamingDate = r2.roamingDate)'.
                 ' ORDER BY r1.roamingDate';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':beginDate', $beginDate, PDO::PARAM_STR);
        $request->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        $this->dbAccess->executeWithException($request);
        return $request->fetchAll(PDO::FETCH_OBJ);
    }

    public function getDocId($roamingId) {
        $query = 'SELECT docId FROM '.SQL_TABLE_ROAMINGS.' WHERE roamingId = :roamingId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':roamingId', $roamingId, PDO::PARAM_INT);
        $this->dbAccess->executeWithException($request);
        $roaming = $request->fetch(PDO::FETCH_OBJ);
        if ( !$roaming ) {
            throw new NotFoundException('No roaming found with id ' . $roamingId);
        }
        return $roaming->docId;
    }

    public function getDate($roamingId) {
        $query = 'SELECT roamingDate FROM '.SQL_TABLE_ROAMINGS.' WHERE roamingId = :roamingId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':roamingId', $roamingId, PDO::PARAM_INT);
        $this->dbAccess->executeWithException($request);
        $roaming = $request->fetch(PDO::FETCH_OBJ);
        if ( !$roaming ) {
            throw new NotFoundException('No roaming found with id ' . $roamingId);
        }
        return $roaming->roamingDate;
    }

    public function getJson($roamingId) {
        $query = 'SELECT rawJson FROM '.SQL_TABLE_ROAMINGS.' WHERE roamingId = :roamingId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':roamingId', $roamingId, PDO::PARAM_INT);
        $this->dbAccess->executeWithException($request);
        $roaming = $request->fetch(PDO::FETCH_OBJ);
        if ( !$roaming ) {
            throw new NotFoundException('No roaming found with id ' . $roamingId);
        }
        return $roaming->rawJson;
    }

    public function setDocId($roamingId, $docId, $userId) {
        $query = 'UPDATE '.SQL_TABLE_ROAMINGS.
                 ' SET docId=:docId, generationDate=NOW(), generationUserId=:generationUserId'.
                 ' WHERE roamingId=:roamingId';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $request->bindValue(':roamingId', $roamingId, PDO::PARAM_INT);
        $request->bindValue(':docId', $docId, PDO::PARAM_STR);
        $request->bindValue(':generationUserId', $userId, PDO::PARAM_INT);
        $this->dbAccess->executeWithException($request);
        return $request->errorCode();
    }

    public function lock() {
        $this->dbAccess->getPdo()->exec('LOCK TABLES '.SQL_TABLE_ROAMINGS.' WRITE');
    }

    public function unlock() {
        $this->dbAccess->getPdo()->exec('UNLOCK TABLES');
    }

    public function cleanPreviousRoamingsVersion() {
        $query = 'DELETE FROM '.SQL_TABLE_ROAMINGS.' WHERE roamingId IN ('.
                 '  SELECT r3.roamingID FROM ('.
                 '    SELECT r1.roamingID FROM '.SQL_TABLE_ROAMINGS.' r1 WHERE r1.docId IS NULL'.
                 '    AND r1.roamingDate < (NOW() - INTERVAL 2 DAY)'.
                 '    AND r1.version <> (select max(r2.version) from '.SQL_TABLE_ROAMINGS.' r2 where r1.roamingDate = r2.roamingDate)'.
                 '  ) r3'.
                 ' )';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $this->dbAccess->executeWithException($request);
    }

    public function deleteOldRoamings() {
        $query = 'DELETE FROM '.SQL_TABLE_ROAMINGS.' WHERE roamingDate  < (NOW() - INTERVAL 125 DAY)';
        $request = $this->dbAccess->getPdo()->prepare($query);
        $this->dbAccess->executeWithException($request);
    }

}

