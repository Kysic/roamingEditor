<?php

require_once(ROAMING_API_DIR.'/conf/google.php');

class GoogleContacts {

    public function extractContacts() {
        $contacts = array();
        $roamingMemberUrl = GOOGLE_DOC_URL . CONTACT_DOC_ID . GOOGLE_DOC_CMD_CSV_GID . CONTACT_ROAMING_SHEET_ID;
        return $this->extractFromCsv($roamingMemberUrl, $contacts, function($data, $stack) {
            $email = $this->getContactEmail($data, 5);
            if ($this->checkContactEmail($email)) {
                $stack[$email] = array(
                    'firstname' => $this->getContactFirstname($data, 2),
                    'lastname' => $this->getContactLastname($data, 1),
                    'phoneNumber' => $this->getPhoneNumber($data, 3),
                    'address' => $this->getAddress($data, 6),
                    'birthDate' => $this->getBirthDate($data, 4),
                    'gender' => $this->getGender($data, 0),
                    'registeringDate' => $this->getRegisteringDate($data, 9),
                    'isTutor' => $this->isTutor($data, 8),
                    'isBoard' => $this->isBoard($data, 7),
                    'doRoaming' => $this->doRoaming($data, 10),
                );
            }
            return $stack;
        });
    }
    private function extractFromCsv($url, $stack, $extractor) {
        if (($handle = fopen($url, 'r')) !== FALSE) {
            // skip table header
            if (($data = fgetcsv($handle, 1000, ',')) === FALSE) {
                return $stack;
            }
            // parse table content
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                //echo '<!--'.implode(';',$data).'-->'."\n";
                if (count($data) >= 2) {
                    $stack = $extractor($data, $stack);
                }
            }
            fclose($handle);
        }
        return $stack;
    }
    private function toPascalCase($txt) {
        return implode('-', array_map('ucwords', explode('-', mb_strtolower(trim($txt)))));
    }
    private function getContactFirstname($data, $col) {
        return $this->toPascalCase(trim($data[$col] ?? ''));
    }
    private function getContactLastname($data, $col) {
        return strtoupper(trim($data[$col] ?? ''));
    }
    private function getPhoneNumber($data, $col) {
        $phoneNumber = trim($data[$col] ?? '');
        if (preg_match('/^[0-9]{9}$/', $phoneNumber)) {
            $phoneNumber = '0'.$phoneNumber;
        }
        return $phoneNumber;
    }
    private function getBirthDate($data, $col) {
        return trim($data[$col] ?? '');
    }
    private function getContactEmail($data, $col) {
        return strtolower(trim($data[$col] ?? ''));
    }
    private function getAddress($data, $col) {
        return trim($data[$col] ?? '');
    }
    private function getGender($data, $col) {
        $gender = strtoupper(trim($data[$col] ?? ''));
        if ($gender == 'H') {
            return 'M';
        } else if ($gender == 'F') {
            return 'F';
        } else {
            return null;
        }
    }
    private function isBoard($data, $col) {
        $trimStr = trim($data[$col] ?? '');
        return !empty($trimStr);
    }
    private function isTutor($data, $col) {
        $trimStr = trim($data[$col] ?? '');
        return !empty($trimStr);
    }
    private function doRoaming($data, $col) {
        $trimStr = trim($data[$col] ?? '');
        return empty($trimStr);
    }
    private function getRegisteringDate($data, $col) {
        return trim($data[$col] ?? '');
    }
    private function checkContactEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

