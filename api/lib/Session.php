<?php

class Session {

    private $rolesPermissions;
    private $lazyUSersStorage;

    public function __construct($rolesPermissions, $lazyUSersStorage, $autologin = true) {
        $this->rolesPermissions = $rolesPermissions;
        $this->lazyUSersStorage = $lazyUSersStorage;
        session_start();
        if ($autologin) {
            $this->doAutologin();
        }
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
        $user->permissions = $this->rolesPermissions->getPermissions($user->role);
        $_SESSION['USER'] = $user;
    }

    public function unsetUser() {
        unset($_SESSION['USER']);
        $this->resetAutologinIdCookie();
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
            throw new BadRequestException('Sequence d\'actions invalide, vous avez sans doute rafraîchit une page contenant un formulaire déjà envoyé.');
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

    public function generateAutologinId($userId) {
        $autologinId = $this->lazyUSersStorage->get()->createAutologinIdForUserId($userId);
        setcookie(AUTOLOGIN_COOKIE_KEY, $autologinId, time() + AUTOLOGIN_COOKIE_EXPIRATION, '/');
    }

    private function doAutologin() {
        if ( !$this->isLoggedIn() ) {
            $autologin = @$_COOKIE[AUTOLOGIN_COOKIE_KEY];
            if ( !empty($autologin) ) {
                try {
                    $this->connectWithAutologin($autologin);
                } catch (Exception $e) {
                    $this->resetAutologinIdCookie();
                }
            }
        }
    }
    private function connectWithAutologin($autologinId64) {
        $user = $this->lazyUSersStorage->get()->getUserWithAutologinId($autologinId64);
        if (!$user) {
            throw new NotFoundException('Unrecognized autologin id');
        }
        $this->setUser($user);
    }
    private function resetAutologinIdCookie() {
        setcookie(AUTOLOGIN_COOKIE_KEY, '', time() - AUTOLOGIN_COOKIE_EXPIRATION, '/');
    }

}

