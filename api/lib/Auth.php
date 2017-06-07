<?php

class Auth {
    private $session;
    private $validator;
    private $lazyUSersStorage;
    private $lazyBruteforceStorage;
    private $lazyMail;
    private $lazyGoogleContacts;
    public function __construct($session, $validator, $lazyUSersStorage, $lazyBruteforceStorage, $lazyMail, $lazyGoogleContacts) {
        $this->session = $session;
        $this->validator = $validator;
        $this->lazyUSersStorage = $lazyUSersStorage;
        $this->lazyBruteforceStorage = $lazyBruteforceStorage;
        $this->lazyMail = $lazyMail;
        $this->lazyGoogleContacts = $lazyGoogleContacts;
    }
    public function signin() {
        $this->session->checkHasPermission(P_LOG_IN, 'Vous n\'êtes pas autorisé à vous inscrire sur le site.');
        $this->session->checkToken(@$_POST['sessionToken']);
        $email = strtolower(@$_POST['email']);
        $this->validator->validateEmail($email);
        $contacts = $this->lazyGoogleContacts->get()->extractContacts();
        $isInVinciContacts = array_key_exists($email, $contacts);
        if ($isInVinciContacts) {
            $firstname = $contacts[$email]['firstname'];
            $lastname = $contacts[$email]['lastname'];
        } else {
            throw new ForbiddenException('Cette adresse mail n\'est pas repertoriée dans la liste des contacts du VINCI.');
        }
        $usersStorage = $this->lazyUSersStorage->get();
        $usersStorage->addUser($email, $firstname, $lastname);
        $user = $usersStorage->getUserWithEmail($email);
        $mailToken = $usersStorage->generateUserMailToken($user->userId);
        $this->lazyMail->get()->sendSigninToken($user->email, $user->firstname, $user->lastname, $user->userId, $mailToken);
    }
    public function setPassword() {
        $this->session->checkToken(@$_POST['sessionToken']);
        $password = @$_POST['password'];
        $this->validator->validatePassword($password);
        if ($password != @$_POST['passwordConfirm']) {
            throw new BadRequestException('Le mot de passe et sa confirmation doivent être identiques.');
        }
        if ($this->session->isLoggedIn()) {
            $this->session->checkHasPermission(P_CHANGE_PASSWORD, 'Vous n\'êtes pas autorisé à modifier votre mot de passe.');
            $usersStorage = $this->lazyUSersStorage->get();
            $userId = $this->session->getUser()->userId;
            $usersStorage->changePassword($userId, $password);
            $usersStorage->deleteUserAutologinId($userId);
        } else {
            $this->session->checkHasPermission(P_RESET_PASSWORD, 'Vous n\'êtes pas autorisé à réinitialiser un mot de passe.');
            $userId = @$_POST['userId'];
            $this->validator->validateUserId($userId);
            $mailToken = @$_POST['mailToken'];
            $this->validator->validateMailTokenFormat($mailToken);
            $usersStorage = $this->lazyUSersStorage->get();
            $usersStorage->validateMailToken($userId, $mailToken);
            $usersStorage->changePassword($userId, $password);
            $usersStorage->resetUserMailToken($userId);
            $usersStorage->deleteUserAutologinId($userId);
            $user = $usersStorage->getUserWithId($userId);
            $this->session->setUser($user);
        }
    }
    public function resetPassword() {
        $this->session->checkHasPermission(P_RESET_PASSWORD, 'Vous n\'êtes pas autorisé à réinitialiser un mot de passe.');
        $this->session->checkToken(@$_POST['sessionToken']);
        $email = strtolower(@$_POST['email']);
        $this->validator->validateEmail($email);
        $usersStorage = $this->lazyUSersStorage->get();
        $user = $usersStorage->getUserWithEmail($email);
        if (!$user) {
            throw new BadRequestException('Aucun compte utilisateur n\'est associé à cette adresse email.');
        }
        $mailToken = $usersStorage->generateUserMailToken($user->userId);
        $this->lazyMail->get()->sendResetPasswordToken($user->email, $user->firstname, $user->lastname, $user->userId, $mailToken);
    }
    public function login() {
        $this->session->checkHasPermission(P_LOG_IN, 'Vous n\'êtes pas autorisé à vous connecter sur le site.');
        $this->session->checkToken(@$_POST['sessionToken']);
        $email = strtolower(@$_POST['email']);
        $this->validator->validateEmail($email);
        $password = @$_POST['password'];
        $this->validator->validatePasswordOnLogin($password);
        $bruteforceStorage = $this->lazyBruteforceStorage->get();
        if ($bruteforceStorage->getNbFailedAttemptInPeriod($_SERVER['REMOTE_ADDR']) >= BRUTEFORCE_MAX_NB_ATTEMPTS) {
            throw new ForbiddenException('Trop de tentatives de connexion depuis cette IP, veillez réessayer dans un moment.');
        }
        $usersStorage = $this->lazyUSersStorage->get();
        try {
            $user = $usersStorage->checkAndGetUser($email, $password);
        } catch (Exception $e) {
            $bruteforceStorage->registerFailedAttempt($_SERVER['REMOTE_ADDR']);
            throw $e;
        }
        $this->session->setUser($user);
        $usersStorage->resetUserMailToken($user->userId);
        if (@$_POST['stayLogged'] == 'true') {
            $this->session->generateAutologinId($user->userId);
        }
    }
    public function logout() {
        $this->session->checkHasPermission(P_LOG_OUT, 'Vous n\'êtes pas autorisé à vous déconnecter du site.');
        $this->session->checkToken(@$_POST['sessionToken']);
        $usersStorage = $this->lazyUSersStorage->get();
        $usersStorage->deleteUserAutologinId($this->session->getUser()->userId);
        $this->session->unsetUser();
    }
}

