<?php

define('ROAMING_API_DIR', realpath (__DIR__.'/..'));

define('ROAMING_TMP_DIR', ROAMING_API_DIR.'/tmp');

require_once(ROAMING_API_DIR.'/conf/main.php');
require_once(ROAMING_API_DIR.'/lib/Exceptions.php');
define('PERMISSIONS_LIB', ROAMING_API_DIR.'/lib/RolesPermissions.php');

define('SESSION_LIB', ROAMING_API_DIR.'/lib/Session.php');
define('VALIDATOR_LIB', ROAMING_API_DIR.'/lib/Validator.php');
define('AUTH_LIB', ROAMING_API_DIR.'/lib/Auth.php');

define('MAIL_LIB', ROAMING_API_DIR.'/lib/Mail.php');
define('JSON_LIB', ROAMING_API_DIR.'/lib/Json.php');

define('GOOGLE_CONTACTS_LIB', ROAMING_API_DIR.'/lib/GoogleContacts.php');
define('GOOGLE_PLANNING_LIB', ROAMING_API_DIR.'/lib/GooglePlanning.php');
define('SPREADSHEETS_GENERATOR_LIB', ROAMING_API_DIR.'/lib/SpreadsheetsGenerator.php');
define('REPORT_FILES_LIB', ROAMING_API_DIR.'/lib/ReportFiles.php');
define('STATS_LIB', ROAMING_API_DIR.'/lib/Stats.php');

define('REPORTING_115_LIB', ROAMING_API_DIR.'/lib/Reporting115.php');

define('DB_ACCESS_LIB', ROAMING_API_DIR.'/db/DbAccess.php');
define('USERS_STORAGE_LIB', ROAMING_API_DIR.'/db/UsersStorage.php');
define('ROAMINGS_STORAGE_LIB', ROAMING_API_DIR.'/db/RoamingsStorage.php');
define('AUTOLOGIN_STORAGE_LIB', ROAMING_API_DIR.'/db/AutologinStorage.php');
define('BRUTEFORCE_STORAGE_LIB', ROAMING_API_DIR.'/db/BruteforceStorage.php');
define('REPORTS_STORAGE_LIB', ROAMING_API_DIR.'/db/ReportsStorage.php');

class Container {

    private $rolesPermissions = NULL;
    private $session = NULL;
    private $validator = NULL;
    private $auth = NULL;
    private $json = NULL;
    private $mail = NULL;
    private $googleContacts = NULL;
    private $googlePlanning = NULL;
    private $spreadsheetsGenerator = NULL;
    private $reportFiles;
    private $dbAcces = NULL;
    private $usersStorage = NULL;
    private $roamingsStorage = NULL;
    private $autologinStorage = NULL;
    private $bruteforceStorage = NULL;
    private $reportsStorage = NULL;
    private $stats = NULL;
    private $reporting115 = NULL;

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
            $this->session = new Session(
                $this->getRolesPermissions(),
                $this->lazyUsersStorage(),
                $this->lazyAutologinStorage(),
                $this->lazyBruteforceStorage()
            );
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
                $this->lazyBruteforceStorage(),
                $this->lazyMail(),
                $this->lazyGoogleContacts()
            );
        }
        return $this->auth;
    }

    public function getJson() {
        if (!$this->json) {
            require_once(JSON_LIB);
            $this->json = new Json($this->lazyMail());
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

    public function getAutologinStorage() {
        if (!$this->autologinStorage) {
            require_once(AUTOLOGIN_STORAGE_LIB);
            $this->autologinStorage = new AutologinStorage($this->getDbAccess());
        }
        return $this->autologinStorage;
    }
    public function lazyAutologinStorage() {
        return new LazyLoader($this, 'getAutologinStorage');
    }

    public function getBruteforceStorage() {
        if (!$this->bruteforceStorage) {
            require_once(BRUTEFORCE_STORAGE_LIB);
            $this->bruteforceStorage = new BruteforceStorage($this->getDbAccess());
        }
        return $this->bruteforceStorage;
    }
    public function lazyBruteforceStorage() {
        return new LazyLoader($this, 'getBruteforceStorage');
    }

    public function getReportsStorage() {
        if (!$this->reportsStorage) {
            require_once(REPORTS_STORAGE_LIB);
            $this->reportsStorage = new ReportsStorage($this->getDbAccess());
        }
        return $this->reportsStorage;
    }

    public function getReportFiles() {
        if (!$this->reportFiles) {
            require_once(REPORT_FILES_LIB);
            $this->reportFiles = new ReportFiles();
        }
        return $this->reportFiles;
    }

    public function getStats() {
        if (!$this->stats) {
            require_once(STATS_LIB);
            $this->stats = new Stats();
        }
        return $this->stats;
    }

    public function getReporting115() {
        if (!$this->reporting115) {
            require_once(REPORTING_115_LIB);
            $this->reporting115 = new Reporting115($this->getReportsStorage());
        }
        return $this->reporting115;
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
