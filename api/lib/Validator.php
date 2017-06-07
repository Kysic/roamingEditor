<?php

require_once('conf/RolesPermissions.php');

class Validator {

    private $session;
    public function __construct($session) {
        $this->session = $session;
    }

    private function getOlderRoamingDate() {
        $now = strtotime('now');
        return strtotime('-'.REPORT_OLD_LIMIT_DAYS.' day', $now);
    }
    private function getOlderRoamingDateStr() {
        return date('Y-m-d', $this->getOlderRoamingDate());
    }

    public function validateRoamingDate($roamingDate) {
        if ( !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $roamingDate) ) {
            throw new BadRequestException('Date de la maraude invalide, format attendu yyyy-mm-dd.');
        }
        if ($roamingDate < $this->getOlderRoamingDateStr()) {
            $this->session->checkHasPermission(P_SEE_ALL_REPORT);
        }
    }

    public function validateUserId($userId) {
        if ( empty( $userId ) ) {
            throw new BadRequestException('Absence de l\'identifiant de l\'utilisateur non renseignée, assurez-vous d\'avoir copier entièrement l\'URL reçue par email.');
        } else if (!filter_var($userId, FILTER_VALIDATE_INT)) {
            throw new BadRequestException('Identifiant de l\'utilisateur invalide.');
        }
    }

    public function validateEmail($email) {
        if ( empty( $email ) ) {
            throw new BadRequestException('Adresse email non renseignée');
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BadRequestException('Adresse email invalide.');
        }
    }

    private function containsLowerUpperNumberAndSpecialChar($password) {
        $nbElt = 0;
        if (preg_match('/[a-z]+/', $password)) {
            $nbElt++;
        }
        if (preg_match('/[A-Z]+/', $password)) {
            $nbElt++;
        }
        if (preg_match('/[0-9]+/', $password)) {
            $nbElt++;
        }
        if (preg_match('/[^0-9a-zA-Z]+/', $password)) {
            $nbElt++;
        }
        return $nbElt >= 3;
    }

    public function validatePasswordOnLogin($password) {
        if ( mb_strlen($password) > MAX_PASSWORD_LENGTH ) {
            throw new BadRequestException('Mot de passe trop long, '.MAX_PASSWORD_LENGTH.' caractères maximum.');
        }
    }

    public function validatePassword($password) {
        if ( empty( $password ) ) {
            throw new BadRequestException('Mot de passe non renseigné');
        } else if ( mb_strlen($password) > MAX_PASSWORD_LENGTH ) {
            throw new BadRequestException('Mot de passe trop long, '.MAX_PASSWORD_LENGTH.' caractères maximum.');
        } else if ( mb_strlen($password) < MIN_PASSWORD_LENGTH
            || !$this->containsLowerUpperNumberAndSpecialChar($password) ) {
            throw new BadRequestException('Le mot de passe doit faire plus de '.MIN_PASSWORD_LENGTH
                .' caractères, contenir des minuscules, majuscules, chiffres et caractères spéciaux.');
        }
    }

    public function validateMailTokenFormat($mailToken) {
        if ( empty( $mailToken ) ) {
            throw new BadRequestException('Absence du token de validation, assurez-vous d\'avoir copier entièrement l\'URL reçue par email.');
        }
    }

    public function validateRole($role) {
        if ( empty( $role ) ) {
            throw new BadRequestException('Nom du rôle absent.');
        } else if ( in_array( $role, array(FORMER, GUEST, MEMBER, TUTOR, BOARD, ADMIN) ) ) {
            throw new BadRequestException('Ce rôle ne peut pas ẽtre assigné par ce formulaire.');
        }
    }

}
