<?php

require_once('../conf/sql.php');

class Sql {

    private static $pdo;

    public function getPdo() {
        if (!self::$pdo) {
            $dsn = SQL_TYPE.':host='.SQL_SERVER.';dbname='.SQL_DATABASE;
            self::$pdo = new PDO($dsn, SQL_USER, SQL_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        }
        return self::$pdo;
    }

    private function execute($script) {
        $script_content = file_get_contents('sqlscripts/'.$script);
        if ( $this->getPdo()->exec($script_content) === FALSE ) {
            throw new Exception(print_r($this->getPdo()->errorInfo(), true));
        }
    }

    public function reinitItDb() {
        $this->execute('it_drop.sql');
        $this->execute('it_create.sql');
        $this->execute('it_populate.sql');
    }

}


