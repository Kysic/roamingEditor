<?php

require_once('conf/google.php');

function toPascalCase($txt) {
    return implode('-', array_map('ucwords', explode('-', strtolower(trim($txt)))));
}
function getContactFirstname($data) {
    return toPascalCase($data[2]);
}
function getContactLastname($data) {
    return strtoupper(trim($data[1]));
}
function getContactEmail($data) {
    return strtolower($data[5]);
}
function isBoard($data) {
    $trimStr = trim($data[8]);
    return !empty($trimStr);
}
function isTutor($data) {
    $trimStr = trim($data[9]);
    return !empty($trimStr);
}
function checkContactEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}
function extractContacts() {
    $contacts = array();
    $planningUrl = GOOGLE_DOC_URL . CONTACT_DOC_ID . GOOGLE_DOC_CMD_CSV . CONTACT_SHEET_ID;
    if (($handle = fopen($planningUrl, 'r')) !== FALSE) {
        if (($data = fgetcsv($handle, 1000, ',')) === FALSE) {
            return $contacts;
        }
        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            // echo '<!--'.implode(';',$data).'-->'."\n";
            if (count($data) >= 2) {
                $email = getContactEmail($data);
                if (checkContactEmail($email)) {
                    $contacts[$email] = array(
                        'firstname' => getContactFirstname($data), 
                        'lastname' => getContactLastname($data),
                        'isTutor' => isTutor($data),
                        'isBoard' => isBoard($data)
                    );
                }
            }
        }
        fclose($handle);
    }
    return $contacts;
}

?>
