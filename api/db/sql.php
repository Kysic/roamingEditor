<?php

require_once('conf/sql.php');

$pdo = false;

function getPdo() {
    global $pdo;
    if (!$pdo) {
        try {
            $dsn = SQL_TYPE.':host='.SQL_SERVER.';dbname='.SQL_DATABASE;
            $pdo = new PDO($dsn, SQL_USER, SQL_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }
    return $pdo;
}

function executeWithException($request) {
    if (!$request->execute()) {
        throw new Exception('Erreur sql lors de l\exécution de la requete ['.implode(', ', $request->errorInfo()).']' );
    }
}

?>
