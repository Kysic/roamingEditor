<?php

class Auth {
    private $session;
    private $validator;
    private $lazyUSersStorage;
    private $lazyBruteforceStorage;
    private $lazyMail;
    private $lazyGoogleContacts;
    private $checkFormToken;

    public function __construct($session, $validator, $lazyUSersStorage, $lazyBruteforceStorage, $lazyMail, $lazyGoogleContacts) {
        $this->session = $session;
        $this->validator = $validator;
        $this->lazyUSersStorage = $lazyUSersStorage;
        $this->lazyBruteforceStorage = $lazyBruteforceStorage;
        $this->lazyMail = $lazyMail;
        $this->lazyGoogleContacts = $lazyGoogleContacts;
        global $noFormTokenCheck;
        $this->checkFormToken = $noFormTokenCheck !== true;
    }
    public function register($pEmail, $pSessionToken = false) {
        $this->session->checkHasPermission(P_REGISTER, 'Vous n\'êtes pas autorisé à vous inscrire sur le site.');
        $this->checkToken($pSessionToken);
        $email = strtolower($pEmail);
        $this->validator->validateEmail($email);
        $bruteforceStorage = $this->lazyBruteforceStorage->get();
        $this->checkNbFailedAttempts($bruteforceStorage, 'Failed registration with email '.$email);
        $contacts = $this->lazyGoogleContacts->get()->extractContacts();
        $isInVinciContacts = array_key_exists($email, $contacts);
        if ($isInVinciContacts) {
            $firstname = $contacts[$email]['firstname'];
            $lastname = $contacts[$email]['lastname'];
            $gender = $contacts[$email]['gender'];
            $role = $contacts[$email]['doRoaming'] ? NIGHT_WATCHER : MEMBER;
        } else {
            $bruteforceStorage->registerFailedAttempt($_SERVER['REMOTE_ADDR']);
            throw new ForbiddenException('Cette adresse mail n\'est pas repertoriée dans la liste des contacts du VINCI.');
        }
        $usersStorage = $this->lazyUSersStorage->get();
        $user = $usersStorage->getUserWithEmail($email);
        if ( ! $user ) {
            $usersStorage->addUser($email, $firstname, $lastname, $gender, $role);
            $user = $usersStorage->getUserWithEmail($email);
        }
        $mailToken = $usersStorage->generateUserMailToken($user->userId);
        $this->lazyMail->get()->sendRegisterToken($user->email, $user->firstname, $user->lastname, $user->userId, $mailToken);
    }
    public function changePassword($pPassword, $pPasswordConfirm, $pSessionToken = false) {
        $this->checkToken($pSessionToken);
        $this->validator->validatePassword($pPassword);
        if ($pPassword != $pPasswordConfirm) {
            throw new BadRequestException('Le mot de passe et sa confirmation doivent être identiques.');
        }
        $this->session->checkHasPermission(P_CHANGE_PASSWORD, 'Vous n\'êtes pas autorisé à modifier votre mot de passe.');
        $userId = $this->session->getUser()->userId;
        $this->lazyUSersStorage->get()->changePassword($userId, $pPassword);
        $this->session->deleteAllUserAutologins($userId);
    }
    public function setPassword($pPassword, $pPasswordConfirm, $pUserId, $pMailToken, $pSessionToken = false) {
        $this->checkToken($pSessionToken);
        $this->validator->validatePassword($pPassword);
        if ($pPassword != $pPasswordConfirm) {
            throw new BadRequestException('Le mot de passe et sa confirmation doivent être identiques.');
        }
        $this->session->checkHasPermission(P_RESET_PASSWORD, 'Vous n\'êtes pas autorisé à réinitialiser un mot de passe.');
        $this->validator->validateUserId($pUserId);
        $this->validator->validateMailTokenFormat($pMailToken);
        $usersStorage = $this->lazyUSersStorage->get();
        $bruteforceStorage = $this->lazyBruteforceStorage->get();
        $this->checkNbFailedAttempts($bruteforceStorage, 'Failed password change for user '.$pUserId);
        try {
            $usersStorage->validateMailToken($pUserId, $pMailToken);
        } catch (Exception $e) {
            $bruteforceStorage->registerFailedAttempt($_SERVER['REMOTE_ADDR']);
            throw $e;
        }
        $usersStorage->changePassword($pUserId, $pPassword);
        $usersStorage->resetUserMailToken($pUserId);
        $this->session->deleteAllUserAutologins($pUserId);
        $user = $usersStorage->getUserWithId($pUserId);
        $this->session->setUser($user);
    }
    public function resetPassword($pEmail, $pSessionToken = false) {
        $this->session->checkHasPermission(P_RESET_PASSWORD, 'Vous n\'êtes pas autorisé à réinitialiser un mot de passe.');
        $this->checkToken($pSessionToken);
        $email = strtolower($pEmail);
        $this->validator->validateEmail($email);
        $usersStorage = $this->lazyUSersStorage->get();
        $user = $usersStorage->getUserWithEmail($email);
        if (!$user) {
            throw new BadRequestException('Aucun compte utilisateur n\'est associé à cette adresse email.');
        }
        $mailToken = $usersStorage->generateUserMailToken($user->userId);
        $this->lazyMail->get()->sendResetPasswordToken($user->email, $user->firstname, $user->lastname, $user->userId, $mailToken);
    }
    public function login($pEmail, $pPassword, $pStayLogged = false, $pSessionToken = false) {
        $this->session->checkHasPermission(P_LOG_IN, 'Vous n\'êtes pas autorisé à vous connecter sur le site.');
        $email = strtolower($pEmail);
        $this->validator->validateEmail($email);
        $password = $pPassword;
        $this->validator->validatePasswordOnLogin($password);
        $usersStorage = $this->lazyUSersStorage->get();
        $bruteforceStorage = $this->lazyBruteforceStorage->get();
        $this->checkNbFailedAttempts($bruteforceStorage, 'Failed login with email '.$email);
        try {
            $user = $usersStorage->checkAndGetUser($email, $password);
        } catch (Exception $e) {
            $bruteforceStorage->registerFailedAttempt($_SERVER['REMOTE_ADDR']);
            throw $e;
        }
        if ($user->role === APPLI) {
            throw new SecurityException('Request rejected',
                'Login attempt with application '.$user->userId.' credentials on standard login form.');
        }
        $this->session->setUser($user);
        $usersStorage->resetUserMailToken($user->userId);
        if ($pStayLogged) {
            $this->session->generateAutologinId($user->userId);
        }
    }
    public function logout($pSessionToken = false) {
        $this->session->checkHasPermission(P_LOG_OUT, 'Vous n\'êtes pas autorisé à vous déconnecter du site.');
        $this->checkToken($pSessionToken);
        $this->session->unsetUser();
    }
    public function emulateRole($role) {
        $this->restoreRole();
        $this->session->checkIsRoot();
        $user = $this->session->getUser();
        $user->role = $role;
        $this->session->setUser($user);
    }
    private function restoreRole() {
        $this->session->checkLoggedIn();
        $userId = $this->session->getUser()->userId;
        $usersStorage = $this->lazyUSersStorage->get();
        $user = $usersStorage->getUserWithId($userId);
        $this->session->setUser($user);
    }
    private function checkNbFailedAttempts($bruteforceStorage, $details = 'no details') {
        if ($bruteforceStorage->getNbFailedAttemptsInPeriod($_SERVER['REMOTE_ADDR']) >= BRUTEFORCE_MAX_NB_ATTEMPTS) {
            throw new SecurityException('Trop de tentatives depuis cette IP, veuillez réessayer dans un moment.',
                $details);
        }
    }
    private function checkToken($pSessionToken) {
        if ($this->checkFormToken) {
            $this->session->checkToken($pSessionToken);
        }
    }
}

