<?php

define('PERMISSIONS_LIB', 'conf/RolesPermissions.php');
define('SESSION_LIB', 'lib/Session.php');
define('VALIDATOR_LIB', 'lib/Validator.php');
define('AUTH_LIB', 'lib/Auth.php');

define('MAIL_LIB', 'lib/Mail.php');
define('JSON_LIB', 'lib/Json.php');

define('GOOGLE_CONTACTS_LIB', 'lib/GoogleContacts.php');
define('GOOGLE_PLANNING_LIB', 'lib/GooglePlanning.php');
define('SPREADSHEETS_GENERATOR_LIB', 'lib/SpreadsheetsGenerator.php');

define('DB_ACCESS_LIB', 'db/DbAccess.php');
define('USERS_STORAGE_LIB', 'db/UsersStorage.php');
define('ROAMINGS_STORAGE_LIB', 'db/RoamingsStorage.php');

class Container {

    private $rolesPermissions = false;
    private $session = false;
    private $validator = false;
    private $auth = false;
    private $json = false;
    private $mail = false;
    private $googleContacts = false;
    private $googlePlanning = false;
    private $spreadsheetsGenerator = false;
    private $dbAcces = false;
    private $usersStorage = false;
    private $roamingsStorage = false;

    public function getRolesPermissions() {
        if (!$this->rolesPermissions) {
            require_once(PERMISSIONS_LIB);
            $this->rolesPermissions = new RolesPermissions();
        }
        return $this->rolesPermissions;
    }

    public function getSession() {
        if (!$this->session) {
            require_once(SESSION_LIB);
            $this->session = new Session($this->getRolesPermissions(), $this->lazyUsersStorage());
        }
        return $this->session;
    }

    public function getValidator() {
        if (!$this->validator) {
            require_once(VALIDATOR_LIB);
            $this->validator = new Validator($this->getSession());
        }
        return $this->validator;
    }

    public function getMail() {
        if (!$this->mail) {
            require_once(MAIL_LIB);
            $this->mail = new Mail();
        }
        return $this->mail;
    }
    public function lazyMail() {
        return new LazyLoader($this, 'getMail');
    }

    public function getAuth() {
        if (!$this->auth) {
            require_once(AUTH_LIB);
            $this->auth = new Auth(
                $this->getSession(),
                $this->getValidator(),
                $this->lazyUsersStorage(),
                $this->lazyMail(),
                $this->lazyGoogleContacts()
            );
        }
        return $this->auth;
    }

    public function getJson() {
        if (!$this->json) {
            require_once(JSON_LIB);
            $this->json = new Json();
        }
        return $this->json;
    }

    public function getGoogleContacts() {
        if (!$this->googleContacts) {
            require_once(GOOGLE_CONTACTS_LIB);
            $this->googleContacts = new GoogleContacts();
        }
        return $this->googleContacts;
    }
    public function lazyGoogleContacts() {
        return new LazyLoader($this, 'getGoogleContacts');
    }

    public function getGooglePlanning() {
        if (!$this->googlePlanning) {
            require_once(GOOGLE_PLANNING_LIB);
            $this->googlePlanning = new GooglePlanning();
        }
        return $this->googlePlanning;
    }
    public function lazyGooglePlanning() {
        return new LazyLoader($this, 'getGooglePlanning');
    }

    public function getSpreadsheetsGenerator() {
        if (!$this->spreadsheetsGenerator) {
            require_once(SPREADSHEETS_GENERATOR_LIB);
            $this->spreadsheetsGenerator = new SpreadsheetsGenerator($this->getRoamingsStorage());
        }
        return $this->spreadsheetsGenerator;
    }
    public function lazySpreadsheetsGenerator() {
        return new LazyLoader($this, 'getSpreadsheetsGenerator');
    }

    public function getDbAccess() {
        if (!$this->dbAcces) {
            require_once(DB_ACCESS_LIB);
            $this->dbAcces = new DbAccess();
        }
        return $this->dbAcces;
    }
    public function lazyDbAccess() {
        return new LazyLoader($this, 'getDbAccess');
    }

    public function getUsersStorage() {
        if (!$this->usersStorage) {
            require_once(USERS_STORAGE_LIB);
            $this->usersStorage = new UsersStorage($this->getDbAccess());
        }
        return $this->usersStorage;
    }
    public function lazyUsersStorage() {
        return new LazyLoader($this, 'getUsersStorage');
    }

    public function getRoamingsStorage() {
        if (!$this->roamingsStorage) {
            require_once(ROAMINGS_STORAGE_LIB);
            $this->roamingsStorage = new RoamingsStorage($this->getDbAccess());
        }
        return $this->roamingsStorage;
    }
    public function lazyRoamingsStorage() {
        return new LazyLoader($this, 'getRoamingsStorage');
    }

}

class LazyLoader {
    private $getterObj;
    private $getterMethod;
    public function __construct($getterObj, $getterMethod) {
        $this->getterObj = $getterObj;
        $this->getterMethod = $getterMethod;
    }
    public function get() {
        return call_user_func(array($this->getterObj, $this->getterMethod));
    }
}

