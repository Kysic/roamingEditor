<?php

class ReportsStorage {

  private $dbAccess;

  public function __construct($dbAccess) {
    $this->dbAccess = $dbAccess;
  }

  public function add($reports) {
    $query = 'INSERT INTO '.SQL_TABLE_REPORTS.' (rawJson) VALUES (:rawJson)';
    $request = $this->dbAccess->getPdo()->prepare($query);
    $request->bindValue(':rawJson', $reports, PDO::PARAM_STR);
    $this->dbAccess->executeWithException($request);
    $this->deleteOld();
  }

  public function deleteOld() {
    $query = 'DELETE FROM '.SQL_TABLE_REPORTS.' WHERE creationDate < (NOW() - INTERVAL 63 DAY)';
    $request = $this->dbAccess->getPdo()->prepare($query);
    $this->dbAccess->executeWithException($request);
  }

  public function getTodaysLast() {
    $query = 'SELECT rawJson FROM '.SQL_TABLE_REPORTS.
             ' WHERE creationDate > (NOW() - INTERVAL 8 HOUR)'.
             ' ORDER BY creationDate DESC LIMIT 1';
    $request = $this->dbAccess->getPdo()->prepare($query);
    $this->dbAccess->executeWithException($request);
    $reports = $request->fetch(PDO::FETCH_OBJ);
    if ( !$reports ) {
        throw new NotFoundException('No reports found');
    }
    return $reports->rawJson;
  }

}
