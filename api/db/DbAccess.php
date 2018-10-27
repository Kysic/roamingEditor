<?php

require_once(ROAMING_API_DIR.'/conf/sql.php');

class DbAccess {

    private static $pdo;

    public function getPdo() {
        if (!self::$pdo) {
            $dsn = SQL_TYPE.':host='.SQL_SERVER.';dbname='.SQL_DATABASE;
            self::$pdo = new PDO($dsn, SQL_USER, SQL_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        }
        return self::$pdo;
    }

    public function executeWithException($request) {
        if (!$request->execute()) {
            $this->raiseException($request);
        }
    }

    public function raiseExceptionIfHasFailed($request) {
        if ($request->errorCode() !== '00000') {
            $this->raiseException($request);
        }
    }

    private function raiseException($request) {
        throw new Exception('Erreur sql lors de l\exécution de la requete ['.implode(', ', $request->errorInfo()).']' );
    }

}
