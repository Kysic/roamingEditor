<?php

class Session {

    private $rolesPermissions;
    private $lazyUSersStorage;
    private $lazyAutologinStorage;
    private $lazyBruteforceStorage;

    public function __construct($rolesPermissions, $lazyUSersStorage, $lazyAutologinStorage, $lazyBruteforceStorage, $autologin = true) {
        $this->rolesPermissions = $rolesPermissions;
        $this->lazyUSersStorage = $lazyUSersStorage;
        $this->lazyAutologinStorage = $lazyAutologinStorage;
        $this->lazyBruteforceStorage = $lazyBruteforceStorage;
        session_name(SESSION_COOKIE_KEY);
        session_start();
        if ($autologin) {
            $this->doApplicationlogin();
            $this->doAutologin();
        }
    }

    public function closeWrite() {
        session_write_close();
    }

    public function isLoggedIn() {
        return isset($_SESSION['USER']);
    }

    public function getUser() {
        if ($this->isLoggedIn()) {
            return $_SESSION['USER'];
        } else {
            return (object) array(
                'role' => VISITOR,
                'permissions' => $this->rolesPermissions->getPermissions(VISITOR)
            );
        }
    }

    public function setUser($user) {
        $user->username = $this->generateUsername($user);
        $user->permissions = $this->rolesPermissions->getPermissions($user->role);
        $_SESSION['USER'] = $user;
    }

    public function unsetUser() {
        unset($_SESSION['USER']);
        $this->deleteAutologin();
    }

    public function hasPermission($permission) {
        return in_array($permission, $this->getUser()->permissions);
    }

    public function getToken() {
        if ( !isset($_SESSION['POST_TOKEN']) ) {
            $_SESSION['POST_TOKEN'] = base64_encode(openssl_random_pseudo_bytes(32));
        }
        return $_SESSION['POST_TOKEN'];
    }

    public function checkToken($tokenToCheck) {
        if ( $this->getToken() != $tokenToCheck ) {
            throw new BadRequestException('Jeton de session invalide, veuillez rafraîchir puis réessayer.');
        }
    }

    public function checkLoggedIn() {
        if (!$this->isLoggedIn()) {
            throw new UnauthenticatedException('Vous devez être identifié pour faire cette action.');
        }
    }

    public function checkHasPermission($permission, $errorMsg = 'Vous n\'êtes pas authorisé à faire cette action.') {
        if (!$this->hasPermission($permission)) {
            throw new ForbiddenException($errorMsg);
        }
    }

    public function checkIsRoot() {
        if ($this->getUser()->role != ROOT) {
            throw new ForbiddenException('Seul root peut effectuer cette action');
        }
    }

    public function generateAutologinId($userId) {
        list($autologinId, $autologinToken) = $this->lazyAutologinStorage->get()->createAutologinFor($userId);
        $this->setAutologinCookies($autologinId, $autologinToken);
    }

    public function deleteAllUserAutologins($userId) {
        $this->lazyAutologinStorage->get()->deleteAllUserAutologins($userId);
        $this->resetAutologinCookies();
    }

    private function doApplicationLogin() {
        if ( !$this->isLoggedIn() ) {
            $applicationId = @$_COOKIE[APPLICATION_ID_COOKIE_KEY];
            $applicationToken = @$_COOKIE[APPLICATION_TOKEN_COOKIE_KEY];
            if ( !empty($applicationId) && !empty($applicationToken) ) {
                $usersStorage = $this->lazyUSersStorage->get();
                try {
                    $user = $usersStorage->checkAndGetUser($applicationId, $applicationToken);
                } catch (Exception $e) {
                    setcookie(APPLICATION_ID_COOKIE_KEY, '', time() - 3000000, '/');
                    setcookie(APPLICATION_TOKEN_COOKIE_KEY, '', time() - 3000000, '/');
                    throw new SecurityException(
                        'Request rejected',
                        'Failed login attempt with applicationId '.$applicationId.' : '.$e->getMessage());
                }
                if ($user->role !== APPLI) {
                    throw new SecurityException(
                        'Request rejected',
                        'Login attempt with applicationId on non application user '.$user->userId);
                }
                $this->setUser($user);
            }
        }
    }
    private function doAutologin() {
        if ( !$this->isLoggedIn() ) {
            $autologinId = @$_COOKIE[AUTOLOGIN_ID_COOKIE_KEY];
            $autologinToken = @$_COOKIE[AUTOLOGIN_TOKEN_COOKIE_KEY];
            if ( !empty($autologinId) && !empty($autologinToken) ) {
                $bruteforceStorage = $this->lazyBruteforceStorage->get();
                if ($bruteforceStorage->getNbFailedAttemptsInPeriod($_SERVER['REMOTE_ADDR']) < BRUTEFORCE_MAX_NB_ATTEMPTS) {
                    try {
                        $this->connectWithAutologin($autologinId, $autologinToken);
                    } catch (Exception $e) {
                        $bruteforceStorage->registerFailedAttempt($_SERVER['REMOTE_ADDR']);
                        $this->resetAutologinCookies();
                        if ($e instanceof SecurityException) {
                            throw $e;
                        }
                    }
                }
            }
        }
    }
    private function connectWithAutologin($autologinId, $autologinToken) {
        $autologinStorage = $this->lazyAutologinStorage->get();
        $userId = $autologinStorage->getUserIdFromAutologin($autologinId, $autologinToken);
        if (!$userId) {
            throw new ForbiddenException('Autologin id invalide ou obsolète');
        }
        $user = $this->lazyUSersStorage->get()->getUserWithId($userId);
        if (!$user) {
            throw new ForbiddenException('Utilisateur '.$userId.' introuvable');
        }
        $autologinToken = $autologinStorage->updateAutologin($autologinId);
        $this->setAutologinCookies($autologinId, $autologinToken);
        $this->setUser($user);
    }
    private function deleteAutologin() {
        $autologinId = @$_COOKIE[AUTOLOGIN_ID_COOKIE_KEY];
        if ( !empty($autologinId) ) {
            $this->lazyAutologinStorage->get()->deleteAutologin($autologinId);
        }
        $this->resetAutologinCookies();
    }
    private function setAutologinCookies($autologinId, $autologinToken) {
        $expTime = time() + AUTOLOGIN_COOKIE_EXPIRATION;
        setcookie(AUTOLOGIN_ID_COOKIE_KEY, $autologinId, $expTime, '/');
        setcookie(AUTOLOGIN_TOKEN_COOKIE_KEY, $autologinToken, $expTime, '/');
    }
    private function resetAutologinCookies() {
        $expTime = time() - AUTOLOGIN_COOKIE_EXPIRATION;
        setcookie(AUTOLOGIN_ID_COOKIE_KEY, '', $expTime, '/');
        setcookie(AUTOLOGIN_TOKEN_COOKIE_KEY, '', $expTime, '/');
    }

    private function generateUsername($user) {
        return $this->toPascalCase($user->firstname).' '.$this->toPascalCase($user->lastname);
    }
    private function toPascalCase($txt) {
        return implode('-', array_map('ucwords', explode('-', mb_strtolower(trim($txt)))));
    }

}

