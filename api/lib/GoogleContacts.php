<?php

require_once(ROAMING_API_DIR.'/conf/google.php');

class GoogleContacts {

    private function toPascalCase($txt) {
        return implode('-', array_map('ucwords', explode('-', mb_strtolower(trim($txt)))));
    }
    private function getContactFirstname($data) {
        return $this->toPascalCase(trim($data[2]));
    }
    private function getContactLastname($data) {
        return strtoupper(trim($data[1]));
    }
    private function getPhoneNumber($data) {
        $phoneNumber = trim($data[3]);
        if (preg_match('/[0-9]{9}/', $phoneNumber)) {
            $phoneNumber = '0'.$phoneNumber;
        }
        return $phoneNumber;
    }
    private function getBirthDate($data) {
        return trim($data[4]);
    }
    private function getContactEmail($data) {
        return strtolower(trim($data[5]));
    }
    private function getAddress($data) {
        return trim($data[6]);
    }
    private function isBoard($data) {
        $trimStr = trim($data[8]);
        return !empty($trimStr);
    }
    private function isTutor($data) {
        $trimStr = trim($data[9]);
        return !empty($trimStr);
    }
    private function getRegisteringDate($data) {
        return trim($data[10]);
    }
    private function checkContactEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public function extractContacts() {
        $contacts = array();
        $planningUrl = GOOGLE_DOC_URL . CONTACT_DOC_ID . GOOGLE_DOC_CMD_CSV_GID . CONTACT_SHEET_ID;
        if (($handle = fopen($planningUrl, 'r')) !== FALSE) {
            if (($data = fgetcsv($handle, 1000, ',')) === FALSE) {
                return $contacts;
            }
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // echo '<!--'.implode(';',$data).'-->'."\n";
                if (count($data) >= 2) {
                    $email = $this->getContactEmail($data);
                    if ($this->checkContactEmail($email)) {
                        $contacts[$email] = array(
                            'firstname' => $this->getContactFirstname($data), 
                            'lastname' => $this->getContactLastname($data),
                            'phoneNumber' => $this->getPhoneNumber($data),
                            'address' => $this->getAddress($data),
                            'birthDate' => $this->getBirthDate($data),
                            'registeringDate' => $this->getRegisteringDate($data),
                            'isTutor' => $this->isTutor($data),
                            'isBoard' => $this->isBoard($data)
                        );
                    }
                }
            }
            fclose($handle);
        }
        return $contacts;
    }

}

